<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerHistoryTest extends TestCase
{
    public function test_owner_can_view_customer_list(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $this->createCustomer($tenant);
        $this->bindTenant($tenant);

        $response = $this->actingAs($owner)->get('/owner/customers');

        $response->assertStatus(200);
        $response->assertViewIs('owner.customers.index');
    }

    public function test_customer_list_shows_lifetime_revenue_and_visit_count(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['price' => 500]);

        $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
            'amount' => 500,
        ]);
        $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
            'amount' => 500,
        ]);

        $this->bindTenant($tenant);
        $response = $this->actingAs($owner)->get('/owner/customers');

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) use ($customer) {
            $row = $customers->firstWhere('id', $customer->id);

            return $row
                && $row->visit_count === 2
                && (float) $row->lifetime_revenue === 1000.0;
        });
    }

    public function test_owner_can_view_customer_detail_history(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['price' => 750]);

        $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
            'amount' => 750,
        ]);

        $this->bindTenant($tenant);
        $response = $this->actingAs($owner)->get("/owner/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertViewIs('owner.customers.show');
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_visits'] === 1
                && (float) $stats['lifetime_revenue'] === 750.0;
        });
    }

    public function test_customer_detail_identifies_preferred_staff(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staffA = $this->createStaff($tenant);
        $staffB = $this->createStaff($tenant);
        $service = $this->createService($tenant);

        // Customer visits staffA twice, staffB once — staffA should be preferred.
        $this->createAppointment($tenant, $customer, $staffA, $service, ['status' => 'completed']);
        $this->createAppointment($tenant, $customer, $staffA, $service, ['status' => 'completed']);
        $this->createAppointment($tenant, $customer, $staffB, $service, ['status' => 'completed']);

        $this->bindTenant($tenant);
        $response = $this->actingAs($owner)->get("/owner/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertViewHas('preferredStaff', function ($preferredStaff) use ($staffA) {
            return $preferredStaff && $preferredStaff->staff_id === $staffA->id;
        });
    }

    public function test_owner_cannot_view_another_tenants_customer_history(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $customerB = $this->createCustomer($tenantB);

        $this->bindTenant($tenantA);
        $response = $this->actingAs($ownerA)->get("/owner/customers/{$customerB->id}");

        $response->assertStatus(404);
    }

    public function test_customer_search_filters_by_name(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $this->createCustomer($tenant, ['name' => 'Aarav Sharma', 'email' => 'aarav@test.com', 'phone' => '9000000020']);
        $this->createCustomer($tenant, ['name' => 'Priya Mehta', 'email' => 'priya@test.com', 'phone' => '9000000021']);

        $this->bindTenant($tenant);
        $response = $this->actingAs($owner)->get('/owner/customers?search=Aarav');

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->total() === 1
                && $customers->first()->name === 'Aarav Sharma';
        });
    }

    public function test_no_show_does_not_count_toward_lifetime_revenue(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['price' => 500]);

        $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'no_show',
            'amount' => 500,
        ]);

        $this->bindTenant($tenant);
        $response = $this->actingAs($owner)->get("/owner/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_visits'] === 0
                && (float) $stats['lifetime_revenue'] === 0.0
                && $stats['no_show_count'] === 1;
        });
    }
}
