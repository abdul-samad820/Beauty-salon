<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    public function definition(): array
    {
        $appointment = Appointment::factory()->create();

        return [
            'tenant_id' => $appointment->tenant_id,
            'staff_id' => $appointment->staff_id,
            'appointment_id' => $appointment->id,
            'commission_amount' => fake()->numberBetween(50, 500),
            'status' => 'pending',
        ];
    }
}
