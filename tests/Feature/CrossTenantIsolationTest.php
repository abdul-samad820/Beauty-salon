<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Product;
use App\Models\Service;
use Tests\TestCase;

/**
 * CROSS-TENANT DATA ISOLATION TESTS
 *
 * Verifies that tenant A cannot read, modify, or delete data belonging to tenant B.
 * This is the most critical security boundary in a multi-tenant SaaS application.
 * A single failure here is a full data-breach.
 */
class CrossTenantIsolationTest extends TestCase
{
    // ──────────────────────────────────────────────
    // SERVICES
    // ──────────────────────────────────────────────

    public function test_owner_cannot_read_another_tenants_services(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $this->createService($tenantB); // belongs to B

        $this->bindTenant($tenantA);

        $response = $this->getJson('/api/v1/owner/services', $this->ownerHeaders($ownerA, $tenantA));

        $response->assertStatus(200);
        // Tenant A's service list must be empty — B's service must not leak
        $this->assertCount(0, $response->json('data.data'));
    }

    public function test_owner_cannot_update_another_tenants_service(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $serviceB = $this->createService($tenantB);

        $this->bindTenant($tenantA);

        $response = $this->putJson(
            "/api/v1/owner/services/{$serviceB->id}",
            ['name' => 'Hacked Name', 'price' => 1, 'duration_minutes' => 30, 'category' => 'hair'],
            $this->ownerHeaders($ownerA, $tenantA)
        );

        // Must be 404 (not found within tenant A's scope) — not 200
        $response->assertStatus(404);
        $this->assertDatabaseHas('services', ['id' => $serviceB->id, 'name' => $serviceB->name]);
    }

    public function test_owner_cannot_delete_another_tenants_service(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $serviceB = $this->createService($tenantB);

        $this->bindTenant($tenantA);

        $response = $this->deleteJson(
            "/api/v1/owner/services/{$serviceB->id}",
            [],
            $this->ownerHeaders($ownerA, $tenantA)
        );

        $response->assertStatus(404);
        $this->assertDatabaseHas('services', ['id' => $serviceB->id]);
    }

    // ──────────────────────────────────────────────
    // APPOINTMENTS
    // ──────────────────────────────────────────────

    public function test_owner_cannot_see_another_tenants_appointments(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $customerB = $this->createCustomer($tenantB);
        $staffB = $this->createStaff($tenantB);
        $serviceB = $this->createService($tenantB);
        $this->createAppointment($tenantB, $customerB, $staffB, $serviceB);

        $this->bindTenant($tenantA);

        $response = $this->getJson('/api/v1/owner/appointments', $this->ownerHeaders($ownerA, $tenantA));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data')); // AppointmentController uses ->get() not paginate
    }

    public function test_owner_cannot_change_status_of_another_tenants_appointment(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $customerB = $this->createCustomer($tenantB);
        $staffB = $this->createStaff($tenantB);
        $serviceB = $this->createService($tenantB);
        $apptB = $this->createAppointment($tenantB, $customerB, $staffB, $serviceB);

        $this->bindTenant($tenantA);

        $response = $this->patchJson(
            "/api/v1/owner/appointments/{$apptB->id}/status",
            ['status' => 'confirmed'],
            $this->ownerHeaders($ownerA, $tenantA)
        );

        $response->assertStatus(404);
        $this->assertDatabaseHas('appointments', ['id' => $apptB->id, 'status' => 'pending']);
    }

    // ──────────────────────────────────────────────
    // PRODUCTS
    // ──────────────────────────────────────────────

    public function test_owner_cannot_read_another_tenants_products(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);

        Product::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Tenant B Shampoo',
            'category' => 'hair',
            'price' => 200,
            'quantity' => 50,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $this->bindTenant($tenantA);

        $response = $this->getJson('/api/v1/owner/products', $this->ownerHeaders($ownerA, $tenantA));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data.data'));
    }

    // ──────────────────────────────────────────────
    // CUSTOMER — cannot book into another tenant's staff/service
    // ──────────────────────────────────────────────

    public function test_customer_cannot_book_using_another_tenants_service_id(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $customerA = $this->createCustomer($tenantA);
        $staffA = $this->createStaff($tenantA);
        $serviceB = $this->createService($tenantB); // belongs to B

        $this->bindTenant($tenantA);

        $response = $this->postJson(
            '/api/v1/customer/appointments',
            [
                'service_id' => $serviceB->id,
                'staff_id' => $staffA->id,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00',
            ],
            $this->customerHeaders($customerA, $tenantA)
        );

        // service_id validation uses Rule::exists scoped to currentTenant — must fail
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_id']);
    }

    // ──────────────────────────────────────────────
    // X-Tenant header cross-access blocked
    // ──────────────────────────────────────────────

    public function test_user_of_tenant_a_is_denied_when_sending_tenant_b_header(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $token = $ownerA->createToken('test')->plainTextToken;

        // Owner A's token but X-Tenant points at B
        $response = $this->getJson('/api/v1/owner/services', [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenantB->slug,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────
    // STAFF / USER — special case: User model has no
    // BelongsToTenant global scope (unlike Service, Product,
    // Appointment, Commission, Staff). Isolation here relies
    // entirely on manual where('tenant_id', ...) in each
    // controller. This test guards against a future controller
    // forgetting that filter.
    // ──────────────────────────────────────────────

    public function test_owner_cannot_update_another_tenants_staff_member(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $staffB = $this->createStaff($tenantB);

        $this->actingAs($ownerA)
            ->put("/owner/staff/{$staffB->id}", [
                'name' => 'Hacked Name',
                'phone' => '9999999999',
                'commission_percent' => 10,
            ])
            ->assertStatus(404);

        $this->assertDatabaseHas('users', [
            'id' => $staffB->user->id,
            'name' => $staffB->user->name,
        ]);
    }

    public function test_owner_cannot_deactivate_another_tenants_staff_member(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);
        $staffB = $this->createStaff($tenantB);

        $this->actingAs($ownerA)
            ->delete("/owner/staff/{$staffB->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('users', [
            'id' => $staffB->user->id,
            'is_active' => true,
        ]);
    }
}
