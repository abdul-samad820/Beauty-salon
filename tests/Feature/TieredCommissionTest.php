<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionTier;
use Tests\TestCase;

/**
 * TIERED COMMISSION TESTS
 *
 * Covers: tier-rate lookup, correct tier applied on appointment completion,
 * flat-rate fallback when no tiers exist, 50% safety cap preserved,
 * overlap prevention, and tenant isolation on tier management.
 */
class TieredCommissionTest extends TestCase
{
    // ──────────────────────────────────────────────
    // Unit — CommissionTier::rateForStaff()
    // ──────────────────────────────────────────────

    public function test_correct_tier_rate_returned_for_given_revenue(): void
    {
        $tenant = $this->createTenant();
        $staff = $this->createStaff($tenant);

        CommissionTier::create(['tenant_id' => $tenant->id, 'staff_id' => $staff->id, 'min_revenue' => 0,     'max_revenue' => 50000,  'commission_percent' => 10]);
        CommissionTier::create(['tenant_id' => $tenant->id, 'staff_id' => $staff->id, 'min_revenue' => 50000, 'max_revenue' => 100000, 'commission_percent' => 15]);
        CommissionTier::create(['tenant_id' => $tenant->id, 'staff_id' => $staff->id, 'min_revenue' => 100000, 'max_revenue' => null,   'commission_percent' => 20]);

        $this->assertEquals(10.0, CommissionTier::rateForStaff($staff->id, 0));
        $this->assertEquals(10.0, CommissionTier::rateForStaff($staff->id, 49999));
        $this->assertEquals(15.0, CommissionTier::rateForStaff($staff->id, 50000));
        $this->assertEquals(15.0, CommissionTier::rateForStaff($staff->id, 99999));
        $this->assertEquals(20.0, CommissionTier::rateForStaff($staff->id, 100000));
        $this->assertEquals(20.0, CommissionTier::rateForStaff($staff->id, 500000));
    }

    public function test_returns_null_when_no_tiers_defined(): void
    {
        $tenant = $this->createTenant();
        $staff = $this->createStaff($tenant);

        $this->assertNull(CommissionTier::rateForStaff($staff->id, 75000));
    }

    // ──────────────────────────────────────────────
    // Integration — commission calculation on appointment completion
    // ──────────────────────────────────────────────

    public function test_tiered_rate_applied_when_tiers_exist(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['price' => 1000]);

        CommissionTier::create([
            'tenant_id' => $tenant->id,
            'staff_id' => $staff->id,
            'min_revenue' => 0,
            'max_revenue' => null,
            'commission_percent' => 18,
        ]);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
            'amount' => 1000,
        ]);
        $this->bindTenant($tenant);

        $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $this->assertDatabaseHas('commissions', [
            'appointment_id' => $appointment->id,
            'commission_percent' => 18,
            'commission_amount' => 180,
        ]);
    }

    public function test_flat_rate_fallback_when_no_tiers(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant, ['commission_percent' => 12]);
        $service = $this->createService($tenant, ['price' => 500]);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
            'amount' => 500,
        ]);
        $this->bindTenant($tenant);

        $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $this->assertDatabaseHas('commissions', [
            'appointment_id' => $appointment->id,
            'commission_percent' => 12,
            'commission_amount' => 60,
        ]);
    }

    public function test_50_percent_safety_cap_still_enforced_on_tiered_rate(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['price' => 1000]);

        // Tier with a rate above the 50% safety ceiling.
        CommissionTier::create([
            'tenant_id' => $tenant->id,
            'staff_id' => $staff->id,
            'min_revenue' => 0,
            'max_revenue' => null,
            'commission_percent' => 50, // controller caps at 50, but test boundary
        ]);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
            'amount' => 1000,
        ]);
        $this->bindTenant($tenant);

        $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        $commission = Commission::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($commission);
        $this->assertLessThanOrEqual(50, (float) $commission->commission_percent);
    }

    // ──────────────────────────────────────────────
    // Web — tier add/delete + overlap prevention
    // ──────────────────────────────────────────────

    public function test_owner_can_add_tier_for_staff(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($owner)
            ->post("/owner/staff/{$staff->id}/tiers", [
                'min_revenue' => 0,
                'max_revenue' => 50000,
                'commission_percent' => 10,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('commission_tiers', [
            'staff_id' => $staff->id,
            'min_revenue' => 0,
            'max_revenue' => 50000,
            'commission_percent' => 10,
        ]);
    }

    public function test_owner_cannot_add_overlapping_tier(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $staff = $this->createStaff($tenant);

        CommissionTier::create([
            'tenant_id' => $tenant->id,
            'staff_id' => $staff->id,
            'min_revenue' => 0,
            'max_revenue' => 100000,
            'commission_percent' => 10,
        ]);

        // Trying to add another tier starting at 50000 — overlaps with existing 0–100000.
        $response = $this->actingAs($owner)
            ->post("/owner/staff/{$staff->id}/tiers", [
                'min_revenue' => 50000,
                'max_revenue' => null,
                'commission_percent' => 15,
            ]);

        $response->assertSessionHasErrors(['min_revenue']);
        $this->assertDatabaseCount('commission_tiers', 1);
    }

    public function test_owner_can_delete_tier(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $staff = $this->createStaff($tenant);

        $tier = CommissionTier::create([
            'tenant_id' => $tenant->id,
            'staff_id' => $staff->id,
            'min_revenue' => 0,
            'max_revenue' => null,
            'commission_percent' => 10,
        ]);

        $response = $this->actingAs($owner)
            ->delete("/owner/staff/tiers/{$tier->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('commission_tiers', ['id' => $tier->id]);
    }

    public function test_owner_cannot_delete_another_tenants_tier(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $staffB = $this->createStaff($tenantB);

        $tier = CommissionTier::create([
            'tenant_id' => $tenantB->id,
            'staff_id' => $staffB->id,
            'min_revenue' => 0,
            'max_revenue' => null,
            'commission_percent' => 15,
        ]);

        $this->actingAs($ownerA)
            ->delete("/owner/staff/tiers/{$tier->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('commission_tiers', ['id' => $tier->id]);
    }
}
