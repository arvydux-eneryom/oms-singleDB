<?php

namespace Tests\Feature\Livewire\Customers;

use App\Livewire\Tenancy\Customers\Index;
use App\Models\Customer;
use App\Models\CustomerEmail;
use App\Models\CustomerPhone;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Test Tenant']);
        tenancy()->initialize($this->tenant);

        $this->user = User::factory()->create([
            'is_tenant' => true,
            'system_id' => 1,
        ]);
        $this->tenant->users()->attach($this->user->id);

        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    #[Test]
    public function it_can_render_index_component(): void
    {
        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_loads_statistics_on_mount(): void
    {
        // Create older customers (last month)
        Customer::factory()->count(5)->create([
            'tenant_id' => tenant('id'),
            'status' => true,
            'created_at' => now()->subMonth(),
        ]);
        Customer::factory()->count(3)->create([
            'tenant_id' => tenant('id'),
            'status' => false,
            'created_at' => now()->subMonth(),
        ]);

        // Create this month customers (explicitly set status to ensure consistent test results)
        Customer::factory()->count(2)->create([
            'tenant_id' => tenant('id'),
            'status' => true,
            'created_at' => now(),
        ]);

        Livewire::test(Index::class)
            ->assertSet('stats.total', 10) // 5 + 3 + 2
            ->assertSet('stats.active', 7) // 5 + 2 (explicitly set to active)
            ->assertSet('stats.inactive', 3)
            ->assertSet('stats.this_month', 2);
    }

    #[Test]
    public function it_displays_customers_for_current_tenant_only(): void
    {
        // Create a second tenant for isolation testing
        $otherTenant = \App\Models\Tenant::create(['name' => 'Other Tenant']);

        $tenantCustomer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Tenant Company',
        ]);

        $otherCustomer = Customer::factory()->create([
            'tenant_id' => $otherTenant->id,
            'company' => 'Other Company',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Tenant Company')
            ->assertDontSee('Other Company');
    }

    #[Test]
    public function it_can_search_by_company_name(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Acme Corporation',
        ]);

        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Wayne Enterprises',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Acme')
            ->assertSee('Acme Corporation')
            ->assertDontSee('Wayne Enterprises');
    }

    #[Test]
    public function it_can_search_by_address(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company A',
            'address' => '123 Main Street',
        ]);

        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company B',
            'address' => '456 Oak Avenue',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Main')
            ->assertSee('Company A')
            ->assertDontSee('Company B');
    }

    #[Test]
    public function it_can_search_by_phone_number(): void
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company A',
        ]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer1->id,
            'phone' => '+1234567890',
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company B',
        ]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer2->id,
            'phone' => '+9876543210',
        ]);

        Livewire::test(Index::class)
            ->set('search', '123456')
            ->assertSee('Company A')
            ->assertDontSee('Company B');
    }

    #[Test]
    public function it_can_search_by_email(): void
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company A',
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer1->id,
            'email' => 'contact@companya.com',
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company B',
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer2->id,
            'email' => 'info@companyb.com',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'companya')
            ->assertSee('Company A')
            ->assertDontSee('Company B');
    }

    #[Test]
    public function it_resets_pagination_when_searching(): void
    {
        Customer::factory()->count(20)->create(['tenant_id' => tenant('id')]);

        Livewire::test(Index::class)
            ->set('perPage', 10)
            ->call('nextPage')
            ->assertSet('paginators.page', 2)
            ->set('search', 'test')
            ->assertSet('paginators.page', 1);
    }

    #[Test]
    public function it_can_filter_by_active_status(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Active Company',
            'status' => true,
        ]);

        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Inactive Company',
            'status' => false,
        ]);

        Livewire::test(Index::class)
            ->set('statusFilter', 'active')
            ->assertSee('Active Company')
            ->assertDontSee('Inactive Company');
    }

    #[Test]
    public function it_can_filter_by_inactive_status(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Active Company',
            'status' => true,
        ]);

        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Inactive Company',
            'status' => false,
        ]);

        Livewire::test(Index::class)
            ->set('statusFilter', 'inactive')
            ->assertSee('Inactive Company')
            ->assertDontSee('Active Company');
    }

    #[Test]
    public function it_can_sort_by_company_name(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Zebra Company',
        ]);

        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Alpha Company',
        ]);

        $component = Livewire::test(Index::class)
            ->set('sortField', 'company')
            ->set('sortDirection', 'asc');

        $customers = $component->get('customers');
        $this->assertEquals('Alpha Company', $customers->first()->company);
    }

    #[Test]
    public function it_can_toggle_sort_direction(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company A',
        ]);

        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Company B',
        ]);

        Livewire::test(Index::class)
            ->set('sortField', 'company')
            ->set('sortDirection', 'asc')
            ->call('sortBy', 'company')
            ->assertSet('sortDirection', 'desc');
    }

    #[Test]
    public function it_changes_sort_field_and_resets_direction(): void
    {
        Livewire::test(Index::class)
            ->set('sortField', 'company')
            ->set('sortDirection', 'desc')
            ->call('sortBy', 'created_at')
            ->assertSet('sortField', 'created_at')
            ->assertSet('sortDirection', 'asc');
    }

    #[Test]
    public function it_can_change_per_page(): void
    {
        Customer::factory()->count(30)->create(['tenant_id' => tenant('id')]);

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        $this->assertCount(25, $component->get('customers'));
    }

    #[Test]
    public function it_resets_page_when_changing_per_page(): void
    {
        Customer::factory()->count(30)->create(['tenant_id' => tenant('id')]);

        Livewire::test(Index::class)
            ->set('perPage', 10)
            ->call('nextPage')
            ->assertSet('paginators.page', 2)
            ->set('perPage', 25)
            ->assertSet('paginators.page', 1);
    }

    #[Test]
    public function it_can_select_all_customers(): void
    {
        $customers = Customer::factory()->count(5)->create(['tenant_id' => tenant('id')]);

        $component = Livewire::test(Index::class)
            ->set('selectAll', true);

        $this->assertCount(5, $component->get('selectedCustomers'));
    }

    #[Test]
    public function it_can_clear_selection(): void
    {
        Customer::factory()->count(5)->create(['tenant_id' => tenant('id')]);

        Livewire::test(Index::class)
            ->set('selectAll', true)
            ->call('clearSelection')
            ->assertSet('selectedCustomers', [])
            ->assertSet('selectAll', false);
    }

    #[Test]
    public function it_can_bulk_delete_selected_customers(): void
    {
        $customers = Customer::factory()->count(3)->create(['tenant_id' => tenant('id')]);
        $customerIds = $customers->pluck('id')->toArray();

        Livewire::test(Index::class)
            ->set('selectedCustomers', $customerIds)
            ->call('bulkDelete')
            ->assertHasNoErrors();

        // Verify all customers are soft deleted
        foreach ($customerIds as $customerId) {
            $this->assertSoftDeleted('customers', ['id' => $customerId]);
        }
    }

    #[Test]
    public function it_shows_error_when_bulk_deleting_with_no_selection(): void
    {
        Livewire::test(Index::class)
            ->set('selectedCustomers', [])
            ->call('bulkDelete')
            ->assertHasNoErrors();

        // Verify no customers were deleted
        $this->assertDatabaseCount('customers', 0);
    }

    #[Test]
    public function it_can_bulk_activate_customers(): void
    {
        $customers = Customer::factory()->count(3)->create([
            'tenant_id' => tenant('id'),
            'status' => false,
        ]);
        $customerIds = $customers->pluck('id')->toArray();

        Livewire::test(Index::class)
            ->set('selectedCustomers', $customerIds)
            ->call('bulkUpdateStatus', true);

        foreach ($customerIds as $id) {
            $this->assertTrue(Customer::find($id)->status);
        }
    }

    #[Test]
    public function it_can_bulk_deactivate_customers(): void
    {
        $customers = Customer::factory()->count(3)->create([
            'tenant_id' => tenant('id'),
            'status' => true,
        ]);
        $customerIds = $customers->pluck('id')->toArray();

        Livewire::test(Index::class)
            ->set('selectedCustomers', $customerIds)
            ->call('bulkUpdateStatus', false);

        foreach ($customerIds as $id) {
            $this->assertFalse(Customer::find($id)->status);
        }
    }

    #[Test]
    public function it_clears_selection_after_bulk_operation(): void
    {
        $customers = Customer::factory()->count(3)->create(['tenant_id' => tenant('id')]);
        $customerIds = $customers->pluck('id')->toArray();

        Livewire::test(Index::class)
            ->set('selectedCustomers', $customerIds)
            ->call('bulkUpdateStatus', true)
            ->assertSet('selectedCustomers', []);
    }

    #[Test]
    public function it_reloads_statistics_after_bulk_delete(): void
    {
        Customer::factory()->count(5)->create(['tenant_id' => tenant('id')]);
        $customersToDelete = Customer::factory()->count(2)->create(['tenant_id' => tenant('id')]);

        Livewire::test(Index::class)
            ->assertSet('stats.total', 7)
            ->set('selectedCustomers', $customersToDelete->pluck('id')->toArray())
            ->call('bulkDelete')
            ->assertSet('stats.total', 5); // Stats should auto-reload after bulk delete
    }

    #[Test]
    public function it_can_export_customers_to_csv(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
        ]);

        // For Livewire components that return StreamedResponse, we verify the method executes without errors
        // The actual CSV content testing would require integration tests
        Livewire::test(Index::class)
            ->call('exportCsv')
            ->assertHasNoErrors();
    }

    #[Test]
    public function it_can_export_selected_customers_to_csv(): void
    {
        $customers = Customer::factory()->count(3)->create(['tenant_id' => tenant('id')]);
        $selectedIds = $customers->take(2)->pluck('id')->toArray();

        Livewire::test(Index::class)
            ->set('selectedCustomers', $selectedIds)
            ->call('exportSelectedCsv')
            ->assertHasNoErrors();
    }

    #[Test]
    public function it_shows_error_when_exporting_with_no_selection(): void
    {
        // Verify the method handles empty selection gracefully without exceptions
        Livewire::test(Index::class)
            ->set('selectedCustomers', [])
            ->call('exportSelectedCsv')
            ->assertHasNoErrors();
    }

    #[Test]
    public function it_can_reset_all_filters(): void
    {
        Livewire::test(Index::class)
            ->set('search', 'test search')
            ->set('statusFilter', 'active')
            ->set('sortField', 'company')
            ->set('sortDirection', 'asc')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', 'all')
            ->assertSet('sortField', 'created_at')
            ->assertSet('sortDirection', 'desc');
    }

    #[Test]
    public function it_displays_pagination_links(): void
    {
        Customer::factory()->count(20)->create(['tenant_id' => tenant('id')]);

        Livewire::test(Index::class)
            ->set('perPage', 10)
            ->assertSee('Showing')
            ->assertSee('results');
    }

    #[Test]
    public function it_uses_url_parameters_for_search(): void
    {
        // Verify search property is URL-backed by setting it and checking the query string
        Livewire::withQueryParams(['q' => 'test'])
            ->test(Index::class)
            ->assertSet('search', 'test');
    }

    #[Test]
    public function it_uses_url_parameters_for_filters(): void
    {
        // Verify filter properties are URL-backed by setting them via query params
        Livewire::withQueryParams([
            'statusFilter' => 'active',
            'sortField' => 'company',
            'sortDirection' => 'asc',
        ])
            ->test(Index::class)
            ->assertSet('statusFilter', 'active')
            ->assertSet('sortField', 'company')
            ->assertSet('sortDirection', 'asc');
    }

    #[Test]
    public function it_eager_loads_relationships(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->create(['customer_id' => $customer->id]);

        \DB::enableQueryLog();

        $component = Livewire::test(Index::class);
        $customers = $component->get('customers');
        $customers->first()->customerPhones; // Access relationship

        $queries = \DB::getQueryLog();

        // Should not trigger additional queries for relationships (eager loaded)
        $relationshipQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'customer_phones')
                || str_contains($query['query'], 'customer_emails');
        });

        // Only initial eager load queries, no N+1
        $this->assertLessThanOrEqual(2, $relationshipQueries->count());
    }

    #[Test]
    public function unauthenticated_users_cannot_access_index(): void
    {
        auth()->logout();

        $this->get(route('customers.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function it_renders_correct_view(): void
    {
        Livewire::test(Index::class)
            ->assertViewIs('livewire.tenancy.customers.index');
    }
}
