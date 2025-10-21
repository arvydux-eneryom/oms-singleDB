<?php

namespace Tests\Feature\Auth;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AutomaticSubdomainRedirectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    // ========================================
    // LOGIN FLOW TESTS
    // ========================================

    public function test_login_with_exactly_one_domain_redirects_to_subdomain(): void
    {
        // Create user with system_id
        $systemId = 123;
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'is_system' => true,
            'system_id' => $systemId,
        ]);

        // Create exactly one domain for this user
        $tenant = Tenant::create(['name' => 'Test Company']);
        Domain::create([
            'domain' => 'test.localhost',
            'subdomain' => 'test',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'system_id' => $systemId,
        ]);

        // Attempt login
        $response = Volt::test('auth.login')
            ->set('email', 'user@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should redirect to subdomains.redirect route (check path only, not domain)
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->effects['redirect'], '/subdomains/redirect'));
    }

    public function test_login_with_multiple_domains_redirects_to_dashboard(): void
    {
        // Create user with system_id
        $systemId = 456;
        $user = User::factory()->create([
            'email' => 'multiuser@example.com',
            'password' => bcrypt('password'),
            'is_system' => true,
            'system_id' => $systemId,
        ]);

        // Create multiple domains for this user
        $tenant1 = Tenant::create(['name' => 'Company 1']);
        Domain::create([
            'domain' => 'company1.localhost',
            'subdomain' => 'company1',
            'tenant_id' => $tenant1->id,
            'name' => 'Company 1',
            'system_id' => $systemId,
        ]);

        $tenant2 = Tenant::create(['name' => 'Company 2']);
        Domain::create([
            'domain' => 'company2.localhost',
            'subdomain' => 'company2',
            'tenant_id' => $tenant2->id,
            'name' => 'Company 2',
            'system_id' => $systemId,
        ]);

        // Attempt login
        $response = Volt::test('auth.login')
            ->set('email', 'multiuser@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should redirect to dashboard
        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_with_zero_domains_redirects_to_dashboard(): void
    {
        // Create user with no domains
        $user = User::factory()->create([
            'email' => 'nodomains@example.com',
            'password' => bcrypt('password'),
            'is_system' => true,
            'system_id' => 789,
        ]);

        // Attempt login (no domains created)
        $response = Volt::test('auth.login')
            ->set('email', 'nodomains@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should redirect to dashboard
        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_with_three_domains_redirects_to_dashboard(): void
    {
        // Create user with system_id
        $systemId = 999;
        $user = User::factory()->create([
            'email' => 'threedomains@example.com',
            'password' => bcrypt('password'),
            'is_system' => true,
            'system_id' => $systemId,
        ]);

        // Create three domains for this user
        for ($i = 1; $i <= 3; $i++) {
            $tenant = Tenant::create(['name' => "Company $i"]);
            Domain::create([
                'domain' => "company$i.localhost",
                'subdomain' => "company$i",
                'tenant_id' => $tenant->id,
                'name' => "Company $i",
                'system_id' => $systemId,
            ]);
        }

        // Attempt login
        $response = Volt::test('auth.login')
            ->set('email', 'threedomains@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should redirect to dashboard
        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_only_considers_domains_matching_user_system_id(): void
    {
        // Create user with system_id = 100
        $systemId = 100;
        $user = User::factory()->create([
            'email' => 'isolated@example.com',
            'password' => bcrypt('password'),
            'is_system' => true,
            'system_id' => $systemId,
        ]);

        // Create one domain for this user (system_id = 100)
        $tenant1 = Tenant::create(['name' => 'User Company']);
        Domain::create([
            'domain' => 'usercompany.localhost',
            'subdomain' => 'usercompany',
            'tenant_id' => $tenant1->id,
            'name' => 'User Company',
            'system_id' => $systemId,
        ]);

        // Create domains for another user (system_id = 200) - should be ignored
        $tenant2 = Tenant::create(['name' => 'Other Company']);
        Domain::create([
            'domain' => 'othercompany.localhost',
            'subdomain' => 'othercompany',
            'tenant_id' => $tenant2->id,
            'name' => 'Other Company',
            'system_id' => 200,
        ]);

        // Attempt login
        $response = Volt::test('auth.login')
            ->set('email', 'isolated@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should redirect to subdomains.redirect (only 1 domain for system_id 100)
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->effects['redirect'], '/subdomains/redirect'));
    }

    // ========================================
    // REGISTRATION FLOW TESTS
    // ========================================

    public function test_registration_creates_exactly_one_subdomain(): void
    {
        // Register new user
        Volt::test('auth.register')
            ->set('name', 'New Company')
            ->set('email', 'newuser@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'newuser@example.com')->first();

        // Check that exactly one domain was created for this user's system_id
        $domains = Domain::where('system_id', $user->system_id)->get();
        $this->assertCount(1, $domains);
    }

    public function test_registration_redirects_to_created_subdomain(): void
    {
        // Register new user
        $response = Volt::test('auth.register')
            ->set('name', 'Auto Redirect Company')
            ->set('email', 'autoredirect@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Should redirect to subdomains.redirect (since exactly 1 domain was created)
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->effects['redirect'], '/subdomains/redirect'));
    }

    public function test_registration_created_subdomain_has_random_name(): void
    {
        // Register new user
        Volt::test('auth.register')
            ->set('name', 'Random Subdomain Co')
            ->set('email', 'random@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'random@example.com')->first();
        $domain = Domain::where('system_id', $user->system_id)->first();

        // Subdomain should exist and be random (between 2-8 characters)
        $this->assertNotNull($domain->subdomain);
        $this->assertGreaterThanOrEqual(2, strlen($domain->subdomain));
        $this->assertLessThanOrEqual(8, strlen($domain->subdomain));
    }

    public function test_registration_created_domain_belongs_to_user_system(): void
    {
        // Register new user
        Volt::test('auth.register')
            ->set('name', 'System Check Company')
            ->set('email', 'systemcheck@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'systemcheck@example.com')->first();
        $domain = Domain::where('system_id', $user->system_id)->first();

        // Domain should have the same system_id as the user
        $this->assertEquals($user->system_id, $domain->system_id);
    }

    public function test_multiple_registrations_create_isolated_subdomains(): void
    {
        // Register first user
        Volt::test('auth.register')
            ->set('name', 'Company A')
            ->set('email', 'usera@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Register second user
        Volt::test('auth.register')
            ->set('name', 'Company B')
            ->set('email', 'userb@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $userA = User::where('email', 'usera@example.com')->first();
        $userB = User::where('email', 'userb@example.com')->first();

        // Each user should have different system_ids
        $this->assertNotEquals($userA->system_id, $userB->system_id);

        // Each user should have exactly 1 domain
        $this->assertCount(1, Domain::where('system_id', $userA->system_id)->get());
        $this->assertCount(1, Domain::where('system_id', $userB->system_id)->get());

        // Domains should be different
        $domainA = Domain::where('system_id', $userA->system_id)->first();
        $domainB = Domain::where('system_id', $userB->system_id)->first();
        $this->assertNotEquals($domainA->subdomain, $domainB->subdomain);
    }

    // ========================================
    // INTEGRATION TESTS
    // ========================================

    public function test_user_can_login_immediately_after_registration(): void
    {
        // Register new user
        Volt::test('auth.register')
            ->set('name', 'Immediate Login Co')
            ->set('email', 'immediate@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // User should be authenticated
        $this->assertAuthenticated();

        // Logout
        auth()->logout();

        // Try to login
        $response = Volt::test('auth.login')
            ->set('email', 'immediate@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should be authenticated and redirect to subdomain
        $this->assertAuthenticated();
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->effects['redirect'], '/subdomains/redirect'));
    }

    public function test_redirect_uses_most_recent_domain_when_only_one_exists(): void
    {
        $systemId = 555;
        $user = User::factory()->create([
            'email' => 'recent@example.com',
            'password' => bcrypt('password'),
            'is_system' => true,
            'system_id' => $systemId,
        ]);

        // Create one domain (most recent)
        $tenant = Tenant::create(['name' => 'Recent Company']);
        $domain = Domain::create([
            'domain' => 'recent.localhost',
            'subdomain' => 'recent',
            'tenant_id' => $tenant->id,
            'name' => 'Recent Company',
            'system_id' => $systemId,
            'created_at' => now(),
        ]);

        // Login
        $response = Volt::test('auth.login')
            ->set('email', 'recent@example.com')
            ->set('password', 'password')
            ->call('login');

        // Should redirect to subdomains.redirect
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->effects['redirect'], '/subdomains/redirect'));

        // Verify redirect page loads and contains the domain
        $this->actingAs($user);
        $redirectResponse = $this->get(route('subdomains.redirect'));
        $redirectResponse->assertStatus(200);
        $redirectResponse->assertSee('recent.localhost');
    }
}
