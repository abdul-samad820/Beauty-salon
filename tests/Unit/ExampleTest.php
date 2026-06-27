<?php

namespace Tests\Unit;

use Tests\TestCase; // FIX: Laravel TestCase chahiye, PHPUnit TestCase nahi

class ExampleTest extends TestCase
{
    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_owner_dashboard(): void
    {
        $response = $this->get('/owner/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_health_check_endpoint_returns_ok(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'healthy']);
    }

    public function test_superadmin_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
