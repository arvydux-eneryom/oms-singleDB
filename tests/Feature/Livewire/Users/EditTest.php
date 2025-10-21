<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\Edit;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->assertStatus(200);
    }

    public function test_mount_initializes_user_data(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser]);

        $this->assertEquals('Test User', $component->instance()->name);
        $this->assertEquals('test@example.com', $component->instance()->email);
    }

    public function test_mount_loads_user_roles(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser]);

        $this->assertEquals('Admin', $component->instance()->userRoles);
    }

    public function test_mount_loads_available_roles(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Manager']);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser]);

        $this->assertNotEmpty($component->instance()->roles);
        $this->assertIsArray($component->instance()->roles);
    }

    public function test_mount_loads_not_assigned_subdomains(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        Domain::create([
            'domain' => 'test.localhost',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser]);

        $this->assertIsArray($component->instance()->notAssignedSubdomains);
    }

    public function test_save_updates_user_name_and_email(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'New Name')
            ->set('email', 'new@example.com')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $tenantUser->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_save_validates_required_fields(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', '')
            ->set('email', '')
            ->call('save')
            ->assertHasErrors(['name', 'email']);
    }

    public function test_save_validates_email_format(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_save_validates_unique_name_except_current_user(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Existing Name',
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Old Name',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Existing Name')
            ->set('email', 'test@example.com')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_save_validates_unique_email_except_current_user(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        User::factory()->create([
            'email' => 'existing@example.com',
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'email' => 'old@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_save_allows_keeping_same_name(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Same Name',
            'email' => 'test@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Same Name')
            ->set('email', 'test@example.com')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_save_allows_keeping_same_email(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'same@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Test User')
            ->set('email', 'same@example.com')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_save_updates_user_role(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $oldRole = Role::firstOrCreate(['name' => 'Admin']);
        $newRole = Role::firstOrCreate(['name' => 'Manager']);
        $tenantUser->assignRole($oldRole);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', $tenantUser->name)
            ->set('email', $tenantUser->email)
            ->set('userRoles', 'Manager')
            ->call('save')
            ->assertHasNoErrors();

        $tenantUser->refresh();
        $this->assertTrue($tenantUser->hasRole('Manager'));
        $this->assertFalse($tenantUser->hasRole('Admin'));
    }

    public function test_save_clears_existing_roles_before_assigning_new_one(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role1 = Role::firstOrCreate(['name' => 'Admin']);
        $role2 = Role::firstOrCreate(['name' => 'Manager']);
        $tenantUser->assignRole([$role1, $role2]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', $tenantUser->name)
            ->set('email', $tenantUser->email)
            ->set('userRoles', 'Admin')
            ->call('save');

        $tenantUser->refresh();
        $this->assertEquals(1, $tenantUser->roles()->count());
    }

    public function test_save_assigns_subdomain_to_user(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        $domain = Domain::create([
            'domain' => 'test.localhost',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', $tenantUser->name)
            ->set('email', $tenantUser->email)
            ->set('userRoles', 'Admin')
            ->set('assignedSubdomain', $tenant->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $tenantUser->id,
        ]);
    }

    public function test_unassign_domain_removes_tenant_user_relationship(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        $domain = Domain::create([
            'domain' => 'test.localhost',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        $tenant->users()->attach($tenantUser->id);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->call('unassignDomain', $tenant->id, $tenantUser->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $tenantUser->id,
        ]);
    }

    public function test_unassign_domain_updates_not_assigned_subdomains_list(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        $domain = Domain::create([
            'domain' => 'test.localhost',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        $tenant->users()->attach($tenantUser->id);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser])
            ->call('unassignDomain', $tenant->id, $tenantUser->id);

        // After unassigning, the domain should be in the notAssignedSubdomains list
        $this->assertArrayHasKey($tenant->id, $component->instance()->notAssignedSubdomains);
    }

    public function test_get_not_assigned_subdomains_excludes_already_assigned_tenants(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        // Create assigned tenant
        $assignedTenant = Tenant::create();
        $assignedTenant->name = 'Assigned Company';
        $assignedTenant->save();

        Domain::create([
            'domain' => 'assigned.localhost',
            'tenant_id' => $assignedTenant->id,
            'name' => 'Assigned Company',
            'subdomain' => 'assigned',
            'system_id' => 1,
        ]);

        $assignedTenant->users()->attach($tenantUser->id);

        // Create unassigned tenant
        $unassignedTenant = Tenant::create();
        $unassignedTenant->name = 'Unassigned Company';
        $unassignedTenant->save();

        Domain::create([
            'domain' => 'unassigned.localhost',
            'tenant_id' => $unassignedTenant->id,
            'name' => 'Unassigned Company',
            'subdomain' => 'unassigned',
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser]);

        $subdomains = $component->instance()->notAssignedSubdomains;
        $this->assertArrayNotHasKey($assignedTenant->id, $subdomains);
        $this->assertArrayHasKey($unassignedTenant->id, $subdomains);
    }

    public function test_get_not_assigned_subdomains_filters_by_system_id(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        // Create tenant for same system
        $tenant1 = Tenant::create();
        $tenant1->name = 'Same System Company';
        $tenant1->save();

        Domain::create([
            'domain' => 'same.localhost',
            'tenant_id' => $tenant1->id,
            'name' => 'Same System Company',
            'subdomain' => 'same',
            'system_id' => 1,
        ]);

        // Create tenant for different system
        $tenant2 = Tenant::create();
        $tenant2->name = 'Other System Company';
        $tenant2->save();

        Domain::create([
            'domain' => 'other.localhost',
            'tenant_id' => $tenant2->id,
            'name' => 'Other System Company',
            'subdomain' => 'other',
            'system_id' => 2,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Edit::class, ['user' => $tenantUser]);

        $subdomains = $component->instance()->notAssignedSubdomains;
        $this->assertArrayHasKey($tenant1->id, $subdomains);
        $this->assertArrayNotHasKey($tenant2->id, $subdomains);
    }

    public function test_save_validates_lowercase_email(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'email' => 'old@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Test User')
            ->set('email', 'UPPERCASE@EXAMPLE.COM')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_save_validates_max_name_length(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', str_repeat('a', 256)) // 256 characters (exceeds max of 255)
            ->set('email', 'test@example.com')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_save_validates_max_email_length(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', 'Test User')
            ->set('email', str_repeat('a', 250) . '@example.com') // Exceeds 255
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_unauthenticated_users_cannot_access_edit(): void
    {
        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $response = $this->get(route('users.edit', $tenantUser));

        $response->assertRedirect(route('login'));
    }

    public function test_render_returns_correct_view(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->assertViewIs('livewire.users.edit');
    }

    public function test_save_with_multiple_subdomains_assigned(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        // Create first tenant
        $tenant1 = Tenant::create();
        $tenant1->name = 'Company One';
        $tenant1->save();

        Domain::create([
            'domain' => 'one.localhost',
            'tenant_id' => $tenant1->id,
            'name' => 'Company One',
            'subdomain' => 'one',
            'system_id' => 1,
        ]);

        $tenant1->users()->attach($tenantUser->id);

        // Create second tenant to assign
        $tenant2 = Tenant::create();
        $tenant2->name = 'Company Two';
        $tenant2->save();

        Domain::create([
            'domain' => 'two.localhost',
            'tenant_id' => $tenant2->id,
            'name' => 'Company Two',
            'subdomain' => 'two',
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Edit::class, ['user' => $tenantUser])
            ->set('name', $tenantUser->name)
            ->set('email', $tenantUser->email)
            ->set('userRoles', 'Admin')
            ->set('assignedSubdomain', $tenant2->id)
            ->call('save');

        // Both tenants should be assigned
        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant1->id,
            'user_id' => $tenantUser->id,
        ]);

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant2->id,
            'user_id' => $tenantUser->id,
        ]);
    }
}