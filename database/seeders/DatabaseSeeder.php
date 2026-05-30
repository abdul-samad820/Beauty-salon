<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pehle roles/permissions seed karo
        $this->call(RolesAndPermissionsSeeder::class);

        // Super Admin user banao
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@lumiere.app'],
            [
                'tenant_id' => null,           // SA ka koi tenant nahi
                'name' => 'Super Admin',
                'password' => Hash::make('lumiere@2026'),  // Production me .env se lo
                'phone' => null,
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super_admin');
    }
}
