<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleHasPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Get super-admin role ID
        $superAdminRole = DB::table('roles')
            ->where('name', 'super-admin-for-system')
            ->first();

        if (!$superAdminRole) {
            return;
        }

        // Get permission IDs
        $permissions = DB::table('permissions')->pluck('id', 'name');

        // Super-admin role permissions
        $superAdminPermissions = [
            'create_super_admin',
            'create_admin',
            'create_site_manager',
            'create_site_foreman',
            'view_project',
            'view_project_details',
            'view_data_entry',
        ];

        foreach ($superAdminPermissions as $permission) {
            if (isset($permissions[$permission])) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissions[$permission],
                    'role_id' => $superAdminRole->id,
                ]);
            }
        }
    }
}
