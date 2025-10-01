<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasNoErrors();

        $this->assertAuthenticated();
    }

    public function test_registration_creates_user_with_correct_attributes(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Acme Corp')
            ->set('email', 'admin@example.com')
            ->set('password', 'SecurePass123!')
            ->set('password_confirmation', 'SecurePass123!')
            ->call('register');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'is_system' => true,
            'is_tenant' => true,
        ]);

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->system_id);
        $this->assertTrue(\Hash::check('SecurePass123!', $user->password));
    }

    public function test_registration_assigns_super_admin_role(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'admin@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($user->hasRole('super-admin-for-tenant'));
    }

    public function test_registration_creates_company(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Acme Corporation')
            ->set('email', 'owner@acme.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'owner@acme.com')->first();

        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Corporation',
            'user_id' => $user->id,
        ]);
    }

    public function test_registration_creates_tenant(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Test Tenant')
            ->set('email', 'tenant@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $tenant = Tenant::first();
        $this->assertNotNull($tenant);
        $this->assertEquals('Test Tenant', $tenant->name);
    }

    public function test_registration_creates_domain_with_subdomain(): void
    {
        Volt::test('auth.register')
            ->set('name', 'My Company')
            ->set('email', 'user@company.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'user@company.com')->first();
        $tenant = Tenant::first();

        $this->assertNotNull($tenant);
        $this->assertEquals('My Company', $tenant->name);

        $domain = $tenant->domains()->first();
        $this->assertNotNull($domain);
        $this->assertEquals('My Company', $domain->name);
        $this->assertNotEmpty($domain->subdomain);
        $this->assertGreaterThanOrEqual(2, strlen($domain->subdomain));
        $this->assertLessThanOrEqual(8, strlen($domain->subdomain));
        $this->assertEquals($user->system_id, $domain->system_id);
        $this->assertStringContainsString(strtolower($domain->subdomain), strtolower($domain->domain));
    }

    public function test_registration_attaches_user_to_tenant(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'user@test.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'user@test.com')->first();
        $tenant = Tenant::first();

        $this->assertTrue($user->tenants->contains($tenant->id));
    }

    public function test_registration_marks_user_as_tenant(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Company')
            ->set('email', 'user@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'user@example.com')->first();
        $this->assertEquals(1, $user->is_tenant);
    }

    public function test_registration_requires_name(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', '')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['name']);
    }

    public function test_registration_requires_email(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', '')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'invalid-email')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_registration_requires_unique_email_for_system_users(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'is_system' => true,
            'system_id' => 1,
        ]);

        $response = Volt::test('auth.register')
            ->set('name', 'New Company')
            ->set('email', 'existing@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_registration_requires_password(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'test@example.com')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('register');

        $response->assertHasErrors(['password']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'different')
            ->call('register');

        $response->assertHasErrors(['password']);
    }

    public function test_registration_enforces_password_rules(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'test@example.com')
            ->set('password', '123')
            ->set('password_confirmation', '123')
            ->call('register');

        $response->assertHasErrors(['password']);
    }

    public function test_email_must_be_lowercase(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', 'TEST@EXAMPLE.COM')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }

    public function test_name_cannot_exceed_255_characters(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', str_repeat('a', 256))
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['name']);
    }

    public function test_email_cannot_exceed_255_characters(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test Company')
            ->set('email', str_repeat('a', 247) . '@test.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors(['email']);
    }
}
