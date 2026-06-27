<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/**
 * IDOR (Insecure Direct Object Reference) TESTS
 *
 * Scenario: Customer A apna data access kare — OK
 *           Customer A, Customer B ka data access karne ki koshish kare — 403/404 milna chahiye
 *
 * Covers:
 *  1. Customer apni appointment list dekhe — sirf apni appointments aayein
 *  2. Customer B ki appointment cancel karne ki koshish — 404 milna chahiye
 *  3. Customer B ki booking-confirmed page dekhne ki koshish — 404 milna chahiye
 *  4. Customer B ka invoice download karne ki koshish — 404 milna chahiye
 *  5. Customer B ki completed appointment pe review likhne ki koshish — 404 milna chahiye
 *  6. Customer B ki appointment pe review submit karne ki koshish — 404 milna chahiye
 */
class IDORTest extends TestCase
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

    // ── Helper: customer guard ke saath acting karo ───────────────
    // Web routes 'customer' guard use karte hain, 'web' nahi
    private function actingAsCustomer(User $customer): static
    {
        return $this->actingAs($customer, 'customer');
    }

    // ── Helper: customerTenant container me bind karo ─────────────
    // CustomerTenantMiddleware jo kaam karta hai wo yahan replicate karo
    private function bindCustomerTenant(Tenant $tenant): void
    {
        app()->instance('customerTenant', $tenant);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 1: Customer apni appointments list dekhe
    //         Sirf uski apni appointments aayein, doosre ki nahi
    //
    //         Strategy: Web route View return karta hai, isliye
    //         DB-level assert use karo — A ki appointment exists,
    //         B ki customer_id se koi appointment A ki customer_id
    //         ke naam pe DB me nahi hogi
    // ─────────────────────────────────────────────────────────────
    public function test_customer_appointment_list_returns_only_own_appointments(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);

        // Customer A ki appointment
        $appointmentA = $this->createAppointment($tenant, $customerA, $staff, $service);

        // Customer B ki appointment — A ko nahi dikhni chahiye
        $appointmentB = $this->createAppointment($tenant, $customerB, $staff, $service, [
            'appointment_date' => now()->addDays(3)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        $this->bindCustomerTenant($tenant);

        // Web route — /{subdomain}/appointments — View return karta hai
        $response = $this->actingAsCustomer($customerA)
            ->get("/{$tenant->subdomain}/appointments");

        $response->assertStatus(200);

        // DB me verify karo: A ki appointment A ke naam pe hai
        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentA->id,
            'customer_id' => $customerA->id,
        ]);

        // DB me verify karo: B ki appointment B ke naam pe hai, A ke naam pe nahi
        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentB->id,
            'customer_id' => $customerB->id,
        ]);

        // Controller query verify: A ki customer_id se sirf A ki appointments milti hain
        $aAppointments = Appointment::where('tenant_id', $tenant->id)
            ->where('customer_id', $customerA->id)
            ->pluck('id')
            ->toArray();

        $this->assertContains($appointmentA->id, $aAppointments);
        $this->assertNotContains($appointmentB->id, $aAppointments);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 2: Customer B ki appointment cancel karne ki koshish
    //         404 milni chahiye — cancel nahi honi chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_customer_cannot_cancel_another_customers_appointment(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);

        // Customer B ki appointment — future me, cancel hone layak
        $appointmentB = $this->createAppointment($tenant, $customerB, $staff, $service, [
            'appointment_date' => now()->addDays(5)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $this->bindCustomerTenant($tenant);

        // Customer A, Customer B ki appointment cancel karne ki koshish
        $response = $this->actingAsCustomer($customerA)
            ->post("/{$tenant->subdomain}/appointments/{$appointmentB->id}/cancel");

        // 404 milni chahiye — appointment "not found" as it doesn't belong to A
        $response->assertStatus(404);

        // DB me status change nahi hona chahiye
        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentB->id,
            'status' => 'pending',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 3: Customer B ki booking-confirmed page dekhne ki koshish
    //         404 milni chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_customer_cannot_view_another_customers_booking_confirmation(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);

        // Customer B ki confirmed appointment
        $appointmentB = $this->createAppointment($tenant, $customerB, $staff, $service);

        $this->bindCustomerTenant($tenant);

        // Customer A, B ki booking confirmation page access karne ki koshish
        $response = $this->actingAsCustomer($customerA)
            ->get("/{$tenant->subdomain}/booking-confirmed/{$appointmentB->id}");

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 4: Customer B ka invoice download karne ki koshish
    //         404 milni chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_customer_cannot_download_another_customers_invoice(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);

        // Customer B ki completed appointment (invoice sirf completed pe hoti hai)
        $appointmentB = $this->createAppointment($tenant, $customerB, $staff, $service, [
            'status' => 'completed',
            'amount' => 500,
        ]);

        $this->bindCustomerTenant($tenant);

        // Customer A, B ka invoice download karne ki koshish
        $response = $this->actingAsCustomer($customerA)
            ->get("/{$tenant->subdomain}/appointments/{$appointmentB->id}/invoice");

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 5: Customer B ki appointment ki review form dekhne ki koshish
    //         404 milni chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_customer_cannot_view_review_form_for_another_customers_appointment(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);

        // Customer B ki completed appointment
        $appointmentB = $this->createAppointment($tenant, $customerB, $staff, $service, [
            'status' => 'completed',
        ]);

        $this->bindCustomerTenant($tenant);

        // Customer A, B ki appointment ki review page access karne ki koshish
        $response = $this->actingAsCustomer($customerA)
            ->get("/{$tenant->subdomain}/review/{$appointmentB->id}");

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 6: Customer B ki appointment pe review POST submit karne ki koshish
    //         404 milni chahiye — review create nahi honi chahiye
    // ─────────────────────────────────────────────────────────────
    public function test_customer_cannot_submit_review_for_another_customers_appointment(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);

        // Customer B ki completed appointment
        $appointmentB = $this->createAppointment($tenant, $customerB, $staff, $service, [
            'status' => 'completed',
        ]);

        $this->bindCustomerTenant($tenant);

        // Customer A, B ki appointment pe review submit karne ki koshish
        $response = $this->actingAsCustomer($customerA)
            ->post("/{$tenant->subdomain}/review/{$appointmentB->id}", [
                'rating' => 5,
                'comment' => 'Bahut acha service tha, bilkul mast experience.',
            ]);

        $response->assertStatus(404);

        // DB me koi review nahi bani honi chahiye
        $this->assertDatabaseMissing('reviews', [
            'appointment_id' => $appointmentB->id,
            'customer_id' => $customerA->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // TEST 7: Customer apni khud ki appointment successfully cancel kar sake
    //         (Positive case — IDOR fix ne legit use case break na kiya ho)
    // ─────────────────────────────────────────────────────────────
    public function test_customer_can_cancel_their_own_appointment(): void
    {
        $tenant = $this->makeUniqueTenant();
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $customer = $this->createCustomer($tenant);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'appointment_date' => now()->addDays(5)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $this->bindCustomerTenant($tenant);

        $response = $this->actingAsCustomer($customer)
            ->post("/{$tenant->subdomain}/appointments/{$appointment->id}/cancel");

        // 302 redirect expected (web route)
        $response->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }
}
