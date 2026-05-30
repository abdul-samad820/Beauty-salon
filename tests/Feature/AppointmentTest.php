<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * APPOINTMENT TESTS
 * Book, cancel, status update, auth checks
 */
class AppointmentTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 21: Bina auth ke appointments nahi milte
    // ──────────────────────────────────────────────
    public function test_owner_appointments_require_authentication(): void
    {
        $tenant = $this->createTenant();

        $this->getJson('/api/v1/owner/appointments', $this->tenantHeaders($tenant))
             ->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // TEST 22: Today's appointments fetch karna
    // ──────────────────────────────────────────────
    public function test_owner_can_fetch_todays_appointments(): void
    {
        $tenant   = $this->createTenant();
        $owner    = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff    = $this->createStaff($tenant);
        $service  = $this->createService($tenant);
        $this->bindTenant($tenant);

        $this->createAppointment($tenant, $customer, $staff, $service, [
            'appointment_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/owner/appointments/today',
            $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'date', 'total', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 23: Appointment status update — pending → confirmed
    // ──────────────────────────────────────────────
    public function test_owner_can_confirm_appointment(): void
    {
        $tenant      = $this->createTenant();
        $owner       = $this->createOwner($tenant);
        $customer    = $this->createCustomer($tenant);
        $staff       = $this->createStaff($tenant);
        $service     = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'confirmed',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Status updated to confirmed');

        $this->assertDatabaseHas('appointments', [
            'id'     => $appointment->id,
            'status' => 'confirmed',
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 24: Cancelled appointment ka status change nahi hota
    // ──────────────────────────────────────────────
    public function test_cancelled_appointment_status_cannot_be_updated(): void
    {
        $tenant      = $this->createTenant();
        $owner       = $this->createOwner($tenant);
        $customer    = $this->createCustomer($tenant);
        $staff       = $this->createStaff($tenant);
        $service     = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'cancelled',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'confirmed',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(400);
    }

    // ──────────────────────────────────────────────
    // TEST 25: Customer apni booking cancel kar sakta hai
    // ──────────────────────────────────────────────
    public function test_customer_can_cancel_their_appointment(): void
    {
        $tenant      = $this->createTenant();
        $customer    = $this->createCustomer($tenant);
        $staff       = $this->createStaff($tenant);
        $service     = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/customer/appointments/{$appointment->id}/cancel",
            [], $this->customerHeaders($customer, $tenant));

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Appointment cancelled successfully']);

        $this->assertDatabaseHas('appointments', [
            'id'     => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 26: Completed appointment cancel nahi hoti
    // ──────────────────────────────────────────────
    public function test_completed_appointment_cannot_be_cancelled(): void
    {
        $tenant      = $this->createTenant();
        $customer    = $this->createCustomer($tenant);
        $staff       = $this->createStaff($tenant);
        $service     = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/customer/appointments/{$appointment->id}/cancel",
            [], $this->customerHeaders($customer, $tenant));

        $response->assertStatus(400);
    }

    // ──────────────────────────────────────────────
    // TEST 27: Customer apni appointments list dekh sakta hai
    // ──────────────────────────────────────────────
    public function test_customer_can_view_their_appointments(): void
    {
        $tenant   = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff    = $this->createStaff($tenant);
        $service  = $this->createService($tenant);
        $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->getJson('/api/v1/customer/appointments',
            $this->customerHeaders($customer, $tenant));

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 28: Invalid status value pe 422
    // ──────────────────────────────────────────────
    public function test_appointment_status_update_rejects_invalid_status(): void
    {
        $tenant      = $this->createTenant();
        $owner       = $this->createOwner($tenant);
        $customer    = $this->createCustomer($tenant);
        $staff       = $this->createStaff($tenant);
        $service     = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'flying', // invalid
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }
    // ──────────────────────────────────────────────
// TEST 29: Appointment status required hai
// ──────────────────────────────────────────────
public function test_appointment_status_update_requires_status(): void
{
    $tenant      = $this->createTenant();
    $owner       = $this->createOwner($tenant);
    $customer    = $this->createCustomer($tenant);
    $staff       = $this->createStaff($tenant);
    $service     = $this->createService($tenant);
    $appointment = $this->createAppointment($tenant, $customer, $staff, $service);

    $this->bindTenant($tenant);

    $response = $this->patchJson(
        "/api/v1/owner/appointments/{$appointment->id}/status",
        [],
        $this->ownerHeaders($owner, $tenant)
    );

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['status']);
}

// ──────────────────────────────────────────────
// TEST 30: Non existing appointment update = 404
// ──────────────────────────────────────────────
public function test_updating_nonexistent_appointment_returns_404(): void
{
    $tenant = $this->createTenant();
    $owner  = $this->createOwner($tenant);

    $this->bindTenant($tenant);

    $response = $this->patchJson(
        '/api/v1/owner/appointments/999999/status',
        [
            'status' => 'confirmed',
        ],
        $this->ownerHeaders($owner, $tenant)
    );

    $response->assertStatus(404);
}

// ──────────────────────────────────────────────
// TEST 31: Customer cannot cancel nonexistent appointment
// ──────────────────────────────────────────────
public function test_customer_cannot_cancel_nonexistent_appointment(): void
{
    $tenant   = $this->createTenant();
    $customer = $this->createCustomer($tenant);

    $this->bindTenant($tenant);

    $response = $this->patchJson(
        '/api/v1/customer/appointments/999999/cancel',
        [],
        $this->customerHeaders($customer, $tenant)
    );

    $response->assertStatus(404);
}

// ──────────────────────────────────────────────
// TEST 32: Completed appointment status dobara update nahi hota
// ──────────────────────────────────────────────
public function test_completed_appointment_status_cannot_be_changed(): void
{
    $tenant      = $this->createTenant();
    $owner       = $this->createOwner($tenant);
    $customer    = $this->createCustomer($tenant);
    $staff       = $this->createStaff($tenant);
    $service     = $this->createService($tenant);

    $appointment = $this->createAppointment(
        $tenant,
        $customer,
        $staff,
        $service,
        [
            'status' => 'completed',
        ]
    );

    $this->bindTenant($tenant);

    $response = $this->patchJson(
        "/api/v1/owner/appointments/{$appointment->id}/status",
        [
            'status' => 'confirmed',
        ],
        $this->ownerHeaders($owner, $tenant)
    );

    $response->assertStatus(400);
}

// ──────────────────────────────────────────────
// TEST 33: Customer appointment list empty bhi aa sakti hai
// ──────────────────────────────────────────────
public function test_customer_receives_empty_appointments_list(): void
{
    $tenant   = $this->createTenant();
    $customer = $this->createCustomer($tenant);

    $this->bindTenant($tenant);

    $response = $this->getJson(
        '/api/v1/customer/appointments',
        $this->customerHeaders($customer, $tenant)
    );

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'data'
             ]);
}
}
