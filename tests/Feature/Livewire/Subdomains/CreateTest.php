<?php

namespace Tests\Feature\Livewire\Subdomains;

use App\Livewire\Subdomains\Create;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

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

    public function test_can_create_subdomain_with_valid_data(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
            'is_tenant' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Acme Corporation')
            ->set('subdomain', 'acme123')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('success', 'Subdomain successfully created.')
            ->assertRedirect(route('subdomains.index'));

        $tenant = Tenant::latest()->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('Acme Corporation', $tenant->companyName);

        $this->assertDatabaseHas('domains', [
            'name' => 'Acme Corporation',
            'subdomain' => 'acme123',
            'domain' => 'acme123.' . config('tenancy.central_domains')[0],
            'system_id' => 1,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_creating_subdomain_attaches_user_to_tenant(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
            'is_tenant' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test123')
            ->call('save');

        $tenant = Tenant::latest()->first();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_creating_subdomain_marks_user_as_tenant(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
            'is_tenant' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test123')
            ->call('save');

        $user->refresh();

        $this->assertTrue((bool)$user->is_tenant);
    }

    public function test_company_name_is_required(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', '')
            ->set('subdomain', 'test123')
            ->call('save')
            ->assertHasErrors(['companyName' => 'required']);
    }

    public function test_subdomain_is_required(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', '')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'required']);
    }

    public function test_company_name_cannot_exceed_255_characters(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', str_repeat('a', 256))
            ->set('subdomain', 'test123')
            ->call('save')
            ->assertHasErrors(['companyName' => 'max']);
    }

    public function test_subdomain_must_be_alphanumeric(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test-123')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'regex']);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test_123')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'regex']);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test 123')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'regex']);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test@123')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'regex']);
    }

    public function test_subdomain_cannot_exceed_8_characters(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test12345')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'max']);
    }

    public function test_subdomain_must_be_unique(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->companyName = 'Existing Company';
        $tenant->save();
        Domain::create([
            'domain' => 'existing.' . config('tenancy.central_domains')[0],
            'tenant_id' => $tenant->id,
            'name' => 'Existing Company',
            'subdomain' => 'existing',
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'New Company')
            ->set('subdomain', 'existing')
            ->call('save')
            ->assertHasErrors(['subdomain' => 'unique']);
    }

    public function test_subdomain_accepts_valid_alphanumeric_values(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test123')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company 2')
            ->set('subdomain', 'TEST456')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company 3')
            ->set('subdomain', 'abc')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company 4')
            ->set('subdomain', '12345678')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_domain_is_created_with_correct_format(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'mytest')
            ->call('save');

        $domain = Domain::where('subdomain', 'mytest')->first();
        $expectedDomain = 'mytest.' . config('tenancy.central_domains')[0];

        $this->assertEquals($expectedDomain, $domain->domain);
    }

    public function test_domain_is_associated_with_user_system_id(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 42,
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('companyName', 'Test Company')
            ->set('subdomain', 'test123')
            ->call('save');

        $this->assertDatabaseHas('domains', [
            'subdomain' => 'test123',
            'system_id' => 42,
        ]);
    }

    public function test_unauthenticated_users_cannot_access_create(): void
    {
        $response = $this->get(route('subdomains.create'));

        $response->assertRedirect(route('login'));
    }
}