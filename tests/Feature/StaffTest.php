<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * STAFF TESTS
 * Covers staff CRUD operations.
 */
class StaffTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 16: Verify owner can fetch staff list
    // ──────────────────────────────────────────────
    public function test_owner_can_list_staff(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $this->createStaff($tenant);
        $this->createStaff($tenant);

        $response = $this->getJson('/api/v1/owner/staff', $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 17: Verify owner can add new staff
    // ──────────────────────────────────────────────
    public function test_owner_can_add_new_staff(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'Sneha Mehta',
            'email' => 'sneha@salon.com',
            'phone' => '9500000099',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'commission_percent' => 25,
            'specializations' => ['hair', 'bridal'],
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(201)
            ->assertJson(['message' => 'Staff added successfully']);

        $this->assertDatabaseHas('users', ['email' => 'sneha@salon.com']);
        $this->assertDatabaseHas('staff', ['commission_percent' => 25]);
    }

    // ──────────────────────────────────────────────
    // TEST 18: Verify owner can update staff commission
    // ──────────────────────────────────────────────
    public function test_owner_can_update_staff_commission(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $staff = $this->createStaff($tenant, ['commission_percent' => 20]);
        $this->bindTenant($tenant);

        $response = $this->putJson("/api/v1/owner/staff/{$staff->id}", [
            'commission_percent' => 30,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200);
        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'commission_percent' => 30,
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 19: Verify owner can delete staff
    // ──────────────────────────────────────────────
    public function test_owner_can_delete_staff(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $staff = $this->createStaff($tenant);
        $this->bindTenant($tenant);

        $response = $this->deleteJson("/api/v1/owner/staff/{$staff->id}",
            [], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Staff removed successfully']);

        $this->assertSoftDeleted('staff', ['id' => $staff->id]);
    }

    // ──────────────────────────────────────────────
    // TEST 20: Verify 404 error when fetching non-existent staff
    // ──────────────────────────────────────────────
    public function test_fetching_nonexistent_staff_returns_404(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $this->bindTenant($tenant);

        $response = $this->getJson('/api/v1/owner/staff/99999',
            $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(404);
    }
}
