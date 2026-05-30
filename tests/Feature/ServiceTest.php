<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * SERVICE TESTS
 * CRUD + validation + tenant isolation
 */
class ServiceTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 10: Owner apni services dekh sakta hai
    // ──────────────────────────────────────────────
    public function test_owner_can_list_services(): void
    {
        $tenant  = $this->createTenant();
        $owner   = $this->createOwner($tenant);
        $this->createService($tenant, ['name' => 'Facial Treatment']);
        $this->createService($tenant, ['name' => 'Hair Color']);

        $response = $this->getJson('/api/v1/owner/services', $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 11: Nayi service create karna
    // ──────────────────────────────────────────────
    public function test_owner_can_create_service(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/services', [
            'name'             => 'Deep Cleansing Facial',
            'category'         => 'skin',
            'duration_minutes' => 60,
            'price'            => 1200,
            'description'      => 'Professional facial',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Service created successfully');

        $this->assertDatabaseHas('services', [
            'name'      => 'Deep Cleansing Facial',
            'tenant_id' => $tenant->id,
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 12: Invalid category pe validation fail
    // ──────────────────────────────────────────────
    public function test_service_creation_fails_with_invalid_category(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/services', [
            'name'             => 'Invalid Service',
            'category'         => 'cooking', // invalid category
            'duration_minutes' => 45,
            'price'            => 500,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['category']);
    }

    // ──────────────────────────────────────────────
    // TEST 13: Service update karna
    // ──────────────────────────────────────────────
    public function test_owner_can_update_service(): void
    {
        $tenant  = $this->createTenant();
        $owner   = $this->createOwner($tenant);
        $service = $this->createService($tenant, ['name' => 'Basic Hair Cut']);
        $this->bindTenant($tenant);

        $response = $this->putJson("/api/v1/owner/services/{$service->id}", [
            'price' => 750,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200);
        $this->assertDatabaseHas('services', ['id' => $service->id, 'price' => 750]);
    }

    // ──────────────────────────────────────────────
    // TEST 14: Service delete karna
    // ──────────────────────────────────────────────
    public function test_owner_can_delete_service(): void
    {
        $tenant  = $this->createTenant();
        $owner   = $this->createOwner($tenant);
        $service = $this->createService($tenant);
        $this->bindTenant($tenant);

        $response = $this->deleteJson("/api/v1/owner/services/{$service->id}",
            [], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Service deleted successfully']);

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }

    // ──────────────────────────────────────────────
    // TEST 15: Unauthenticated user services access nahi kar sakta
    // ──────────────────────────────────────────────
    public function test_unauthenticated_user_cannot_access_services(): void
    {
        $tenant = $this->createTenant();

        $response = $this->getJson('/api/v1/owner/services',
            $this->tenantHeaders($tenant));

        $response->assertStatus(401);
    }
}
