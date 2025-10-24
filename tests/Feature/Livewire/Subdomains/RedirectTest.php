<?php

namespace Tests\Feature\Livewire\Subdomains;

use App\Livewire\Subdomains\Redirect;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_auto_redirects_to_first_subdomain(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        $domain = Domain::create([
            'domain' => 'test.'.config('tenancy.central_domains')[0],
            'subdomain' => 'test',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Redirect::class)
            ->assertStatus(200)
            ->assertSee('window.location.href')
            ->assertSee('test.'.config('tenancy.central_domains')[0]);
    }

    public function test_redirect_uses_first_subdomain_ordered_by_created_at_desc(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        // Create multiple subdomains
        $tenant1 = Tenant::create();
        $tenant1->name = 'First Company';
        $tenant1->save();
        Domain::create([
            'domain' => 'first.'.config('tenancy.central_domains')[0],
            'subdomain' => 'first',
            'tenant_id' => $tenant1->id,
            'name' => 'First Company',
            'system_id' => 1,
            'created_at' => now()->subDays(2),
        ]);

        $tenant2 = Tenant::create();
        $tenant2->name = 'Second Company';
        $tenant2->save();
        Domain::create([
            'domain' => 'second.'.config('tenancy.central_domains')[0],
            'subdomain' => 'second',
            'tenant_id' => $tenant2->id,
            'name' => 'Second Company',
            'system_id' => 1,
            'created_at' => now()->subDay(),
        ]);

        $tenant3 = Tenant::create();
        $tenant3->name = 'Third Company';
        $tenant3->save();
        Domain::create([
            'domain' => 'third.'.config('tenancy.central_domains')[0],
            'subdomain' => 'third',
            'tenant_id' => $tenant3->id,
            'name' => 'Third Company',
            'system_id' => 1,
            'created_at' => now(),
        ]);

        $this->actingAs($user);

        // Should redirect to the most recent subdomain (third)
        Livewire::test(Redirect::class)
            ->assertStatus(200)
            ->assertSee('window.location.href')
            ->assertSee('third.'.config('tenancy.central_domains')[0]);
    }

    public function test_redirect_loads_tenant_relationship(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Test Company';
        $tenant->save();

        $domain = Domain::create([
            'domain' => 'test.'.config('tenancy.central_domains')[0],
            'subdomain' => 'test',
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Redirect::class);

        // The component should load tenants with user counts
        $this->assertTrue($component->viewData('subdomains')->isNotEmpty());
        $this->assertNotNull($component->viewData('subdomains')->first()->tenant);
    }

    public function test_redirect_respects_user_system_id(): void
    {
        $user1 = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $user2 = User::factory()->create([
            'is_system' => true,
            'system_id' => 2,
        ]);

        $tenant1 = Tenant::create();
        $tenant1->name = 'Company System 1';
        $tenant1->save();

        $tenant2 = Tenant::create();
        $tenant2->name = 'Company System 2';
        $tenant2->save();

        Domain::create([
            'domain' => 'company1.'.config('tenancy.central_domains')[0],
            'subdomain' => 'company1',
            'tenant_id' => $tenant1->id,
            'name' => 'Company System 1',
            'system_id' => 1,
        ]);

        Domain::create([
            'domain' => 'company2.'.config('tenancy.central_domains')[0],
            'subdomain' => 'company2',
            'tenant_id' => $tenant2->id,
            'name' => 'Company System 2',
            'system_id' => 2,
        ]);

        // User 1 should redirect to their system's subdomain
        $this->actingAs($user1);

        Livewire::test(Redirect::class)
            ->assertSee('company1.'.config('tenancy.central_domains')[0])
            ->assertDontSee('company2.'.config('tenancy.central_domains')[0]);

        // User 2 should redirect to their system's subdomain
        $this->actingAs($user2);

        Livewire::test(Redirect::class)
            ->assertSee('company2.'.config('tenancy.central_domains')[0])
            ->assertDontSee('company1.'.config('tenancy.central_domains')[0]);
    }

    public function test_unauthenticated_users_cannot_access_redirect(): void
    {
        $response = $this->get(route('subdomains.redirect'));

        $response->assertRedirect(route('login'));
    }
}
