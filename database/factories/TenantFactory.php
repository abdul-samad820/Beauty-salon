<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'subdomain' => fake()->unique()->userName(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('98########'),
            'address' => fake()->address(),
            'plan' => 'premium',
            'status' => 'active',
            'settings' => [],
            'trial_ends_at' => now()->addDays(30),
        ];
    }
}
