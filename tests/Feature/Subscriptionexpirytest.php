<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use Tests\TestCase;

class SubscriptionExpiryTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 10a: Trial tenant with expired trial and no active subscription
    //           is redirected away from owner dashboard
    // ──────────────────────────────────────────────

    public function test_expired_trial_tenant_is_redirected_to_expired_page(): void
    {
        $tenant = $this->createTenant([
            'trial_ends_at' => now()->subDay(), // trial expired yesterday
            'status' => 'active',
        ]);
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        // Simulate web session login
        $response = $this->actingAs($owner)
            ->get('/owner/dashboard');

        // Should be redirected to subscription expired page — not 200
        $response->assertRedirect(route('owner.subscription.expired'));
    }

    // ──────────────────────────────────────────────
    // TEST 10b: Tenant with active subscription can access dashboard
    //           even if trial_ends_at is in the past
    // ──────────────────────────────────────────────

    public function test_tenant_with_active_subscription_can_access_dashboard(): void
    {
        $tenant = $this->createTenant([
            'trial_ends_at' => now()->subDay(),
            'status' => 'active',
        ]);
        $owner = $this->createOwner($tenant);

        // Create a Plan first
        $plan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'monthly_price' => 999,
            'yearly_price' => 9999,
            'max_staff' => 5,
            'max_services' => 20,
            'max_appointments' => 100,
            'features' => [],
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'amount' => 999,
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->addMonth(), // active subscription
        ]);

        $this->bindTenant($tenant);

        $response = $this->actingAs($owner)
            ->get('/owner/dashboard');

        // Must NOT redirect to expired — should pass through
        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // TEST 10c: Suspended tenant is blocked regardless of subscription
    // ──────────────────────────────────────────────

    public function test_suspended_tenant_is_blocked_from_owner_dashboard(): void
    {
        $tenant = $this->createTenant(['status' => 'suspended']);
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $response = $this->actingAs($owner)
            ->get('/owner/dashboard');

        $response->assertRedirect(route('owner.subscription.expired'));
    }

    // ──────────────────────────────────────────────
    // TEST 10d: Tenant with trial still active can access dashboard
    // ──────────────────────────────────────────────

    public function test_tenant_within_trial_period_can_access_dashboard(): void
    {
        $tenant = $this->createTenant([
            'trial_ends_at' => now()->addDays(7),
            'status' => 'active',
        ]);
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $response = $this->actingAs($owner)
            ->get('/owner/dashboard');

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // TEST 10e: Expired subscription (status = 'expired') blocks access
    // ──────────────────────────────────────────────

    public function test_expired_subscription_blocks_dashboard_access(): void
    {
        $tenant = $this->createTenant([
            'trial_ends_at' => now()->subDays(30),
            'status' => 'active',
        ]);
        $owner = $this->createOwner($tenant);

        $plan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic-exp',
            'monthly_price' => 999,
            'yearly_price' => 9999,
            'max_staff' => 5,
            'max_services' => 20,
            'max_appointments' => 100,
            'features' => [],
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'expired',
            'amount' => 999,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDays(5), // already expired
        ]);

        $this->bindTenant($tenant);

        $response = $this->actingAs($owner)
            ->get('/owner/dashboard');

        // trial expired AND subscription expired → blocked
        $response->assertRedirect(route('owner.subscription.expired'));
    }
}
