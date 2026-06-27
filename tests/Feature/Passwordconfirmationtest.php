<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * PASSWORD CONFIRMATION VALIDATION TESTS
 *
 * The TenantRegisterController uses 'password' => 'required|min:8|confirmed'.
 * These tests verify that the `password_confirmation` field is enforced correctly
 * and that a mismatch is rejected with a 422.
 *
 * Without this test, a developer could silently break or remove the `confirmed`
 * rule and users could set passwords without confirming them.
 */
class PasswordConfirmationTest extends TestCase
{
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Test Salon',
            'subdomain' => 'test-salon-pwd',
            'phone' => '9876543210',
            'name' => 'Salon Owner',
            'email' => 'pwdtest@test.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ], $overrides);
    }

    // ──────────────────────────────────────────────
    // TEST: password_confirmation mismatch → 422
    // ──────────────────────────────────────────────

    public function test_registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->validPayload([
            'password' => 'correct_password',
            'password_confirmation' => 'different_password',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ──────────────────────────────────────────────
    // TEST: missing password_confirmation → 422
    // ──────────────────────────────────────────────

    public function test_registration_fails_when_password_confirmation_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['password_confirmation']);

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ──────────────────────────────────────────────
    // TEST: empty password_confirmation → 422
    // ──────────────────────────────────────────────

    public function test_registration_fails_when_password_confirmation_is_empty(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->validPayload([
            'password_confirmation' => '',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ──────────────────────────────────────────────
    // TEST: password too short → 422
    // ──────────────────────────────────────────────

    public function test_registration_fails_when_password_is_too_short(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->validPayload([
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ──────────────────────────────────────────────
    // TEST: matching passwords succeed → 201
    // ──────────────────────────────────────────────

    public function test_registration_succeeds_when_passwords_match(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'tenant', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'pwdtest@test.com']);
    }

    // ──────────────────────────────────────────────
    // TEST: correct password is hashed in DB — never stored in plaintext
    // ──────────────────────────────────────────────

    public function test_password_is_stored_hashed_not_in_plaintext(): void
    {
        $this->postJson('/api/v1/auth/register', $this->validPayload([
            'email' => 'hashcheck@test.com',
        ]));

        $user = User::where('email', 'hashcheck@test.com')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('secret1234', $user->password);
        $this->assertTrue(Hash::check('secret1234', $user->password));
    }
}
