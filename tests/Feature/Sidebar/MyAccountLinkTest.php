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

    public function test_my_account_link_shows_when_in_tenant_context_and_user_is_system_user(): void
    {
        tenancy()->initialize($this->tenant);

        $this->actingAs($this->systemUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('My account');
        $response->assertSee(config('app.url').'/dashboard');
    }

    public function test_my_account_link_hidden_when_not_in_tenant_context(): void
    {
        $this->actingAs($this->systemUser);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('My account', false);
    }

    public function test_my_account_link_hidden_when_user_is_not_system_user(): void
    {
        tenancy()->initialize($this->tenant);

        // Create tenant-only user (is_system = false)
        $tenantUser = User::factory()->create([
            'is_system' => false,
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

    public function test_my_account_link_hidden_regardless_of_role_if_not_system_user(): void
    {
        tenancy()->initialize($this->tenant);

        // Create tenant user with super-admin-for-tenant role but is_system = false
        $tenantUser = User::factory()->create([
            'is_system' => false, // This is the key - regardless of role
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
        $response->assertSee(config('app.url').'/dashboard', false);
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
        // Test 1: User is system user but not in tenant context
        $this->actingAs($this->systemUser);
        $response = $this->get('/dashboard');
        $response->assertDontSee('My account', false);

        // Test 2: In tenant context but user is not system user
        tenancy()->initialize($this->tenant);
        $regularUser = User::factory()->create([
            'is_system' => false, // Not a system user
            'is_tenant' => true,
            'system_id' => 1,
        ]);
        $regularUser->assignRole('admin-for-tenant');
        $this->tenant->users()->attach($regularUser->id);

        $this->actingAs($regularUser);
        $response = $this->get('/dashboard');
        $response->assertDontSee('My account', false);
    }

    public function test_visibility_uses_is_system_method(): void
    {
        // Verify isSystem() method returns correct values
        $this->assertTrue($this->systemUser->isSystem());

        $tenantOnlyUser = User::factory()->create([
            'is_system' => false,
            'is_tenant' => true,
        ]);
        $this->assertFalse($tenantOnlyUser->isSystem());

        // Now verify link shows correctly based on isSystem()
        tenancy()->initialize($this->tenant);

        // System user should see link
        $this->actingAs($this->systemUser);
        $response = $this->get('/dashboard');
        $response->assertSee('My account');

        // Tenant-only user should not see link
        $tenantOnlyUser->assignRole('admin-for-tenant');
        $this->tenant->users()->attach($tenantOnlyUser->id);
        $this->actingAs($tenantOnlyUser);
        $response = $this->get('/dashboard');
        $response->assertDontSee('My account', false);
    }
}
