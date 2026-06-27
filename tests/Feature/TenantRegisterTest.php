<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * TENANT REGISTER TESTS
 * Naya salon register karna
 */
class TenantRegisterTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 7: Successful registration
    // ──────────────────────────────────────────────
    public function test_tenant_registers_successfully(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'business_name' => 'Glamour Salon',
            'subdomain' => 'glamour-salon',
            'phone' => '9876543210',
            'address' => 'Mumbai, India',
            'name' => 'Priya Sharma',
            'email' => 'priya@glamour.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'tenant' => ['id', 'name', 'subdomain', 'status'],
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('tenants', ['subdomain' => 'glamour-salon']);
        $this->assertDatabaseHas('users', ['email' => 'priya@glamour.com']);
    }

    // ──────────────────────────────────────────────
    // TEST 8: Duplicate subdomain reject hona chahiye
    // ──────────────────────────────────────────────
    public function test_duplicate_subdomain_returns_422(): void
    {
        $this->createTenant(['subdomain' => 'my-salon', 'slug' => 'my-salon']);

        $response = $this->postJson('/api/v1/auth/register', [
            'business_name' => 'My Salon 2',
            'subdomain' => 'my-salon', // same subdomain
            'phone' => '9111111111',
            'name' => 'Owner Two',
            'email' => 'owner2@test.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    }

    // ──────────────────────────────────────────────
    // TEST 9: Password confirmation mismatch
    // ──────────────────────────────────────────────
    public function test_password_mismatch_returns_422(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'business_name' => 'Test Salon',
            'subdomain' => 'test-new',
            'phone' => '9000000000',
            'name' => 'Test Owner',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'different456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
