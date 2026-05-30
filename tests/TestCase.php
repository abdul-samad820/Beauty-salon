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

        // Spatie permission cache clear karo
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles seed karo
        $this->seedRoles();
    }

    /**
     * Test tenant banao
     */
    protected function createTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name'      => 'Test Salon',
            'slug'      => 'test-salon',
            'subdomain' => 'test-salon',
            'email'     => 'salon@test.com',
            'phone'     => '9876543210',
            'plan'      => 'free',
            'status'    => 'active',
            'settings'  => [
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
     * Owner user banao aur tenant bind karo
     */
    protected function createOwner(Tenant $tenant, array $overrides = []): User
    {
        $user = User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name'      => 'Test Owner',
            'email'     => 'owner@test.com',
            'phone'     => '9000000001',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ], $overrides));

        $user->assignRole('owner');
        return $user;
    }

    /**
     * Customer banao
     */
    protected function createCustomer(Tenant $tenant, array $overrides = []): User
    {
        static $counter = 0;
        $counter++;

        $user = User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name'      => "Customer $counter",
            'email'     => "customer{$counter}@test.com",
            'phone'     => "98000{$counter}0000",
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ], $overrides));

        $user->assignRole('customer');
        return $user;
    }

    /**
     * Staff member banao
     */
    protected function createStaff(Tenant $tenant, array $overrides = []): Staff
    {
        static $sCounter = 0;
        $sCounter++;

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => "Staff Member $sCounter",
            'email'     => "staff{$sCounter}@test.com",
            'phone'     => "97000{$sCounter}0000",
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('staff');

        return Staff::create(array_merge([
            'tenant_id'          => $tenant->id,
            'user_id'            => $user->id,
            'commission_percent' => 20,
            'specializations'    => ['hair', 'nail'],
            'working_hours'      => [
                'mon' => '09:00-18:00',
                'tue' => '09:00-18:00',
                'wed' => '09:00-18:00',
                'thu' => '09:00-18:00',
                'fri' => '09:00-18:00',
                'sat' => '09:00-18:00',
                'sun' => null,
            ],
            'is_available'       => true,
        ], $overrides));
    }

    /**
     * Service banao
     */
    protected function createService(Tenant $tenant, array $overrides = []): Service
    {
        static $svCounter = 0;
        $svCounter++;

        return Service::create(array_merge([
            'tenant_id'        => $tenant->id,
            'name'             => "Service $svCounter",
            'category'         => 'hair',
            'duration_minutes' => 60,
            'price'            => 500.00,
            'is_active'        => true,
        ], $overrides));
    }

    /**
     * Appointment banao
     */
    protected function createAppointment(Tenant $tenant, User $customer, Staff $staff, Service $service, array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'staff_id'         => $staff->id,
            'service_id'       => $service->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time'       => '10:00:00',
            'end_time'         => '11:00:00',
            'status'           => 'pending',
            'amount'           => $service->price,
        ], $overrides));
    }

    /**
     * Owner ke liye authenticated request — X-Tenant header ke saath
     */
    protected function ownerHeaders(User $owner, Tenant $tenant): array
    {
        $token = $owner->createToken('test')->plainTextToken;
        return [
            'Authorization' => "Bearer $token",
            'X-Tenant'      => $tenant->slug,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Customer ke liye authenticated request
     */
    protected function customerHeaders(User $customer, Tenant $tenant): array
    {
        $token = $customer->createToken('test')->plainTextToken;
        return [
            'Authorization' => "Bearer $token",
            'X-Tenant'      => $tenant->slug,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Sirf tenant header — no auth
     */
    protected function tenantHeaders(Tenant $tenant): array
    {
        return [
            'X-Tenant' => $tenant->slug,
            'Accept'   => 'application/json',
        ];
    }

    /**
     * App container mein currentTenant bind karo
     */
    protected function bindTenant(Tenant $tenant): void
    {
        app()->instance('currentTenant', $tenant);
    }

    private function seedRoles(): void
    {
        $roles = ['super_admin', 'owner', 'staff', 'customer'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
