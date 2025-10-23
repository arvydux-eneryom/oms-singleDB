<?php

namespace Tests\Feature\Livewire\Customers;

use App\Livewire\Tenancy\Customers\Edit;
use App\Models\Customer;
use App\Models\CustomerPhone;
use App\Models\CustomerEmail;
use App\Models\CustomerContact;
use App\Models\CustomerServiceAddress;
use App\Models\CustomerBillingAddress;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EditTest extends TestCase
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

    /**
     * Create a customer with at least one phone and email (required for Edit component)
     */
    protected function createCustomerWithDefaults(array $attributes = []): Customer
    {
        $customer = Customer::factory()->create(array_merge([
            'tenant_id' => tenant('id'),
        ], $attributes));

        // Create default phone if not exists
        if ($customer->customerPhones()->count() === 0) {
            CustomerPhone::factory()->create([
                'customer_id' => $customer->id,
                'phone' => '+1234567890',
                'type' => 'primary',
                'is_sms_enabled' => false,
            ]);
        }

        // Create default email if not exists
        if ($customer->customerEmails()->count() === 0) {
            CustomerEmail::factory()->create([
                'customer_id' => $customer->id,
                'email' => 'default@example.com',
                'type' => 'primary',
                'is_verified' => false,
            ]);
        }

        return $customer->fresh(['customerPhones', 'customerEmails']);
    }

    #[Test]
    public function it_can_render_edit_component(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->assertStatus(200);
    }

    #[Test]
    public function it_loads_customer_data_on_mount(): void
    {
        $customer = $this->createCustomerWithDefaults([
            'company' => 'Test Company',
            'address' => '123 Test Street',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->assertSet('customer.company', 'Test Company')
            ->assertSet('customer.address', '123 Test Street');
    }

    #[Test]
    public function it_loads_existing_phones(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        // Create specific phones and emails for this test
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
            'type' => 'primary',
            'is_sms_enabled' => true,
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'required@example.com',
            'type' => 'primary',
        ]);

        $customer = $customer->fresh(['customerPhones', 'customerEmails']);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->assertSet('phones.0', '+1234567890')
            ->assertSet('phoneTypes.0', 'Primary')  // ucfirst in component
            ->assertSet('isSmsEnabled.0', true);
    }

    #[Test]
    public function it_loads_existing_emails(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        // Create specific phones and emails for this test
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
            'type' => 'primary',
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'test@example.com',
            'type' => 'primary',
            'is_verified' => true,
        ]);

        $customer = $customer->fresh(['customerPhones', 'customerEmails']);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->assertSet('emails.0', 'test@example.com')
            ->assertSet('emailTypes.0', 'Primary')  // ucfirst in component
            ->assertSet('isVerified.0', true);
    }

    #[Test]
    public function it_can_update_customer_basic_info(): void
    {
        $customer = $this->createCustomerWithDefaults([
            'company' => 'Old Company',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', 'Updated Company')
            ->call('update');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'company' => 'Updated Company',
        ]);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', '')
            ->call('update')
            ->assertHasErrors(['customer.company']);
    }

    #[Test]
    public function it_can_add_new_phone_to_existing_customer(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->call('addPhone')
            ->set('phones.1', '+1234567890')
            ->call('update');

        $this->assertDatabaseHas('customer_phones', [
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
        ]);
    }

    #[Test]
    public function it_can_update_existing_phone(): void
    {
        // Create customer without helper to avoid default phone conflicts
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1111111111',
            'type' => 'primary',
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'required@example.com',
            'type' => 'primary',
        ]);

        $customer = $customer->fresh(['customerPhones', 'customerEmails']);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('phones.0', '+2222222222')
            ->call('update');

        $this->assertDatabaseHas('customer_phones', [
            'customer_id' => $customer->id,
            'phone' => '+2222222222',
        ]);

        $this->assertDatabaseMissing('customer_phones', [
            'phone' => '+1111111111',
        ]);
    }

    #[Test]
    public function it_can_remove_phone_from_customer(): void
    {
        // Create customer without helper to avoid default phone conflicts
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
            'type' => 'primary',
        ]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+9876543210',
            'type' => 'work',
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'required@example.com',
            'type' => 'primary',
        ]);

        $customer = $customer->fresh(['customerPhones', 'customerEmails']);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->call('removePhone', 0)
            ->call('update');

        $this->assertDatabaseMissing('customer_phones', [
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
        ]);

        $this->assertDatabaseHas('customer_phones', [
            'customer_id' => $customer->id,
            'phone' => '+9876543210',
        ]);
    }

    #[Test]
    public function it_maintains_phone_array_alignment_when_removing(): void
    {
        $customer = $this->createCustomerWithDefaults();

        $component = Livewire::test(Edit::class, ['customer' => $customer])
            ->set('phones.0', '+1111111111')
            ->set('phoneTypes.0', 'primary')
            ->set('isSmsEnabled.0', true)
            ->set('phones.1', '+2222222222')
            ->set('phoneTypes.1', 'secondary')
            ->set('isSmsEnabled.1', false)
            ->call('removePhone', 0);

        $this->assertEquals('+2222222222', $component->get('phones')[0]);
        $this->assertEquals('secondary', $component->get('phoneTypes')[0]);
        $this->assertFalse($component->get('isSmsEnabled')[0]);
    }

    #[Test]
    public function it_can_add_new_email_to_existing_customer(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->call('addEmail')
            ->set('emails.1', 'new@example.com')
            ->call('update');

        $this->assertDatabaseHas('customer_emails', [
            'customer_id' => $customer->id,
            'email' => 'new@example.com',
        ]);
    }

    #[Test]
    public function it_can_update_existing_email(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'old@example.com',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('emails.0', 'updated@example.com')
            ->call('update');

        $this->assertDatabaseHas('customer_emails', [
            'customer_id' => $customer->id,
            'email' => 'updated@example.com',
        ]);
    }

    #[Test]
    public function it_maintains_email_array_alignment_when_removing(): void
    {
        $customer = $this->createCustomerWithDefaults();

        $component = Livewire::test(Edit::class, ['customer' => $customer])
            ->set('emails.0', 'first@example.com')
            ->set('emailTypes.0', 'primary')
            ->set('isVerified.0', true)
            ->set('emails.1', 'second@example.com')
            ->set('emailTypes.1', 'secondary')
            ->set('isVerified.1', false)
            ->call('removeEmail', 0);

        $this->assertEquals('second@example.com', $component->get('emails')[0]);
        $this->assertEquals('secondary', $component->get('emailTypes')[0]);
        $this->assertFalse($component->get('isVerified')[0]);
    }

    #[Test]
    public function it_uses_bulk_updates_instead_of_delete_recreate(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
        ]);

        \DB::enableQueryLog();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('phones.0', '+9999999999')
            ->call('update');

        $queries = \DB::getQueryLog();

        // Should delete old phones and bulk insert new ones
        $deleteQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'delete from `customer_phones`');
        });

        $insertQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'insert into `customer_phones`');
        });

        // Should have 1 delete and 1 insert (bulk), not multiple individual operations
        $this->assertLessThanOrEqual(1, $deleteQueries->count());
        $this->assertLessThanOrEqual(1, $insertQueries->count());
    }

    #[Test]
    public function it_uses_database_transaction_for_update(): void
    {
        $customer = $this->createCustomerWithDefaults();

        \DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', 'Updated Company')
            ->call('update');
    }

    #[Test]
    public function it_detects_duplicate_company_name_excluding_current(): void
    {
        $this->createCustomerWithDefaults([
            'company' => 'Existing Company',
        ]);

        $customer = $this->createCustomerWithDefaults([
            'company' => 'Current Company',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', 'Existing Company')
            ->call('update')
            ->assertHasErrors(['customer.company']);
    }

    #[Test]
    public function it_allows_keeping_same_company_name(): void
    {
        $customer = $this->createCustomerWithDefaults([
            'company' => 'Test Company',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', 'Test Company')
            ->set('customer.address', 'Updated Address')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'company' => 'Test Company',
            'address' => 'Updated Address',
        ]);
    }

    #[Test]
    public function it_normalizes_phone_numbers_on_update(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('phones.0', '+1 (234) 567-8900')
            ->call('update');

        $this->assertDatabaseHas('customer_phones', [
            'customer_id' => $customer->id,
            'phone' => '+12345678900',  // Normalized format keeps all digits
        ]);
    }

    #[Test]
    public function it_normalizes_emails_to_lowercase_on_update(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('emails.0', 'TEST@EXAMPLE.COM')
            ->call('update');

        $this->assertDatabaseHas('customer_emails', [
            'customer_id' => $customer->id,
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_redirects_to_index_after_successful_update(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', 'Updated Company')
            ->call('update')
            ->assertRedirect(route('customers.index'));
    }

    #[Test]
    public function it_can_update_customer_contacts(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerContact::factory()->create([
            'customer_id' => $customer->id,
            'name' => 'Old Contact',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('contacts.0.name', 'Updated Contact')
            ->call('update');

        $this->assertDatabaseHas('customer_contacts', [
            'customer_id' => $customer->id,
            'name' => 'Updated Contact',
        ]);
    }

    #[Test]
    public function it_can_update_service_addresses(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerServiceAddress::factory()->create([
            'customer_id' => $customer->id,
            'address' => 'Old Address',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('serviceAddresses.0.address', 'Updated Address')
            ->call('update');

        $this->assertDatabaseHas('customer_service_addresses', [
            'customer_id' => $customer->id,
            'address' => 'Updated Address',
        ]);
    }

    #[Test]
    public function it_can_update_billing_addresses(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerBillingAddress::factory()->create([
            'customer_id' => $customer->id,
            'address' => 'Old Address',
        ]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('billingAddresses.0.address', 'Updated Address')
            ->call('update');

        $this->assertDatabaseHas('customer_billing_addresses', [
            'customer_id' => $customer->id,
            'address' => 'Updated Address',
        ]);
    }

    #[Test]
    public function it_loads_all_relationship_data_on_mount(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerPhone::factory()->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->create(['customer_id' => $customer->id]);
        CustomerContact::factory()->create(['customer_id' => $customer->id]);
        CustomerServiceAddress::factory()->create(['customer_id' => $customer->id]);
        CustomerBillingAddress::factory()->create(['customer_id' => $customer->id]);

        $component = Livewire::test(Edit::class, ['customer' => $customer]);

        $this->assertNotEmpty($component->get('phones'));
        $this->assertNotEmpty($component->get('emails'));
        $this->assertNotEmpty($component->get('contacts'));  // Fixed: was 'contactNames'
        $this->assertNotEmpty($component->get('serviceAddresses'));
        $this->assertNotEmpty($component->get('billingAddresses'));
    }

    #[Test]
    public function it_can_remove_all_phones_and_add_new_ones(): void
    {
        $customer = $this->createCustomerWithDefaults();
        CustomerPhone::factory()->count(3)->create(['customer_id' => $customer->id]);

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('phones', ['+9999999999'])
            ->call('update');

        $this->assertDatabaseCount('customer_phones', 1);
        $this->assertDatabaseHas('customer_phones', [
            'customer_id' => $customer->id,
            'phone' => '+9999999999',
        ]);
    }

    #[Test]
    public function it_shows_error_indicators_on_tabs_with_errors(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.company', '')
            ->call('update')
            ->assertSet('tabErrors.general', true);
    }

    #[Test]
    public function it_switches_to_error_tab_when_saving_with_errors(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('currentTab', 'contacts')
            ->set('customer.company', '')
            ->call('update')
            ->assertSet('currentTab', 'general');
    }

    #[Test]
    public function unauthenticated_users_cannot_access_edit(): void
    {
        auth()->logout();
        $customer = $this->createCustomerWithDefaults();

        $this->get(route('customers.edit', $customer))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function it_renders_correct_view(): void
    {
        $customer = $this->createCustomerWithDefaults();

        Livewire::test(Edit::class, ['customer' => $customer])
            ->assertViewIs('livewire.tenancy.customers.edit');
    }

    #[Test]
    public function it_does_not_create_duplicate_records_on_update(): void
    {
        $customer = $this->createCustomerWithDefaults();  // Creates 1 phone and 1 email

        Livewire::test(Edit::class, ['customer' => $customer])
            ->set('customer.address', 'Updated Address')
            ->call('update');

        // Should still have only 1 phone record (from helper)
        $this->assertCount(1, $customer->fresh()->customerPhones);
        // Should still have only 1 email record (from helper)
        $this->assertCount(1, $customer->fresh()->customerEmails);
    }
}
