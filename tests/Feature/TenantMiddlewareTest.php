<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * TENANT MIDDLEWARE TESTS
 * X-Tenant header validation, inactive tenant, wrong tenant access
 */
class TenantMiddlewareTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 33: X-Tenant header nahi bheja toh 400
    // ──────────────────────────────────────────────
    public function test_missing_x_tenant_header_returns_400(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $token  = $owner->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/v1/owner/services', [
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/json',
            // X-Tenant missing!
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Tenant identifier missing. X-Tenant header required.']);
    }

    // ──────────────────────────────────────────────
    // TEST 34: Galat/non-existent tenant slug pe 404
    // ──────────────────────────────────────────────
    public function test_invalid_tenant_slug_returns_404(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $token  = $owner->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/v1/owner/services', [
            'Authorization' => "Bearer $token",
            'X-Tenant'      => 'non-existent-salon',
            'Accept'        => 'application/json',
        ]);

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Tenant not found or inactive.']);
    }

    // ──────────────────────────────────────────────
    // TEST 35: Suspended tenant pe 404
    // ──────────────────────────────────────────────
    public function test_suspended_tenant_returns_404(): void
    {
        $tenant = $this->createTenant([
            'slug'      => 'suspended-salon',
            'subdomain' => 'suspended-salon',
            'status'    => 'suspended', // suspended tenant
        ]);
        $owner = $this->createOwner($tenant);
        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/v1/owner/services', [
            'Authorization' => "Bearer $token",
            'X-Tenant'      => 'suspended-salon',
            'Accept'        => 'application/json',
        ]);

        $response->assertStatus(404);
    }
}
