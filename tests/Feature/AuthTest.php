<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/**
 * AUTH TESTS
 * Login, logout, validation, inactive user
 */
class AuthTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 1: Empty body pe 422 validation error
    // ──────────────────────────────────────────────
    public function test_login_returns_422_when_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    // ──────────────────────────────────────────────
    // TEST 2: Galat credentials pe 401
    // ──────────────────────────────────────────────
    public function test_login_returns_401_for_wrong_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'notexist@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Email ya password galat hai.']);
    }

    // ──────────────────────────────────────────────
    // TEST 3: Sahi credentials pe token milta hai
    // ──────────────────────────────────────────────
    public function test_login_returns_token_on_valid_credentials(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $owner->email,
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
    // TEST 4: Inactive user ko login block hona chahiye
    // ──────────────────────────────────────────────
    public function test_inactive_user_cannot_login(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant, ['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $owner->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────
    // TEST 5: Logout — token delete ho jata hai
    // ──────────────────────────────────────────────
    public function test_logout_deletes_current_token(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $headers = $this->ownerHeaders($owner, $tenant);

        $response = $this->postJson('/api/v1/auth/logout', [], $headers);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logout successful']);
    }

    // ──────────────────────────────────────────────
    // TEST 6: Bina token ke logout pe 401
    // ──────────────────────────────────────────────
    public function test_logout_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);
    }
}
