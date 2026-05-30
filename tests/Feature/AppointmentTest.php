<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppointmentTest extends TestCase
{
    public function test_appointments_require_authentication()
    {
        $response = $this->getJson(
            '/api/v1/owner/appointments',
            [
                'X-Tenant' => 'demo',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_today_appointments_require_authentication()
    {
        $response = $this->getJson(
            '/api/v1/owner/appointments/today',
            [
                'X-Tenant' => 'demo',
            ]
        );

        $response->assertStatus(401);
    }
}
