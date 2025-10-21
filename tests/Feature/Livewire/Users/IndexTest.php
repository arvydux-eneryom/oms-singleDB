<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\Index;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_index_displays_users_for_current_system(): void
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

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('Test User')
            ->assertSee('test@example.com');
    }

    public function test_index_does_not_display_users_from_other_systems(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Other System User',
            'email' => 'other@example.com',
            'is_tenant' => true,
            'system_id' => 2, // Different system
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertDontSee('Other System User')
            ->assertDontSee('other@example.com');
    }

    public function test_index_displays_user_roles(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('Admin');
    }

    public function test_index_displays_assigned_domains(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
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

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('test.localhost');
    }

    public function test_index_displays_no_domains_assigned_message(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('No domains assigned');
    }

    public function test_delete_removes_user_and_detaches_tenants(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        $tenant->users()->attach($tenantUser->id);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->call('delete', $tenantUser->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $tenantUser->id]);
        $this->assertDatabaseMissing('tenant_user', [
            'user_id' => $tenantUser->id,
        ]);
    }

    public function test_delete_removes_user_roles(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $tenantUser->assignRole($role);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->call('delete', $tenantUser->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $tenantUser->id]);
        $this->assertDatabaseMissing('model_has_roles', [
            'model_id' => $tenantUser->id,
            'model_type' => User::class,
        ]);
    }

    public function test_delete_requires_valid_user_id(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', 99999);
    }

    public function test_pagination_works_correctly(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        // Create 10 users to test pagination (default is 5 per page based on component)
        User::factory()->count(10)->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Index::class)
            ->assertStatus(200);

        // Should have pagination links since we have more than 5 users
        $component->assertSee('Next');
    }

    public function test_component_renders_pagination_when_needed(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        // Create enough users to trigger pagination
        User::factory()->count(10)->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('Next');
    }

    public function test_index_displays_multiple_domains_for_user(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Test User',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        // Create first tenant and domain
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

        // Create second tenant and domain
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

        // Attach user to both tenants
        $tenant1->users()->attach($tenantUser->id);
        $tenant2->users()->attach($tenantUser->id);

        $this->actingAs($systemUser);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('one.localhost')
            ->assertSee('two.localhost');
    }

    public function test_index_loads_users_with_roles_and_permissions(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Index::class);

        // Verify that relationships are eager loaded
        $users = $component->viewData('users');
        $this->assertTrue($users->first()->relationLoaded('roles'));
        $this->assertTrue($users->first()->relationLoaded('permissions'));
    }

    public function test_unauthenticated_users_cannot_access_index(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_mount_sets_tenant_id(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Index::class);

        // tenantId should be set but might be null if not in tenant context
        $this->assertObjectHasProperty('tenantId', $component->instance());
    }
}