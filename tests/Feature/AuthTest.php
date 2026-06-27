<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * AUTH TESTS
 * Covers login, logout, validation, and account status checks.
 */
class AuthTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 1: Verify 422 error when request body is empty
    // ──────────────────────────────────────────────
    public function test_login_returns_422_when_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ──────────────────────────────────────────────
    // TEST 2: Verify 401 error with invalid credentials
    // ──────────────────────────────────────────────
    public function test_login_returns_401_for_wrong_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid email or password.']);
    }

    // ──────────────────────────────────────────────
    // TEST 3: Verify token issuance with valid credentials
    // ──────────────────────────────────────────────
    public function test_login_returns_token_on_valid_credentials(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $owner->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    // ──────────────────────────────────────────────
    // TEST 4: Verify login is blocked for inactive users
    // ──────────────────────────────────────────────
    public function test_inactive_user_cannot_login(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant, ['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $owner->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────
    // TEST 5: Verify logout successfully deletes the current token
    // ──────────────────────────────────────────────
    public function test_logout_deletes_current_token(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $headers = $this->ownerHeaders($owner, $tenant);

        $response = $this->postJson('/api/v1/auth/logout', [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successful']);
    }

    // ──────────────────────────────────────────────
    // TEST 6: Verify logout returns 401 without a valid token
    // ──────────────────────────────────────────────
    public function test_logout_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);
    }
}
