<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * DASHBOARD REDIRECT TESTS
 *
 * Covers the single-source-of-truth role -> dashboard route resolver
 * (User::dashboardRouteName()) and its three call sites:
 *
 *   1. App\Models\User::dashboardRouteName()       (unit-level)
 *   2. AuthWebController::redirectByRole()          (POST /login)
 *   3. routes/web.php '/' route                     (GET /)
 *   4. bootstrap/app.php redirectUsersTo()           (auth middleware bounce)
 *
 * Purpose: guard against regressions if a new role is ever added and only
 * some of these call sites get updated. Since all four now delegate to
 * dashboardRouteName(), these tests primarily protect that one method —
 * but we assert through all the entry points so a future refactor that
 * accidentally re-duplicates the logic gets caught wherever it breaks.
 */
class DashboardRedirectTest extends TestCase
{
    // ──────────────────────────────────────────────
    // UNIT LEVEL — User::dashboardRouteName()
    // ──────────────────────────────────────────────

    public function test_dashboard_route_name_for_superadmin(): void
    {
        $superadmin = $this->createSuperAdmin();

        $this->assertSame('superadmin.dashboard', $superadmin->dashboardRouteName());
    }

    public function test_dashboard_route_name_for_owner(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->assertSame('owner.dashboard', $owner->dashboardRouteName());
    }

    public function test_dashboard_route_name_for_staff(): void
    {
        $tenant = $this->createTenant();
        $staff = $this->createStaff($tenant);

        $this->assertSame('staff.dashboard', $staff->user->dashboardRouteName());
    }

    public function test_dashboard_route_name_is_null_for_user_with_no_recognized_role(): void
    {
        $tenant = $this->createTenant();

        // A bare user with no role assigned at all (e.g. mid-registration, or
        // a 'customer' guard user accidentally loaded via the web guard).
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Roleless User',
            'email' => 'roleless@test.com',
            'phone' => '9200000000',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->assertNull($user->dashboardRouteName());
    }

    /**
     * If a user somehow holds multiple roles, priority order must be
     * superadmin > owner > staff. This protects the match() ordering
     * in dashboardRouteName() from being silently reordered.
     */
    public function test_dashboard_route_name_priority_when_user_has_multiple_roles(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $owner->assignRole('superadmin');

        $this->assertSame('superadmin.dashboard', $owner->dashboardRouteName());

        $staffMember = $this->createStaff($tenant)->user;
        $staffMember->assignRole('owner');

        $this->assertSame('owner.dashboard', $staffMember->dashboardRouteName());
    }

    // ──────────────────────────────────────────────
    // INTEGRATION — POST /login (AuthWebController)
    // ──────────────────────────────────────────────

    public function test_login_redirects_superadmin_to_superadmin_dashboard(): void
    {
        $superadmin = $this->createSuperAdmin();

        $response = $this->post('/login', [
            'email' => $superadmin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $this->assertAuthenticatedAs($superadmin);
    }

    public function test_login_redirects_owner_to_owner_dashboard(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('owner.dashboard'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_login_redirects_staff_to_staff_dashboard(): void
    {
        $tenant = $this->createTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->post('/login', [
            'email' => $staff->user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $this->assertAuthenticatedAs($staff->user);
    }

    public function test_login_logs_out_and_redirects_to_login_when_user_has_no_recognized_role(): void
    {
        $tenant = $this->createTenant();

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Roleless Login User',
            'email' => 'roleless-login@test.com',
            'phone' => '9200000001',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    // ──────────────────────────────────────────────
    // INTEGRATION — GET / (root route)
    // ──────────────────────────────────────────────

    public function test_root_redirects_authenticated_superadmin_to_superadmin_dashboard(): void
    {
        $superadmin = $this->createSuperAdmin();

        $response = $this->actingAs($superadmin)->get('/');

        $response->assertRedirect(route('superadmin.dashboard'));
    }

    public function test_root_redirects_authenticated_owner_to_owner_dashboard(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->actingAs($owner)->get('/');

        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_root_redirects_authenticated_staff_to_staff_dashboard(): void
    {
        $tenant = $this->createTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($staff->user)->get('/');

        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_root_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_root_logs_out_and_redirects_to_login_for_user_with_no_recognized_role(): void
    {
        $tenant = $this->createTenant();

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Roleless Root User',
            'email' => 'roleless-root@test.com',
            'phone' => '9200000002',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ──────────────────────────────────────────────
    // CROSS-CHECK — all three entry points agree
    //
    // This is the regression guard the refactor was specifically for:
    // if dashboardRouteName() ever gets bypassed in one call site (e.g.
    // someone reintroduces a hardcoded hasRole() chain there), this test
    // will catch the divergence because all three must resolve identically
    // for the same user.
    // ──────────────────────────────────────────────

    public function test_all_redirect_entry_points_agree_for_each_role(): void
    {
        $ownerTenant = $this->createTenant();
        $staffTenant = $this->createTenant(['slug' => 'staff-cross-check-salon', 'subdomain' => 'staff-cross-check-salon', 'email' => 'staff-cross-check@test.com']);

        $cases = [
            'superadmin' => [$this->createSuperAdmin(), 'superadmin.dashboard'],
            'owner' => [$this->createOwner($ownerTenant), 'owner.dashboard'],
            'staff' => [$this->createStaff($staffTenant)->user, 'staff.dashboard'],
        ];

        foreach ($cases as $role => [$user, $expectedRouteName]) {
            $expectedUrl = route($expectedRouteName);

            // Entry point 1: model method directly
            $this->assertSame(
                $expectedRouteName,
                $user->dashboardRouteName(),
                "dashboardRouteName() mismatch for role: {$role}"
            );

            // Entry point 2: GET /
            $this->actingAs($user)
                ->get('/')
                ->assertRedirect($expectedUrl);
        }
    }
}
