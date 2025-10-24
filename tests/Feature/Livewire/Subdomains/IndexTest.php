<?php

namespace Tests\Feature\Livewire\Subdomains;

use App\Livewire\Subdomains\Index;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_index_displays_subdomains_for_current_system(): void
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
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('Test Company');
    }

    public function test_index_does_not_display_subdomains_from_other_systems(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenant = Tenant::create();
        $tenant->name = 'Other System Company';
        $tenant->save();

        Domain::create([
            'domain' => 'other.'.config('tenancy.central_domains')[0],
            'tenant_id' => $tenant->id,
            'name' => 'Other System Company',
            'subdomain' => 'other',
            'system_id' => 2, // Different system
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertDontSee('Other System Company');
    }

    public function test_index_displays_user_count_for_each_subdomain(): void
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
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        // Attach multiple users to the tenant
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tenant->users()->attach([$user1->id, $user2->id]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('Test Company');
    }

    public function test_delete_removes_subdomain_and_tenant(): void
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
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->call('delete', $domain->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }

    public function test_delete_detaches_all_users_from_tenant(): void
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
            'tenant_id' => $tenant->id,
            'name' => 'Test Company',
            'subdomain' => 'test',
            'system_id' => 1,
        ]);

        // Attach users to the tenant
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tenant->users()->attach([$user1->id, $user2->id]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->call('delete', $domain->id);

        $this->assertDatabaseMissing('tenant_user', [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_pagination_works_correctly(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        // Create 15 domains to test pagination (default is 10 per page)
        for ($i = 1; $i <= 15; $i++) {
            $tenant = Tenant::create();
            $tenant->name = "Company $i";
            $tenant->save();
            Domain::create([
                'domain' => "test{$i}.".config('tenancy.central_domains')[0],
                'tenant_id' => $tenant->id,
                'name' => "Company $i",
                'subdomain' => "test{$i}",
                'system_id' => 1,
            ]);
        }

        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->assertStatus(200);

        // Should see 10 items on first page
        $component->assertSee('Company 1');
        $component->assertSee('Company 10');
    }

    public function test_subdomains_are_ordered_by_created_at_desc(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $tenant1 = Tenant::create();
        $tenant1->name = 'First Company';
        $tenant1->save();
        $domain1 = Domain::create([
            'domain' => 'first.'.config('tenancy.central_domains')[0],
            'tenant_id' => $tenant1->id,
            'name' => 'First Company',
            'subdomain' => 'first',
            'system_id' => 1,
            'created_at' => now()->subDays(2),
        ]);

        $tenant2 = Tenant::create();
        $tenant2->name = 'Second Company';
        $tenant2->save();
        $domain2 = Domain::create([
            'domain' => 'second.'.config('tenancy.central_domains')[0],
            'tenant_id' => $tenant2->id,
            'name' => 'Second Company',
            'subdomain' => 'second',
            'system_id' => 1,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSeeInOrder(['Second Company', 'First Company']);
    }

    public function test_delete_requires_valid_domain_id(): void
    {
        $user = User::factory()->create([
            'is_system' => true,
            'system_id' => 1,
        ]);

        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', 99999);
    }

    public function test_unauthenticated_users_cannot_access_index(): void
    {
        $response = $this->get(route('subdomains.index'));

        $response->assertRedirect(route('login'));
    }
}
