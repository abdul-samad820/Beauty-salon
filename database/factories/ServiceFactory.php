<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement([
                'Hair Cut',
                'Hair Spa',
                'Facial',
                'Manicure',
                'Pedicure',
            ]),
            'category' => fake()->randomElement([
                'hair',
                'skin',
                'nail',
                'bridal',
                'massage',
            ]),
            'description' => fake()->sentence(),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'price' => fake()->numberBetween(200, 3000),
            'is_active' => true,
        ];
    }
}
