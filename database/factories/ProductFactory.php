<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(2, true),
            'category' => 'hair-care',
            'price' => fake()->numberBetween(100, 1000),
            'quantity' => fake()->numberBetween(5, 50),
            'low_stock_threshold' => 5,
            'is_active' => true,
        ];
    }
}
