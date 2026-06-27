<?php

namespace Tests;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles
        $this->seedRoles();
    }

    /**
     * Create a test tenant.
     */
    protected function createTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Test Salon',
            'slug' => 'test-salon',
            'subdomain' => 'test-salon',
            'email' => 'salon@test.com',
            'phone' => '9876543210',
            'plan' => 'free',
            'status' => 'active',
            'settings' => [
                'working_hours' => [
                    'mon' => '09:00-18:00',
                    'tue' => '09:00-18:00',
                    'wed' => '09:00-18:00',
                    'thu' => '09:00-18:00',
                    'fri' => '09:00-18:00',
                    'sat' => '09:00-18:00',
                    'sun' => null,
                ],
                'timezone' => 'Asia/Kolkata',
            ],
        ], $overrides));
    }

    /**
     * Create an owner user and bind the tenant.
     */
    protected function createOwner(Tenant $tenant, array $overrides = []): User
    {
        $user = User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'phone' => '9000000001',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ], $overrides));

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole('owner');

        return $user;
    }

    /**
     * Create a superadmin user (platform-level, not tied to a specific tenant's
     * business data the way owner/staff are — tenant_id is nullable here).
     */
    protected function createSuperAdmin(array $overrides = []): User
    {
        static $saCounter = 0;
        $saCounter++;

        $user = User::create(array_merge([
            'name' => "Test SuperAdmin $saCounter",
            'email' => "superadmin{$saCounter}@test.com",
            'phone' => "9100000{$saCounter}00",
            'password' => bcrypt('password123'),
            'is_active' => true,
            'email_verified_at' => now(),
        ], $overrides));

        $user->assignRole('superadmin');

        return $user;
    }

    /**
     * Create a customer user.
     *
     * The 'customer' role lives on the 'customer' guard (isolated from 'web').
     * We must attach the Role model directly so Spatie does not look it up via
     * the User model default 'web' guard, which throws RoleDoesNotExist.
     */
    protected function createCustomer(Tenant $tenant, array $overrides = []): User
    {
        static $counter = 0;
        $counter++;

        $user = User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => "Customer $counter",
            'email' => "customer{$counter}@test.com",
            'phone' => "98000{$counter}0000",
            'password' => bcrypt('password123'),
            'is_active' => true,
        ], $overrides));

        // Production CustomerAuthController uses assignRole('customer') with default web guard.
        // We mirror that here so role middleware 'role:customer' (web guard) passes correctly.
        $user->assignRole('customer');

        return $user;
    }

    /**
     * Create a staff member.
     */
    protected function createStaff(Tenant $tenant, array $overrides = []): Staff
    {
        static $sCounter = 0;
        $sCounter++;

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => "Staff Member $sCounter",
            'email' => "staff{$sCounter}@test.com",
            'phone' => "97000{$sCounter}0000",
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('staff');

        return Staff::create(array_merge([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'commission_percent' => 20,
            'specializations' => ['hair', 'nail'],
            'working_hours' => [
                'mon' => '09:00-18:00',
                'tue' => '09:00-18:00',
                'wed' => '09:00-18:00',
                'thu' => '09:00-18:00',
                'fri' => '09:00-18:00',
                'sat' => '09:00-18:00',
                'sun' => null,
            ],
            'is_available' => true,
        ], $overrides));
    }

    /**
     * Create a service.
     */
    protected function createService(Tenant $tenant, array $overrides = []): Service
    {
        static $svCounter = 0;
        $svCounter++;

        return Service::create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => "Service $svCounter",
            'category' => 'hair',
            'duration_minutes' => 60,
            'price' => 500.00,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create an appointment.
     */
    protected function createAppointment(Tenant $tenant, User $customer, Staff $staff, Service $service, array $overrides = []): Appointment
    {
        static $apCounter = 0;
        $apCounter++;

        return Appointment::create(array_merge([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays($apCounter + 2)->toDateString(),
            'start_time' => sprintf('%02d:00:00', (8 + $apCounter) % 24),
            'end_time' => sprintf('%02d:00:00', (9 + $apCounter) % 24),
            'status' => 'pending',
            'amount' => $service->price,
        ], $overrides));
    }

    /**
     * Authenticated request headers for owner.
     */
    protected function ownerHeaders(User $owner, Tenant $tenant): array
    {
        $token = $owner->createToken('test')->plainTextToken;

        return [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Authenticated request headers for customer.
     */
    protected function customerHeaders(User $customer, Tenant $tenant): array
    {
        $token = $customer->createToken('test')->plainTextToken;

        return [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Headers with tenant slug only (no authentication).
     */
    protected function tenantHeaders(Tenant $tenant): array
    {
        return [
            'X-Tenant' => $tenant->slug,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Bind currentTenant instance in the application container.
     */
    protected function bindTenant(Tenant $tenant): void
    {
        app()->instance('currentTenant', $tenant);
    }

    /**
     * Seed initial roles.
     * Guards must match RolesAndPermissionsSeeder exactly:
     *   - superadmin, owner, staff → 'web' guard
     *   - customer                 → 'customer' guard
     */
    private function seedRoles(): void
    {
        // All roles on 'web' guard — matches how production assignRole() works.
        // The API route uses 'role:customer' (no guard arg) which defaults to web guard.
        $roles = ['superadmin', 'owner', 'staff', 'customer'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
