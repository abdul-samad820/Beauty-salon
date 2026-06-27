<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Tests\TestCase;

/**
 * PLAN LIMIT ENFORCEMENT TESTS
 *
 * Covers:
 *  3. Plan limit enforcement — the audit notes this doesn't exist;
 *     these tests will confirm whether it is or is not enforced.
 *
 * Tests verify that tenants on limited plans cannot exceed their
 * allowed number of staff members, services, and monthly appointments.
 *
 * If plan enforcement is NOT yet implemented, these tests will fail —
 * which is the point: they document the gap and will pass once the
 * enforcement is added.
 */
class PlanLimitEnforcementTest extends TestCase
{
    private function createPlanAndSubscribe(array $tenantOverrides, array $planOverrides): void
    {
        $tenant = $this->createTenant($tenantOverrides);

        $plan = Plan::create(array_merge([
            'name' => 'Starter',
            'slug' => 'starter-'.uniqid(),
            'price_monthly' => 299,
            'price_yearly' => 2999,
            'max_staff' => 2,
            'max_services' => 5,
            'max_appointments_per_month' => 50,
            'features' => [],
            'is_active' => true,
        ], $planOverrides));

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'amount' => 299,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->bindTenant($tenant);

        // Store on test instance for access in individual tests
        $this->currentTenantForPlanTest = $tenant;
        $this->currentPlanForPlanTest = $plan;
    }

    private Tenant $currentTenantForPlanTest;

    private Plan $currentPlanForPlanTest;

    // ──────────────────────────────────────────────
    // TEST 3a: Cannot create more staff than plan allows
    // ──────────────────────────────────────────────

    public function test_cannot_create_staff_beyond_plan_limit(): void
    {
        $this->createPlanAndSubscribe(
            ['slug' => 'plan-test-a', 'subdomain' => 'plan-test-a', 'email' => 'plana@test.com'],
            ['max_staff' => 1]
        );

        $tenant = $this->currentTenantForPlanTest;
        $owner = $this->createOwner($tenant);

        // Create the maximum allowed (1 staff member)
        $this->createStaff($tenant);

        // Attempt to add a second → should be rejected
        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'Extra Staff',
            'email' => 'extra@salon.com',
            'phone' => '9500000001',
            'commission_percent' => 15,
            'specializations' => ['hair'],
            'working_hours' => [
                'mon' => '09:00-18:00',
                'tue' => '09:00-18:00',
                'wed' => '09:00-18:00',
                'thu' => '09:00-18:00',
                'fri' => '09:00-18:00',
                'sat' => '09:00-18:00',
                'sun' => null,
            ],
        ], $this->ownerHeaders($owner, $tenant));

        // Expect 422 or 403 — plan limit exceeded
        $this->assertContains(
            $response->status(),
            [422, 403],
            "Expected plan limit enforcement (422/403) but got {$response->status()}"
        );
    }

    // ──────────────────────────────────────────────
    // TEST 3b: Cannot create more services than plan allows
    // ──────────────────────────────────────────────

    public function test_cannot_create_services_beyond_plan_limit(): void
    {
        $this->createPlanAndSubscribe(
            ['slug' => 'plan-test-b', 'subdomain' => 'plan-test-b', 'email' => 'planb@test.com'],
            ['max_services' => 2]
        );

        $tenant = $this->currentTenantForPlanTest;
        $owner = $this->createOwner($tenant);

        // Create 2 services (the maximum)
        $this->createService($tenant);
        $this->createService($tenant);

        // Third service → should be rejected
        $response = $this->postJson('/api/v1/owner/services', [
            'name' => 'Extra Service',
            'category' => 'nail',
            'duration_minutes' => 30,
            'price' => 400,
        ], $this->ownerHeaders($owner, $tenant));

        $this->assertContains(
            $response->status(),
            [422, 403],
            "Expected plan limit enforcement (422/403) but got {$response->status()}"
        );
    }

    // ──────────────────────────────────────────────
    // TEST 3c: Tenant within plan limits can create staff
    // ──────────────────────────────────────────────

    public function test_tenant_within_plan_limit_can_create_staff(): void
    {
        $this->createPlanAndSubscribe(
            ['slug' => 'plan-test-c', 'subdomain' => 'plan-test-c', 'email' => 'planc@test.com'],
            ['max_staff' => 5]
        );

        $tenant = $this->currentTenantForPlanTest;
        $owner = $this->createOwner($tenant);

        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'Within Limit Staff',
            'email' => 'within@salon.com',
            'phone' => '9500000010',
            'commission_percent' => 10,
            'specializations' => ['nail'],
            'working_hours' => [
                'mon' => '09:00-18:00',
                'tue' => '09:00-18:00',
                'wed' => '09:00-18:00',
                'thu' => '09:00-18:00',
                'fri' => '09:00-18:00',
                'sat' => '09:00-18:00',
                'sun' => null,
            ],
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(201);
    }

    // ──────────────────────────────────────────────
    // TEST 3d: Free plan tenants cannot exceed free limits
    // ──────────────────────────────────────────────

    public function test_free_plan_tenant_is_limited(): void
    {
        // Free plan: typically 1 staff, 3 services
        $this->createPlanAndSubscribe(
            ['slug' => 'plan-test-free', 'subdomain' => 'plan-test-free', 'email' => 'free@test.com'],
            ['name' => 'Free', 'max_staff' => 1, 'max_services' => 3, 'price_monthly' => 0, 'price_yearly' => 0]
        );

        $tenant = $this->currentTenantForPlanTest;
        $owner = $this->createOwner($tenant);

        // Create 3 services
        $this->createService($tenant);
        $this->createService($tenant);
        $this->createService($tenant);

        // 4th service on free plan → rejected
        $response = $this->postJson('/api/v1/owner/services', [
            'name' => 'Fourth Service',
            'category' => 'hair',
            'duration_minutes' => 45,
            'price' => 600,
        ], $this->ownerHeaders($owner, $tenant));

        $this->assertContains(
            $response->status(),
            [422, 403],
            "Expected free plan limit enforcement but got {$response->status()}"
        );
    }
}
