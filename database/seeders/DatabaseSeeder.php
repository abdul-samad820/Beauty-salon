<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create the Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@lumiere.app'],
            [
                'tenant_id' => null,                          // Super Admins are not assigned to a tenant
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD') ?? throw new \RuntimeException('SUPERADMIN_PASSWORD must be set in .env before seeding')),
                'phone' => null,
                'is_active' => true,
            ]
        );

        $superAdmin->assignRole('superadmin');
    }
}
