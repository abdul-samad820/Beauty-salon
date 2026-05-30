<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_owner_routes(): void
    {
        $response = $this->getJson(
            '/api/v1/owner/products',
            ['X-Tenant' => 'demo']
        );

        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_customer_routes(): void
    {
        $response = $this->getJson(
            '/api/v1/customer/appointments',
            ['X-Tenant' => 'demo']
        );

        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->getJson(
            '/api/v1/admin/tenants',
            ['X-Tenant' => 'demo']
        );

        $response->assertStatus(401);
    }
}