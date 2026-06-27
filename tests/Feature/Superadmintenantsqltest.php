<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SUPER ADMIN TENANT TESTS
 *
 * Covers:
 *  14. SuperAdmin SQL bug in tenant show() — DATE_FORMAT() is MySQL-only
 *      and crashes on SQLite (the default test DB). This test verifies
 *      that the show() endpoint returns 200 without crashing.
 *
 * Also covers general SuperAdmin authentication and tenant listing
 * to raise coverage of routes that were completely untested.
 */
class SuperAdminTenantSQLTest extends TestCase
{
    private function createSuperAdmin(): User
    {
        $user = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@platform.com',
            'phone' => '9999999999',
            'password' => bcrypt('admin1234'),
            'is_active' => true,
        ]);

        // The role name used in TenantMiddleware is 'super_admin'
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user->assignRole('superadmin');

        return $user;
    }

    // ──────────────────────────────────────────────
    // TEST 14a: SuperAdmin can view tenant list (web)
    // ──────────────────────────────────────────────

    public function test_superadmin_can_view_tenant_list(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)
            ->get('/superadmin/tenants');

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // TEST 14b: SuperAdmin tenant show() does NOT crash (SQL bug regression)
    //
    // The bug: DATE_FORMAT() in monthlyRevenue query is MySQL-only.
    // On SQLite (test env) it throws a QueryException.
    // If this test passes, either the query was fixed (e.g. strftime)
    // or the DB driver supports it.  If it fails, the bug is confirmed.
    // ──────────────────────────────────────────────

    public function test_superadmin_tenant_show_does_not_crash(): void
    {
        $admin = $this->createSuperAdmin();
        $tenant = $this->createTenant();

        // Create some completed appointments so the monthly revenue query
        // actually executes against real rows
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);

        $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
            'amount' => 1500,
        ]);

        $response = $this->actingAs($admin)
            ->get("/superadmin/tenants/{$tenant->id}");

        // Must return 200 — not 500 from DATE_FORMAT SQL crash
        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // TEST 14c: Non-superadmin cannot access superadmin routes
    // ──────────────────────────────────────────────

    public function test_regular_owner_cannot_access_superadmin_tenant_list(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->actingAs($owner)
            ->get('/superadmin/tenants');

        // Must be redirected or forbidden — not 200
        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────
    // TEST 14d: Unauthenticated users cannot access superadmin routes
    // ──────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_superadmin_routes(): void
    {
        $response = $this->get('/superadmin/tenants');

        $response->assertRedirect('/login');
    }

    // ──────────────────────────────────────────────
    // TEST 14e: SuperAdmin API tenant list returns JSON
    // ──────────────────────────────────────────────

    public function test_superadmin_api_tenant_list_returns_json(): void
    {
        $admin = $this->createSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $this->createTenant(['slug' => 'salon-api-a', 'subdomain' => 'salon-api-a', 'email' => 'api-a@test.com']);
        $this->createTenant(['slug' => 'salon-api-b', 'subdomain' => 'salon-api-b', 'email' => 'api-b@test.com']);

        $response = $this->getJson('/api/v1/admin/tenants', [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 14f: SuperAdmin can create a new tenant via web
    // ──────────────────────────────────────────────

    public function test_superadmin_can_create_tenant_via_web(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)
            ->post('/superadmin/tenants', [
                'business_name' => 'New Salon',
                'subdomain' => 'new-salon',
                'owner_name' => 'Jane Doe',
                'owner_email' => 'jane@newsalon.com',
                'phone' => '8888888888',
                'plan' => 'free',
                '_token' => csrf_token(),
            ]);

        // Should redirect after successful creation
        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', ['subdomain' => 'new-salon']);
        $this->assertDatabaseHas('users', ['email' => 'jane@newsalon.com']);
    }
}
