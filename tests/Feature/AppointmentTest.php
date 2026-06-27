<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * APPOINTMENT TESTS
 * Tests for booking, cancellation, status updates, and authentication checks.
 */
class AppointmentTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 21: Verify appointments cannot be accessed without authentication
    // ──────────────────────────────────────────────
    public function test_owner_appointments_require_authentication(): void
    {
        $tenant = $this->createTenant();

        $this->getJson('/api/v1/owner/appointments', $this->tenantHeaders($tenant))
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // TEST 22: Verify owner can fetch today's appointments
    // ──────────────────────────────────────────────
    public function test_owner_can_fetch_todays_appointments(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
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
    // TEST 23: Verify appointment status update from 'pending' to 'confirmed'
    // ──────────────────────────────────────────────
    public function test_owner_can_confirm_appointment(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'confirmed',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Appointment status updated to confirmed');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 24: Verify cancelled appointment status cannot be updated
    // ──────────────────────────────────────────────
    public function test_cancelled_appointment_status_cannot_be_updated(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
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
    // TEST 25: Verify customer can cancel their own appointment
    // ──────────────────────────────────────────────
    public function test_customer_can_cancel_their_appointment(): void
    {
        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/customer/appointments/{$appointment->id}/cancel",
            [], $this->customerHeaders($customer, $tenant));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Appointment cancelled successfully.']);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 26: Verify completed appointment cannot be cancelled
    // ──────────────────────────────────────────────
    public function test_completed_appointment_cannot_be_cancelled(): void
    {
        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'completed',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/customer/appointments/{$appointment->id}/cancel",
            [], $this->customerHeaders($customer, $tenant));

        $response->assertStatus(400);
    }

    // ──────────────────────────────────────────────
    // TEST 27: Verify customer can view their appointment list
    // ──────────────────────────────────────────────
    public function test_customer_can_view_their_appointments(): void
    {
        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->getJson('/api/v1/customer/appointments',
            $this->customerHeaders($customer, $tenant));

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    // ──────────────────────────────────────────────
    // TEST 28: Verify 422 error on invalid status value
    // ──────────────────────────────────────────────
    public function test_appointment_status_update_rejects_invalid_status(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'flying', // invalid
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ──────────────────────────────────────────────
    // TEST 29: Verify status field is required for update
    // ──────────────────────────────────────────────
    public function test_appointment_status_update_requires_status(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
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
    // TEST 30: Verify updating a non-existent appointment returns 404
    // ──────────────────────────────────────────────
    public function test_updating_nonexistent_appointment_returns_404(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

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
    // TEST 31: Verify customer cannot cancel a non-existent appointment
    // ──────────────────────────────────────────────
    public function test_customer_cannot_cancel_nonexistent_appointment(): void
    {
        $tenant = $this->createTenant();
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
    // TEST 32: Verify completed appointment status cannot be changed
    // ──────────────────────────────────────────────
    public function test_completed_appointment_status_cannot_be_changed(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);

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
    // TEST 33: Verify customer appointment list can be empty
    // ──────────────────────────────────────────────
    public function test_customer_receives_empty_appointments_list(): void
    {
        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);

        $this->bindTenant($tenant);

        $response = $this->getJson(
            '/api/v1/customer/appointments',
            $this->customerHeaders($customer, $tenant)
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
            ]);
    }

    // ──────────────────────────────────────────────
    // CHECKED_IN / NO_SHOW STATUS TESTS
    // ──────────────────────────────────────────────

    public function test_owner_can_mark_appointment_checked_in(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'checked_in',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Appointment status updated to checked_in');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'checked_in',
        ]);
    }

    public function test_checked_in_appointment_can_be_marked_completed(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'checked_in',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'completed',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);
    }

    public function test_owner_can_mark_appointment_no_show(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'no_show',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Appointment status updated to no_show');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'no_show',
        ]);
    }

    public function test_no_show_appointment_status_cannot_be_changed(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'no_show',
        ]);
        $this->bindTenant($tenant);

        $response = $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'confirmed',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(400);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'no_show',
        ]);
    }

    public function test_no_show_appointment_does_not_generate_commission(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'checked_in',
        ]);
        $this->bindTenant($tenant);

        $this->patchJson("/api/v1/owner/appointments/{$appointment->id}/status", [
            'status' => 'no_show',
        ], $this->ownerHeaders($owner, $tenant));

        $this->assertDatabaseMissing('commissions', [
            'appointment_id' => $appointment->id,
        ]);
    }
}
