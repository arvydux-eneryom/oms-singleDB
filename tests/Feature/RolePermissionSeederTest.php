<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the seeders
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RoleSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RoleHasPermissionsSeeder']);
    }

    public function test_roles_table_has_correct_system_roles(): void
    {
        $this->assertDatabaseHas('roles', [
            'name' => 'super-admin-for-system',
            'guard_name' => 'web',
            'is_system' => true,
        ]);
    }

    public function test_roles_table_has_correct_tenant_roles(): void
    {
        $tenantRoles = ['super-admin-for-tenant', 'admin-for-tenant', 'manager', 'site-manager', 'foreman'];

        foreach ($tenantRoles as $roleName) {
            $this->assertDatabaseHas('roles', [
                'name' => $roleName,
                'guard_name' => 'web',
                'is_system' => false,
            ]);
        }
    }

    public function test_permissions_table_has_all_required_permissions(): void
    {
        $permissions = [
            'create_super_admin',
            'create_admin',
            'create_site_manager',
            'create_site_foreman',
            'view_project',
            'view_project_details',
            'view_data_entry',
        ];

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_super_admin_role_has_all_permissions(): void
    {
        $superAdminRole = DB::table('roles')
            ->where('name', 'super-admin-for-system')
            ->first();

        $this->assertNotNull($superAdminRole);

        $expectedPermissions = [
            'create_super_admin',
            'create_admin',
            'create_site_manager',
            'create_site_foreman',
            'view_project',
            'view_project_details',
            'view_data_entry',
        ];

        $assignedPermissions = DB::table('role_has_permissions')
            ->where('role_id', $superAdminRole->id)
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->pluck('permissions.name')
            ->toArray();

        $this->assertCount(count($expectedPermissions), $assignedPermissions);

        foreach ($expectedPermissions as $permission) {
            $this->assertContains($permission, $assignedPermissions);
        }
    }

    public function test_role_has_permissions_table_has_correct_structure(): void
    {
        $superAdminRole = DB::table('roles')
            ->where('name', 'super-admin-for-system')
            ->first();

        $this->assertNotNull($superAdminRole);

        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_id', $superAdminRole->id)
            ->get();

        $this->assertGreaterThan(0, $rolePermissions->count());

        foreach ($rolePermissions as $rolePermission) {
            $this->assertObjectHasProperty('permission_id', $rolePermission);
            $this->assertObjectHasProperty('role_id', $rolePermission);
            $this->assertEquals($superAdminRole->id, $rolePermission->role_id);
        }
    }

    public function test_all_role_permissions_reference_valid_permissions(): void
    {
        $rolePermissions = DB::table('role_has_permissions')->get();

        foreach ($rolePermissions as $rolePermission) {
            $permission = DB::table('permissions')
                ->where('id', $rolePermission->permission_id)
                ->first();

            $this->assertNotNull($permission, "Permission ID {$rolePermission->permission_id} does not exist");
        }
    }

    public function test_all_role_permissions_reference_valid_roles(): void
    {
        $rolePermissions = DB::table('role_has_permissions')->get();

        foreach ($rolePermissions as $rolePermission) {
            $role = DB::table('roles')
                ->where('id', $rolePermission->role_id)
                ->first();

            $this->assertNotNull($role, "Role ID {$rolePermission->role_id} does not exist");
        }
    }

    public function test_super_admin_role_has_exactly_seven_permissions(): void
    {
        $superAdminRole = DB::table('roles')
            ->where('name', 'super-admin-for-system')
            ->first();

        $this->assertNotNull($superAdminRole);

        $permissionCount = DB::table('role_has_permissions')
            ->where('role_id', $superAdminRole->id)
            ->count();

        $this->assertEquals(7, $permissionCount);
    }

    public function test_no_duplicate_role_permission_assignments(): void
    {
        $rolePermissions = DB::table('role_has_permissions')
            ->select('role_id', 'permission_id')
            ->get();

        $uniqueCombinations = $rolePermissions->unique(function ($item) {
            return $item->role_id . '-' . $item->permission_id;
        });

        $this->assertEquals(
            $rolePermissions->count(),
            $uniqueCombinations->count(),
            'Duplicate role-permission assignments found'
        );
    }
}
