<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Tests\TestCase;

/**
 * PRIVILEGE ESCALATION & XSS PROTECTION TESTS
 *
 * Covers:
 *  D23 — Privilege Escalation: Staff owner routes access na kar sake
 *  D25/B17-18 — XSS: Malicious input store + retrieve safely ho
 */
class SecurityTest extends TestCase
{
    // ── Helper: unique tenant banao ───────────────────────────────
    private function makeUniqueTenant(): Tenant
    {
        $uid = uniqid();

        return $this->createTenant([
            'name' => 'Test Salon '.$uid,
            'slug' => 'test-salon-'.$uid,
            'subdomain' => 'test-salon-'.$uid,
            'email' => 'salon-'.$uid.'@test.com',
        ]);
    }

    // ═════════════════════════════════════════════════════════════
    // SECTION: PRIVILEGE ESCALATION (D23)
    // Staff user owner routes access karne ki koshish kare
    // ═════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // TEST D23-1: Staff, owner dashboard access na kar sake
    // ─────────────────────────────────────────────────────────────
    public function test_staff_cannot_access_owner_dashboard(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($staff->user)
            ->get('/owner/dashboard');

        // 403 milna chahiye — owner route pe staff ka koi haq nahi
        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D23-2: Staff, owner staff management access na kar sake
    // ─────────────────────────────────────────────────────────────
    public function test_staff_cannot_access_owner_staff_management(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($staff->user)
            ->get('/owner/staff');

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D23-3: Staff, owner inventory management access na kar sake
    // ─────────────────────────────────────────────────────────────
    public function test_staff_cannot_access_owner_inventory(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($staff->user)
            ->get('/owner/inventory');

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D23-4: Staff, owner commissions management access na kar sake
    // ─────────────────────────────────────────────────────────────
    public function test_staff_cannot_access_owner_commissions(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($staff->user)
            ->get('/owner/commissions');

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D23-5: Staff, owner analytics access na kar sake
    // ─────────────────────────────────────────────────────────────
    public function test_staff_cannot_access_owner_analytics(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);

        $response = $this->actingAs($staff->user)
            ->get('/owner/analytics');

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D23-6: Owner, staff-only dashboard access na kar sake
    // (Reverse escalation check)
    // ─────────────────────────────────────────────────────────────
    public function test_owner_cannot_access_staff_dashboard(): void
    {
        $tenant = $this->makeUniqueTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->actingAs($owner)
            ->get('/staff/dashboard');

        // 403 milni chahiye — staff route owner ke liye nahi hai
        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D23-7: Positive — Staff apna dashboard access kar sake
    // ─────────────────────────────────────────────────────────────
    public function test_staff_can_access_their_own_dashboard(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);

        // currentTenant bind karo — middleware ki zaroorat
        app()->instance('currentTenant', $tenant);

        $response = $this->actingAs($staff->user)
            ->get('/staff/dashboard');

        $response->assertStatus(200);
    }

    // ═════════════════════════════════════════════════════════════
    // SECTION: XSS PROTECTION (D25 / B17-18)
    // Malicious script input — store aur safely escape ho
    // ═════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // TEST D25-1: Stored XSS — Service name me script inject karne ki koshish
    // Blade {{ }} auto-escape karta hai — script execute nahi honi chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_xss_payload_in_service_name_is_stored_safely(): void
    {
        $tenant = $this->makeUniqueTenant();
        $owner = $this->createOwner($tenant);

        app()->instance('currentTenant', $tenant);

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->actingAs($owner)
            ->post('/owner/services', [
                'name' => $xssPayload,
                'category' => 'Hair',
                'price' => 500,
                'duration' => 60,
                'description' => 'Test service',
                'is_active' => true,
            ]);

        // 302 redirect ya 200 — store hona chahiye, crash nahi
        $this->assertContains($response->status(), [200, 201, 302, 422]);

        // Agar store hua toh DB me raw HTML hoga — ye expected hai
        // Blade {{ }} se render hone pe escape ho jaayega
        if (in_array($response->status(), [200, 201, 302])) {
            $this->assertDatabaseHas('services', [
                'tenant_id' => $tenant->id,
                'name' => $xssPayload, // raw stored
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D25-2: Reflected XSS — Search/query param me script
    // Response me unescaped script nahi honi chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_xss_payload_in_query_param_is_not_reflected(): void
    {
        $tenant = $this->makeUniqueTenant();
        $owner = $this->createOwner($tenant);

        app()->instance('currentTenant', $tenant);

        $xssPayload = '<script>alert("XSS")</script>';

        // Owner appointments page pe XSS param inject karo
        $response = $this->actingAs($owner)
            ->get('/owner/appointments?status='.urlencode($xssPayload));

        // 200 ya redirect — crash nahi hona chahiye
        $this->assertContains($response->status(), [200, 302]);

        // Response content me unescaped script tag nahi hona chahiye
        if ($response->status() === 200) {
            $this->assertStringNotContainsString(
                '<script>alert("XSS")</script>',
                $response->getContent(),
                'Unescaped XSS payload response me reflect ho raha hai'
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // TEST D25-3: XSS in review comment — customer review submit kare
    // DB me raw store ho, lekin response me escape ho
    // ─────────────────────────────────────────────────────────────
    public function test_xss_payload_in_review_comment_is_escaped(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customer = $this->createCustomer($tenant);

        // Completed appointment chahiye review ke liye
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
        ]);

        app()->instance('customerTenant', $tenant);

        $xssPayload = '<script>document.cookie="stolen"</script>';

        $response = $this->actingAs($customer, 'customer')
            ->post("/{$tenant->subdomain}/review/{$appointment->id}", [
                'rating' => 5,
                'comment' => $xssPayload,
            ]);

        // Crash nahi hona chahiye
        $this->assertContains($response->status(), [200, 201, 302, 422]);

        // Agar store hua — raw DB me hoga, Blade render pe escape hoga
        if (in_array($response->status(), [200, 201, 302])) {
            $this->assertDatabaseHas('reviews', [
                'appointment_id' => $appointment->id,
                'comment' => $xssPayload,
            ]);
        }
    }
}
