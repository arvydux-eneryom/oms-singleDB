<?php

namespace Tests\Feature\Sidebar;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MyAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $systemUser;
    protected Tenant $tenant;
    protected Role $superAdminSystemRole;
    protected Role $superAdminTenantRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->superAdminSystemRole = Role::create(['name' => 'super-admin-for-system', 'guard_name' => 'web', 'is_system' => true]);
        $this->superAdminTenantRole = Role::create(['name' => 'super-admin-for-tenant', 'guard_name' => 'web', 'is_system' => false]);
        Role::create(['name' => 'admin-for-tenant', 'guard_name' => 'web', 'is_system' => false]);

        // Create system user with super-admin-for-system role
        $this->systemUser = User::factory()->create([
            'is_system' => true,
            'is_tenant' => true,
            'system_id' => 1,
        ]);
        $this->systemUser->assignRole('super-admin-for-system');

        // Create a tenant
        $this->tenant = Tenant::create(['name' => 'Test Tenant']);

        // Create domain for tenant
        $this->tenant->domains()->create([
            'domain' => 'test.localhost',
            'name' => 'Test Domain',
            'subdomain' => 'test',
            'system_id' => $this->systemUser->system_id,
        ]);

        // Attach user to tenant
        $this->tenant->users()->attach($this->systemUser->id);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_my_account_link_shows_when_in_tenant_context_and_user_has_super_admin_system_role(): void
    {
        tenancy()->initialize($this->tenant);

        $this->actingAs($this->systemUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('My account');
        $response->assertSee(config('app.url') . '/dashboard');
    }

    public function test_my_account_link_hidden_when_not_in_tenant_context(): void
    {
        $this->actingAs($this->systemUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('My account', false);
    }

    public function test_my_account_link_hidden_when_user_does_not_have_super_admin_system_role(): void
    {
        tenancy()->initialize($this->tenant);

        // Create tenant user with different role
        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);
        $tenantUser->assignRole('admin-for-tenant');
        $this->tenant->users()->attach($tenantUser->id);

        $this->actingAs($tenantUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('My account', false);
    }

    public function test_my_account_link_hidden_when_user_has_super_admin_tenant_role_not_system(): void
    {
        tenancy()->initialize($this->tenant);

        // Create tenant user with super-admin-for-tenant role (not system)
        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);
        $tenantUser->assignRole('super-admin-for-tenant');
        $this->tenant->users()->attach($tenantUser->id);

        $this->actingAs($tenantUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('My account', false);
    }

    public function test_my_account_link_points_to_system_dashboard(): void
    {
        tenancy()->initialize($this->tenant);

        $this->actingAs($this->systemUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee(config('app.url') . '/dashboard', false);
    }

    public function test_my_account_link_has_target_blank_attribute(): void
    {
        tenancy()->initialize($this->tenant);

        $this->actingAs($this->systemUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('target="_blank"', false);
    }

    public function test_both_conditions_must_be_true_for_link_to_show(): void
    {
        // Test 1: User has role but not in tenant context
        $this->actingAs($this->systemUser);
        $response = $this->get('/dashboard');
        $response->assertDontSee('My account', false);

        // Test 2: In tenant context but user doesn't have role
        tenancy()->initialize($this->tenant);
        $regularUser = User::factory()->create(['is_tenant' => true, 'system_id' => 1]);
        $regularUser->assignRole('admin-for-tenant');
        $this->tenant->users()->attach($regularUser->id);

        $this->actingAs($regularUser);
        $response = $this->get('/dashboard');
        $response->assertDontSee('My account', false);
    }
}
