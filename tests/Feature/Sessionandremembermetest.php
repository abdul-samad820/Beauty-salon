<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * SESSION & REMEMBER ME TESTS
 *
 * Covers:
 *  D4 — Session Regeneration: login ke baad session ID badal jaaye
 *  D5 — Remember Me: remember=true se long-lived cookie mile
 */
class SessionAndRememberMeTest extends TestCase
{
    // ── Helper: unique tenant + owner banao ───────────────────────
    private function makeOwnerWithTenant(): array
    {
        $uid = uniqid();
        $tenant = $this->createTenant([
            'name' => 'Test Salon '.$uid,
            'slug' => 'test-salon-'.$uid,
            'subdomain' => 'test-salon-'.$uid,
            'email' => 'salon-'.$uid.'@test.com',
        ]);
        $owner = $this->createOwner($tenant);

        return [$tenant, $owner];
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D4: Session Regeneration
    // Login ke baad session ID change hona chahiye
    // (Session Fixation attack se bachav)
    // ─────────────────────────────────────────────────────────────
    public function test_session_id_regenerates_after_login(): void
    {
        [, $owner] = $this->makeOwnerWithTenant();

        // Login se pehle session ID capture karo
        $this->get('/login'); // session start karo
        $sessionBefore = session()->getId();

        // Login karo
        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        // Login ke baad session ID alag hona chahiye
        $sessionAfter = session()->getId();

        $this->assertNotEquals(
            $sessionBefore,
            $sessionAfter,
            'Session ID login ke baad regenerate nahi hua — Session Fixation risk hai'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D4b: Logout ke baad session invalidate hona chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_session_is_invalidated_after_logout(): void
    {
        [, $owner] = $this->makeOwnerWithTenant();

        // Login karo
        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        $sessionAfterLogin = session()->getId();

        // Logout karo
        $this->post('/logout');

        // Session ID change honi chahiye
        $sessionAfterLogout = session()->getId();

        $this->assertNotEquals(
            $sessionAfterLogin,
            $sessionAfterLogout,
            'Session logout ke baad invalidate nahi hua'
        );

        // Auth bhi clear hona chahiye
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D5: Remember Me — remember=true se long-lived cookie mile
    // ─────────────────────────────────────────────────────────────
    public function test_remember_me_sets_remember_token_in_database(): void
    {
        [, $owner] = $this->makeOwnerWithTenant();

        // remember=true ke saath login karo
        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password',
            'remember' => true,
        ]);

        // DB me remember_token set hona chahiye
        $this->assertNotNull(
            User::find($owner->id)->remember_token,
            'Remember Me token DB me save nahi hua'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D5b: Remember Me = false — token set nahi hona chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_without_remember_me_no_token_is_set(): void
    {
        [, $owner] = $this->makeOwnerWithTenant();

        // remember ke bina login karo
        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password',
            // remember field nahi hai
        ]);

        // remember_token null rehna chahiye
        $this->assertNull(
            User::find($owner->id)->remember_token,
            'Remember Me token bina request ke bhi set ho gaya'
        );
    }
}
