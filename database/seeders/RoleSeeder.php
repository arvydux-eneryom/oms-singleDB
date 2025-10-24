<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Built-in roles for the system users
        DB::table('roles')->insert([
            ['name' => 'super-admin-for-system', 'guard_name' => 'web', 'is_system' => true],
            //   other 3 system roles will be added later. So let keep them commented for now.
            //   ['name' => 'admin-for-system', 'guard_name' => 'web', 'is_system' => true],
            //   ['name' => 'analyst', 'guard_name' => 'web', 'is_system' => true],
            //   ['name' => 'support-role', 'guard_name' => 'web', 'is_system' => true],
        ]);

        // Roles for the tenant users
        DB::table('roles')->insert([
            ['name' => 'super-admin-for-tenant', 'guard_name' => 'web', 'is_system' => false],
            ['name' => 'admin-for-tenant', 'guard_name' => 'web', 'is_system' => false],
            ['name' => 'manager', 'guard_name' => 'web', 'is_system' => false],
            ['name' => 'site-manager', 'guard_name' => 'web', 'is_system' => false],
            ['name' => 'foreman', 'guard_name' => 'web', 'is_system' => false],
        ]);
    }
}
