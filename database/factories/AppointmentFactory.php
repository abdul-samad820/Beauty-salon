<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $staff = Staff::factory()->create();

        $customer = User::factory()->create([
            'tenant_id' => $staff->tenant_id,
        ]);

        $service = Service::factory()->create([
            'tenant_id' => $staff->tenant_id,
        ]);

        return [
            'tenant_id' => $staff->tenant_id,
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'confirmed',
            'amount' => $service->price,
            'notes' => fake()->sentence(),
        ];
    }
}
