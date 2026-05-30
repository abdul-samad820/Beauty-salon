<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public function definition(): array
    {
        $tenant = Tenant::factory()->create();

        return [
            'tenant_id' => $tenant->id,

            'user_id' => User::factory()->create([
                'tenant_id' => $tenant->id,
            ])->id,

            'commission_percent' => fake()->numberBetween(10, 40),

            'specializations' => [
                'hair',
                'skin',
            ],

            'working_hours' => [
                'mon' => '09:00-18:00',
                'tue' => '09:00-18:00',
                'wed' => '09:00-18:00',
                'thu' => '09:00-18:00',
                'fri' => '09:00-18:00',
                'sat' => '09:00-18:00',
            ],

            'is_available' => true,
        ];
    }
}
