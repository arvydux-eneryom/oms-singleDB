<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TenantGuestRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a system user first
        $systemUser = User::factory()->create([
            'is_system' => true,
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        // Create a tenant and initialize tenancy context
        $this->tenant = \App\Models\Tenant::create([
            'name' => 'Test Tenant',
        ]);

        // Attach the system user to the tenant
        $this->tenant->users()->attach($systemUser->id);

        // Create a domain for the tenant
        $this->tenant->domains()->create([
            'domain' => 'test.localhost',
            'name' => 'Test Domain',
            'subdomain' => 'test',
            'system_id' => $systemUser->system_id,
        ]);

        // Initialize tenancy
        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        // End tenancy after each test
        tenancy()->end();
        parent::tearDown();
    }

    public function test_tenant_registration_route_exists(): void
    {
        $this->assertTrue(\Route::has('register'));
    }

    public function test_guest_can_access_registration_page(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('register', false);
    }

    public function test_tenant_register_volt_component_exists(): void
    {
        $viewPath = resource_path('views/livewire/auth/tenant/register.blade.php');
        $this->assertFileExists($viewPath);
    }

    public function test_guest_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $response = Volt::test('auth.tenant.register')
            ->set('name', $userData['name'])
            ->set('email', $userData['email'])
            ->set('password', $userData['password'])
            ->set('password_confirmation', $userData['password_confirmation'])
            ->call('register');

        $response->assertHasNoErrors();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'is_tenant' => true,
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
        $this->assertTrue((bool) $user->is_tenant);
        $this->assertAuthenticated();
    }

    public function test_guest_registration_requires_name(): void
    {
        $response = Volt::test('auth.tenant.register')
            ->set('name', '')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['name']);
    }

    public function test_guest_registration_requires_valid_email(): void
    {
        $response = Volt::test('auth.tenant.register')
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_guest_registration_requires_email(): void
    {
        $response = Volt::test('auth.tenant.register')
            ->set('name', 'Test User')
            ->set('email', '')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_guest_registration_requires_password(): void
    {
        $response = Volt::test('auth.tenant.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('register');

        $response->assertHasErrors(['password']);
    }

    public function test_guest_registration_requires_password_confirmation(): void
    {
        $response = Volt::test('auth.tenant.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'different-password')
            ->call('register');

        $response->assertHasErrors(['password']);
    }

    public function test_guest_registration_prevents_duplicate_email_in_tenant(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'is_tenant' => true,
            'system_id' => 1,
        ]);

        // Attach the existing user to the tenant
        $this->tenant->users()->attach($existingUser->id);

        $response = Volt::test('auth.tenant.register')
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_guest_registration_enforces_password_rules(): void
    {
        $response = Volt::test('auth.tenant.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', '123')
            ->set('password_confirmation', '123')
            ->call('register');

        $response->assertHasErrors(['password']);
    }

    public function test_registered_user_is_marked_as_tenant_user(): void
    {
        $userData = [
            'name' => 'Tenant User',
            'email' => 'tenant@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        Volt::test('auth.tenant.register')
            ->set('name', $userData['name'])
            ->set('email', $userData['email'])
            ->set('password', $userData['password'])
            ->set('password_confirmation', $userData['password_confirmation'])
            ->call('register');

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->is_tenant);
    }

    public function test_registered_user_has_correct_attributes(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        Volt::test('auth.tenant.register')
            ->set('name', $userData['name'])
            ->set('email', $userData['email'])
            ->set('password', $userData['password'])
            ->set('password_confirmation', $userData['password_confirmation'])
            ->call('register');

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertTrue((bool) $user->is_tenant);
        $this->assertNotNull($user->system_id);
        $this->assertFalse((bool) ($user->is_system ?? false));
    }

    public function test_registration_page_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->create(['is_tenant' => true, 'system_id' => 1]);
        $this->actingAs($user);

        $response = $this->get('/register');

        $response->assertStatus(200);
    }
}
