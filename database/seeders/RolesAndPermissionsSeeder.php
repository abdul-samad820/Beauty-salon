<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permission cache clear
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $webPermissions = [
            'manage services',
            'manage staff',
            'manage appointments',
            'manage inventory',
            'manage products',
            'view analytics',
            'manage commissions',
            'access superadmin dashboard', // Added clean operational permission for Super Admin middleware mapping
        ];

        foreach ($webPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Dedicated Customer permissions registered strictly on the 'customer' guard context
        $customerPermissions = [
            'manage customer appointments',
        ];

        foreach ($customerPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'customer', // Bound to its isolated guard perimeter
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles Configuration Matrix
        |--------------------------------------------------------------------------
        */

        // FIXED: Dropped the underscore to match 'superadmin' middleware validation check explicitly
        $superAdmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web',
        ]);

        $owner = Role::firstOrCreate([
            'name' => 'owner',
            'guard_name' => 'web',
        ]);

        $staff = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]);

        // FIXED SEC-004: Shifted customer role to 'customer' guard name to block cross-guard session hijack bugs
        $customer = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'customer',
        ]);

        $owner->syncPermissions([
            'manage services',
            'manage staff',
            'manage appointments',
            'manage inventory',
            'manage products',
            'view analytics',
            'manage commissions',
        ]);

        $staff->syncPermissions([
            'manage appointments',
        ]);

        // Enforce customer specific permissions over its standalone guard loop
        $customer->syncPermissions([
            'manage customer appointments',
        ]);

        // Super Admin receives all web context operations
        $superAdmin->syncPermissions(
            Permission::where('guard_name', 'web')->pluck('name')->toArray()
        );
    }
}
