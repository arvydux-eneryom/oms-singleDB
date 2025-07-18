<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        DB::table('permissions')->insert([
            ['name' => 'create_super_admin', 'guard_name' => 'web'],
            ['name' => 'create_admin', 'guard_name' => 'web'],
            ['name' => 'create_site_manager', 'guard_name' => 'web'],
            ['name' => 'create_site_foreman', 'guard_name' => 'web'],
            ['name' => 'view_project', 'guard_name' => 'web'],
            ['name' => 'view_project_details', 'guard_name' => 'web'],
            ['name' => 'view_data_entry', 'guard_name' => 'web'],


/*
            Potential permissions to add later

            ['name' => 'View data entry list', 'guard_name' => 'web'],
            ['name' => 'View data entry details', 'guard_name' => 'web'],
            ['name' => 'Create data entry', 'guard_name' => 'web'],
            ['name' => 'Edit data entry', 'guard_name' => 'web'],
            ['name' => 'Delete data entry', 'guard_name' => 'web'],
            ['name' => 'View user', 'guard_name' => 'web'],
            ['name' => 'View user details', 'guard_name' => 'web'],
            ['name' => 'Create user', 'guard_name' => 'web'],
            ['name' => 'Edit user', 'guard_name' => 'web'],
            ['name' => 'Delete user', 'guard_name' => 'web'],
            ['name' => 'View role', 'guard_name' => 'web'],
            ['name' => 'View role details', 'guard_name' => 'web'],
            ['name' => 'Create role', 'guard_name' => 'web'],
            ['name' => 'Edit role', 'guard_name' => 'web'],
            ['name' => 'Delete role', 'guard_name' => 'web'],
            ['name' => 'View permission', 'guard_name' => 'web'],
            ['name' => 'View permission details', 'guard_name' => 'web'],
            ['name' => 'Create permission', 'guard_name' => 'web'],
            ['name' => 'Edit permission', 'guard_name' => 'web'],
            ['name' => 'Delete permission', 'guard_name' => 'web'],
            ['name' => 'View subdomain', 'guard_name' => 'web'],
            ['name' => 'View subdomain details', 'guard_name' => 'web'],
            ['name' => 'Create subdomain', 'guard_name' => 'web'],
            ['name' => 'Edit subdomain', 'guard_name' => 'web'],
            ['name' => 'Delete subdomain', 'guard_name' => 'web'],*/
        ]);
    }
}
