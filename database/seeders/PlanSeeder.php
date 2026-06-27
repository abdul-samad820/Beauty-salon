<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * File: database/seeders/PlanSeeder.php
 *
 * Run: php artisan db:seed --class=PlanSeeder
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Chhoti salons ke liye — basic features, limited bookings.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_staff' => 2,
                'max_services' => 5,
                'max_appointments_per_month' => 50,
                'inventory_enabled' => false,
                'analytics_enabled' => false,
                'commission_enabled' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Growing salons ke liye — inventory + commissions included.',
                'price_monthly' => 999,
                'price_yearly' => 9999,
                'max_staff' => 10,
                'max_services' => 25,
                'max_appointments_per_month' => 500,
                'inventory_enabled' => true,
                'analytics_enabled' => false,
                'commission_enabled' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Established chains — unlimited features, full analytics.',
                'price_monthly' => 2499,
                'price_yearly' => 24999,
                'max_staff' => 50,
                'max_services' => 100,
                'max_appointments_per_month' => 9999,
                'inventory_enabled' => true,
                'analytics_enabled' => true,
                'commission_enabled' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        $this->command->info(' Plans seeded: Free, Basic, Premium');
    }
}
