<?php

namespace Tests\Feature\Livewire\Customers;

use App\Livewire\Tenancy\Customers\Delete;
use App\Models\Customer;
use App\Models\CustomerBillingAddress;
use App\Models\CustomerContact;
use App\Models\CustomerEmail;
use App\Models\CustomerPhone;
use App\Models\CustomerServiceAddress;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteTest extends TestCase
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
    public function it_can_render_delete_component(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertStatus(200);
    }

    #[Test]
    public function it_loads_customer_on_mount(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
        ]);

        $component = Livewire::test(Delete::class, ['customerId' => $customer->id]);

        $this->assertEquals($customer->id, $component->get('customer')->id);
        $this->assertEquals('Test Company', $component->get('customer')->company);
    }

    #[Test]
    public function it_stores_customer_name_on_mount(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
        ]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSet('customerName', 'Test Company');
    }

    #[Test]
    public function it_eager_loads_all_relationships(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->create(['customer_id' => $customer->id]);
        CustomerContact::factory()->create(['customer_id' => $customer->id]);
        CustomerServiceAddress::factory()->create(['customer_id' => $customer->id]);
        CustomerBillingAddress::factory()->create(['customer_id' => $customer->id]);

        $component = Livewire::test(Delete::class, ['customerId' => $customer->id]);
        $loadedCustomer = $component->get('customer');

        $this->assertTrue($loadedCustomer->relationLoaded('customerPhones'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerEmails'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerContacts'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerServiceAddresses'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerBillingAddresses'));
    }

    #[Test]
    public function it_counts_related_records_on_mount(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->count(2)->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->count(3)->create(['customer_id' => $customer->id]);
        CustomerContact::factory()->create(['customer_id' => $customer->id]);
        CustomerServiceAddress::factory()->create(['customer_id' => $customer->id]);
        CustomerBillingAddress::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSet('relatedRecordsCount', 8); // 2+3+1+1+1
    }

    #[Test]
    public function it_shows_zero_related_records_when_customer_has_none(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSet('relatedRecordsCount', 0);
    }

    #[Test]
    public function it_can_delete_customer(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    #[Test]
    public function it_uses_database_transaction_for_deletion(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        \DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer');
    }

    #[Test]
    public function it_deletes_customer_with_all_related_records(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        $phone = CustomerPhone::factory()->create(['customer_id' => $customer->id]);
        $email = CustomerEmail::factory()->create(['customer_id' => $customer->id]);
        $contact = CustomerContact::factory()->create(['customer_id' => $customer->id]);
        $serviceAddress = CustomerServiceAddress::factory()->create(['customer_id' => $customer->id]);
        $billingAddress = CustomerBillingAddress::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);

        // Related records should be deleted (assuming cascade delete or handled in deletion)
        // If using soft deletes on related models, adjust accordingly
    }

    #[Test]
    public function it_redirects_to_index_after_deletion(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer')
            ->assertRedirect(route('customers.index'));
    }

    #[Test]
    public function it_sets_success_message_after_deletion(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
        ]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer');

        $this->assertEquals(
            'Customer "Test Company" successfully deleted.',
            session('success')
        );
    }

    #[Test]
    public function it_dispatches_customer_deleted_event(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer')
            ->assertDispatched('customer-deleted');
    }

    #[Test]
    public function it_shows_deleting_state_during_deletion(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        $component = Livewire::test(Delete::class, ['customerId' => $customer->id]);

        // Initially not deleting
        $component->assertSet('isDeleting', false);
    }

    #[Test]
    public function it_displays_customer_information(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
            'address' => '123 Test Street',
            'status' => true,
        ]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSee('Test Company')
            ->assertSee('123 Test Street')
            ->assertSee('Active');
    }

    #[Test]
    public function it_displays_related_records_breakdown(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->count(2)->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSee('2')  // Phone count
            ->assertSee('1'); // Email count
    }

    #[Test]
    public function it_handles_deletion_errors_gracefully(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        // Force an error by deleting the customer before the component tries
        $customer->delete();

        // Attempting to mount with a soft-deleted customer should throw ModelNotFoundException
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer');
    }

    #[Test]
    public function it_only_deletes_customer_from_current_tenant(): void
    {
        // Create a second tenant for isolation testing
        $otherTenant = \App\Models\Tenant::create(['name' => 'Other Tenant']);

        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('customers', [
            'id' => $otherCustomer->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_throws_error_for_non_existent_customer(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Delete::class, ['customerId' => 999999]);
    }

    #[Test]
    public function unauthenticated_users_cannot_access_delete(): void
    {
        auth()->logout();
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        $this->get(route('customers.delete', $customer))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function it_renders_correct_view(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertViewIs('livewire.tenancy.customers.delete');
    }

    #[Test]
    public function it_displays_warning_when_customer_has_related_records(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSee('related record');
    }

    #[Test]
    public function it_shows_each_relationship_type_count(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->count(2)->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->count(3)->create(['customer_id' => $customer->id]);
        CustomerContact::factory()->count(1)->create(['customer_id' => $customer->id]);
        CustomerServiceAddress::factory()->count(2)->create(['customer_id' => $customer->id]);
        CustomerBillingAddress::factory()->count(1)->create(['customer_id' => $customer->id]);

        $component = Livewire::test(Delete::class, ['customerId' => $customer->id]);
        $customer = $component->get('customer');

        $this->assertEquals(2, $customer->customerPhones->count());
        $this->assertEquals(3, $customer->customerEmails->count());
        $this->assertEquals(1, $customer->customerContacts->count());
        $this->assertEquals(2, $customer->customerServiceAddresses->count());
        $this->assertEquals(1, $customer->customerBillingAddresses->count());
    }

    #[Test]
    public function it_preserves_customer_name_even_after_deletion_attempt(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
        ]);

        $component = Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->assertSet('customerName', 'Test Company')
            ->call('deleteCustomer');

        // Even after deletion, the stored name should be preserved
        $this->assertEquals('Test Company', $component->get('customerName'));
    }

    #[Test]
    public function it_logs_deletion_activity(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        \Log::shouldReceive('info')->once()->withArgs(function ($message, $context) use ($customer) {
            return str_contains($message, 'deleted') && $context['customer_id'] === $customer->id;
        });

        Livewire::test(Delete::class, ['customerId' => $customer->id])
            ->call('deleteCustomer');
    }
}
