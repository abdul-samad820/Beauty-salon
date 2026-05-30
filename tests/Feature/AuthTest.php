<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_validation_required()
    {
        $response = $this->postJson(
            '/api/v1/auth/login',
            []
        );

        $response->assertStatus(422);
    }

    public function test_invalid_credentials_return_401()
    {
        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'email' => 'fake@test.com',
                'password' => 'wrong',
            ]
        );

        $response->assertStatus(401);
    }
}
