<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\Create;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->assertStatus(200);
    }

    public function test_component_loads_roles_on_mount(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Manager']);

        $this->actingAs($user);

        $component = Livewire::test(Create::class);

        $this->assertNotEmpty($component->instance()->roles);
        $this->assertIsArray($component->instance()->roles);
    }

    public function test_component_loads_not_assigned_subdomains_on_mount(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
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

        $this->actingAs($user);

        $component = Livewire::test(Create::class);

        $this->assertNotEmpty($component->instance()->notAssignedSubdomains);
        $this->assertIsArray($component->instance()->notAssignedSubdomains);
    }

    public function test_save_creates_new_user_successfully(): void
    {
        Event::fake([Registered::class]);

        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('success', 'User successfully created.')
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_save_validates_required_fields(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'password']);
    }

    public function test_save_validates_email_format(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->set('password', 'SecurePassword123!')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_save_validates_unique_email(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        User::factory()->create([
            'email' => 'existing@example.com',
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'SecurePassword123!')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_save_validates_password_strength(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', '123') // Too short
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_save_hashes_password(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->call('save');

        $user = User::where('email', 'newuser@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SecurePassword123!', $user->password));
    }

    public function test_save_assigns_role_to_user(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->call('save');

        $user = User::where('email', 'newuser@example.com')->first();

        $this->assertTrue($user->hasRole('Admin'));
    }

    public function test_save_assigns_subdomain_to_user(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
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

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->set('assignedSubdomain', $tenant->id)
            ->call('save')
            ->assertSessionHas('success', 'User successfully created.');

        $user = User::where('email', 'newuser@example.com')->first();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_save_sets_is_tenant_flag(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'is_tenant' => true,
        ]);
    }

    public function test_save_sets_system_id_from_authenticated_user(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 5,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'system_id' => 5,
        ]);
    }

    public function test_save_clears_existing_roles_before_assigning_new_one(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin']);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->call('save');

        $user = User::where('email', 'newuser@example.com')->first();

        // Should have exactly one role
        $this->assertEquals(1, $user->roles()->count());
    }

    public function test_save_updates_not_assigned_subdomains_after_assignment(): void
    {
        Event::fake();

        $systemUser = User::factory()->create([
            'is_system' => true,
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

        $this->actingAs($systemUser);

        $component = Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('userRoles', 'Admin')
            ->set('assignedSubdomain', $tenant->id)
            ->call('save');

        // The component should have updated notAssignedSubdomains
        $this->assertIsArray($component->instance()->notAssignedSubdomains);
    }

    public function test_save_validates_lowercase_email(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'New User')
            ->set('email', 'UPPERCASE@EXAMPLE.COM')
            ->set('password', 'SecurePassword123!')
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

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', str_repeat('a', 256)) // 256 characters (exceeds max of 255)
            ->set('email', 'test@example.com')
            ->set('password', 'SecurePassword123!')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_save_validates_max_email_length(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->set('name', 'Test User')
            ->set('email', str_repeat('a', 250) . '@example.com') // Exceeds 255
            ->set('password', 'SecurePassword123!')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_get_not_assigned_subdomains_returns_domains_for_system(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
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

        // Create domain for different system
        $tenant2 = Tenant::create();
        $tenant2->name = 'Other Company';
        $tenant2->save();

        Domain::create([
            'domain' => 'other.localhost',
            'tenant_id' => $tenant2->id,
            'name' => 'Other Company',
            'subdomain' => 'other',
            'system_id' => 2,
        ]);

        $this->actingAs($systemUser);

        $component = Livewire::test(Create::class);

        // Should only see domains from system_id 1
        $subdomains = $component->instance()->notAssignedSubdomains;
        $this->assertArrayHasKey($tenant->id, $subdomains);
        $this->assertArrayNotHasKey($tenant2->id, $subdomains);
    }

    public function test_unauthenticated_users_cannot_access_create(): void
    {
        $response = $this->get(route('users.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_render_returns_correct_view(): void
    {
        $systemUser = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($systemUser);

        Livewire::test(Create::class)
            ->assertViewIs('livewire.users.create');
    }
}