<?php

namespace Tests\Feature;

use App\Models\Commission;
use Tests\TestCase;

/**
 * COMMISSION TESTS
 * Tests for listing, marking as paid, and handling empty commission scenarios.
 */
class CommissionTest extends TestCase
{
    private function createCommission($tenant, $staff, $appointment, array $overrides = []): Commission
    {
        return Commission::create(array_merge([
            'tenant_id' => $tenant->id,
            'staff_id' => $staff->id,
            'appointment_id' => $appointment->id,
            'service_price' => 1000,
            'commission_percent' => 10,
            'commission_amount' => 100,
            'status' => 'pending',
        ], $overrides));
    }

    // ──────────────────────────────────────────────
    // TEST 36: Verify owner can list commissions
    // ──────────────────────────────────────────────
    public function test_owner_can_list_commissions(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $this->createCommission($tenant, $staff, $appointment);

        $response = $this->getJson('/api/v1/owner/commissions',
            $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'total_amount', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 37: Verify owner can mark commissions as paid
    // ──────────────────────────────────────────────
    public function test_owner_can_mark_commission_as_paid(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $commission = $this->createCommission($tenant, $staff, $appointment);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/commissions/{$staff->id}/mark-paid",
            [], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Commission payout marked as paid.']);

        $this->assertDatabaseHas('commissions', [
            'id' => $commission->id,
            'status' => 'paid',
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 38: Verify 404 when no pending commissions exist
    // ──────────────────────────────────────────────
    public function test_mark_paid_returns_404_when_no_pending_commissions(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $staff = $this->createStaff($tenant);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/commissions/{$staff->id}/mark-paid",
            [], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
            ->assertJson(['message' => 'No pending commissions found for this period.']);
    }
}
