<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ServiceProduct;
use Tests\TestCase;

/**
 * APPOINTMENT OBSERVER TESTS
 *
 * Covers:
 *  4. Commission is calculated when appointment status → 'completed'
 *  5. Inventory is deducted via ServiceProduct mappings on completion
 *  6. Double-completion idempotency (lockForUpdate race condition guard)
 *
 * The observer is registered on the Appointment model and fires inside
 * a DB::transaction with lockForUpdate. These tests verify the end-to-end
 * business logic without mocking the observer.
 */
class ObserverCommissionTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 4a: Commission created on appointment completion
    // ──────────────────────────────────────────────

    public function test_commission_is_created_when_appointment_is_completed(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant, ['commission_percent' => 20]);
        $service = $this->createService($tenant, ['price' => 1000]);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

        $this->bindTenant($tenant);

        $this->patchJson(
            "/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $this->assertDatabaseHas('commissions', [
            'appointment_id' => $appointment->id,
            'staff_id' => $staff->id,
            'commission_percent' => 20,
            'commission_amount' => 200.00, // 20% of 1000
            'status' => 'pending',
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 4b: Commission amount capped at 50% even if staff rate is higher
    // ──────────────────────────────────────────────

    public function test_commission_is_capped_at_50_percent(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant, ['commission_percent' => 80]); // over the cap
        $service = $this->createService($tenant, ['price' => 1000]);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

        $this->bindTenant($tenant);

        $this->patchJson(
            "/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $commission = Commission::where('appointment_id', $appointment->id)->first();

        $this->assertNotNull($commission);
        $this->assertEquals(50.0, (float) $commission->commission_percent);
        $this->assertEquals(500.0, (float) $commission->commission_amount); // 50% of 1000
    }

    // ──────────────────────────────────────────────
    // TEST 4c: No commission created for non-completed status
    // ──────────────────────────────────────────────

    public function test_commission_is_not_created_when_appointment_is_confirmed(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

        $this->bindTenant($tenant);

        $this->patchJson(
            "/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'confirmed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $this->assertDatabaseMissing('commissions', ['appointment_id' => $appointment->id]);
    }

    // ──────────────────────────────────────────────
    // TEST 6: Idempotency — completing twice creates only ONE commission
    // ──────────────────────────────────────────────

    public function test_commission_is_not_duplicated_if_completed_event_fires_twice(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant, ['commission_percent' => 20]);
        $service = $this->createService($tenant, ['price' => 500]);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

        $this->bindTenant($tenant);

        // First completion
        $appointment->update(['status' => 'completed']);

        // Simulate a second update event (e.g., retry or race condition)
        $appointment->status = 'completed';
        $appointment->save();

        $count = Commission::where('appointment_id', $appointment->id)->count();
        $this->assertEquals(1, $count, 'Observer idempotency guard failed — duplicate commission created');
    }

    // ──────────────────────────────────────────────
    // TEST 5: Inventory deducted via ServiceProduct on completion
    // ──────────────────────────────────────────────

    public function test_inventory_is_deducted_when_appointment_is_completed(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Hair Dye',
            'category' => 'hair',
            'price' => 150,
            'quantity' => 20,
            'low_stock_threshold' => 3,
            'is_active' => true,
        ]);

        ServiceProduct::create([
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity_used' => 2,
            'unit' => 'ml',
        ]);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

        $this->bindTenant($tenant);

        $this->patchJson(
            "/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $product->refresh();

        // quantity should have dropped from 20 → 18
        $this->assertEquals(18, $product->quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => 'appointment_deduct',
            'reference_id' => $appointment->id,
            'quantity' => 2,
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 5b: Inventory deduction is idempotent (no double deduct on retry)
    // ──────────────────────────────────────────────

    public function test_inventory_is_not_deducted_twice_for_same_appointment(): void
    {
        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Conditioner',
            'category' => 'hair',
            'price' => 80,
            'quantity' => 10,
            'low_stock_threshold' => 2,
            'is_active' => true,
        ]);

        ServiceProduct::create([
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity_used' => 3,
            'unit' => 'ml',
        ]);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

        // Fire the completed event twice
        $appointment->update(['status' => 'completed']);
        $appointment->status = 'completed';
        $appointment->save();

        $product->refresh();

        // Must be 10 - 3 = 7, not 10 - 6 = 4
        $this->assertEquals(7, $product->quantity, 'Inventory was double-deducted on repeated completion');

        $txCount = InventoryTransaction::where('product_id', $product->id)
            ->where('type', 'appointment_deduct')
            ->where('reference_id', $appointment->id)
            ->count();

        $this->assertEquals(1, $txCount, 'Inventory transaction was written more than once');
    }
}
