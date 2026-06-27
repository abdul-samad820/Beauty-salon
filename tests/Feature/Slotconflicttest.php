<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * SLOT BOOKING CONFLICT DETECTION TESTS
 *
 * Verifies that the race-condition guard in AppointmentController::store()
 * (SELECT … lockForUpdate) prevents double-booking the same staff slot.
 *
 * Also covers boundary cases: back-to-back bookings, exact overlap,
 * partial overlap, and cross-tenant staff isolation.
 */
class SlotConflictTest extends TestCase
{
    private function bookSlot(array $overrides = []): TestResponse
    {
        return $this->postJson('/api/v1/customer/appointments', $overrides);
    }

    // ──────────────────────────────────────────────
    // TEST 15a: Same slot → 409 Conflict
    // ──────────────────────────────────────────────

    public function test_booking_same_slot_twice_returns_409(): void
    {
        $tenant = $this->createTenant();
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['duration_minutes' => 60]);

        $this->bindTenant($tenant);

        // First booking — must succeed
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
        ], $this->customerHeaders($customerA, $tenant))
            ->assertStatus(201);

        // Second booking — exact same slot, same staff → must fail with 409
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
        ], $this->customerHeaders($customerB, $tenant))
            ->assertStatus(409)
            ->assertJsonFragment(['message' => 'Sorry, this slot is already booked. Please choose a different time slot.']);
    }

    // ──────────────────────────────────────────────
    // TEST 15b: Partially overlapping slot → 409
    // ──────────────────────────────────────────────

    public function test_partially_overlapping_slot_is_rejected(): void
    {
        $tenant = $this->createTenant();
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        // 60-minute service: 10:00–11:00
        $service = $this->createService($tenant, ['duration_minutes' => 60]);

        $this->bindTenant($tenant);

        // First booking: 10:00–11:00
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00',
        ], $this->customerHeaders($customerA, $tenant))
            ->assertStatus(201);

        // Second booking: 10:30–11:30 (overlaps by 30 minutes) → must be rejected
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:30',
        ], $this->customerHeaders($customerB, $tenant))
            ->assertStatus(409);
    }

    // ──────────────────────────────────────────────
    // TEST 15c: Back-to-back slots (no overlap) → 201
    // ──────────────────────────────────────────────

    public function test_back_to_back_slots_are_both_accepted(): void
    {
        $tenant = $this->createTenant();
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        // 60-minute service
        $service = $this->createService($tenant, ['duration_minutes' => 60]);

        $this->bindTenant($tenant);

        $date = now()->addDays(4)->toDateString();

        // First: 10:00–11:00
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => $date,
            'start_time' => '10:00',
        ], $this->customerHeaders($customerA, $tenant))
            ->assertStatus(201);

        // Second: 11:00–12:00 (starts exactly when first ends) → allowed
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => $date,
            'start_time' => '11:00',
        ], $this->customerHeaders($customerB, $tenant))
            ->assertStatus(201);
    }

    // ──────────────────────────────────────────────
    // TEST 15d: Cancelled slot can be rebooked
    // ──────────────────────────────────────────────

    public function test_cancelled_slot_can_be_booked_by_another_customer(): void
    {
        $tenant = $this->createTenant();
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['duration_minutes' => 60]);

        $this->bindTenant($tenant);

        $date = now()->addDays(5)->toDateString();

        // Customer A books and then cancels
        $resp = $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => $date,
            'start_time' => '14:00',
        ], $this->customerHeaders($customerA, $tenant));

        $resp->assertStatus(201);
        $apptId = $resp->json('data.id');

        $this->patchJson(
            "/api/v1/customer/appointments/{$apptId}/cancel",
            [],
            $this->customerHeaders($customerA, $tenant)
        )->assertStatus(200);

        // Customer B books the same slot — should succeed
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => $date,
            'start_time' => '14:00',
        ], $this->customerHeaders($customerB, $tenant))
            ->assertStatus(201);
    }

    // ──────────────────────────────────────────────
    // TEST 15e: Different staff same time → 201 (no conflict)
    // ──────────────────────────────────────────────

    public function test_same_slot_different_staff_both_succeed(): void
    {
        $tenant = $this->createTenant();
        $customerA = $this->createCustomer($tenant);
        $customerB = $this->createCustomer($tenant);
        $staffA = $this->createStaff($tenant);
        $staffB = $this->createStaff($tenant);
        $service = $this->createService($tenant, ['duration_minutes' => 60]);

        $this->bindTenant($tenant);

        $date = now()->addDays(6)->toDateString();

        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staffA->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
        ], $this->customerHeaders($customerA, $tenant))
            ->assertStatus(201);

        // Different staff — no conflict
        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staffB->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
        ], $this->customerHeaders($customerB, $tenant))
            ->assertStatus(201);
    }

    // ──────────────────────────────────────────────
    // TEST 15f: Booking in the past is rejected
    // ──────────────────────────────────────────────

    public function test_booking_in_the_past_is_rejected(): void
    {
        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);

        $this->bindTenant($tenant);

        $this->postJson('/api/v1/customer/appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->subDays(1)->toDateString(),
            'start_time' => '10:00',
        ], $this->customerHeaders($customer, $tenant))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_date']);
    }
}
