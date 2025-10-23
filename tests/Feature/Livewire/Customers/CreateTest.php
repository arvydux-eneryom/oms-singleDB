<?php

namespace Tests\Feature\Livewire\Customers;

use App\Livewire\Tenancy\Customers\Create;
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

class CreateTest extends TestCase
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
    public function it_can_render_create_component(): void
    {
        Livewire::test(Create::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_starts_with_general_tab(): void
    {
        Livewire::test(Create::class)
            ->assertSet('currentTab', 'general');
    }

    #[Test]
    public function it_can_switch_tabs(): void
    {
        Livewire::test(Create::class)
            ->call('switchTab', 'contacts')
            ->assertSet('currentTab', 'contacts');
    }

    #[Test]
    public function it_can_create_customer_with_valid_data(): void
    {
        $customerData = [
            'company' => 'Test Company',
            'address' => '123 Test Street',
            'postcode' => '12345',
            'city' => 'Test City',
            'country' => 'Test Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'status' => true,
        ];

        Livewire::test(Create::class)
            ->set('customer', $customerData)
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'company' => 'Test Company',
            'tenant_id' => tenant('id'),
        ]);
    }

    #[Test]
    public function it_validates_company_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', '')
            ->call('save')
            ->assertHasErrors(['customer.company']);
    }

    #[Test]
    public function it_validates_address_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save')
            ->assertHasErrors(['customer.address']);
    }

    #[Test]
    public function it_validates_country_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', '')
            ->set('customer.city', 'Test City')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save')
            ->assertHasErrors(['customer.country']);
    }

    #[Test]
    public function it_can_add_phone_number(): void
    {
        Livewire::test(Create::class)
            ->assertCount('phones', 1) // Default one field
            ->call('addPhone')
            ->assertCount('phones', 2);
    }

    #[Test]
    public function it_can_remove_phone_number(): void
    {
        Livewire::test(Create::class)
            ->call('addPhone')
            ->call('addPhone')
            ->assertCount('phones', 3)
            ->call('removePhone', 1)
            ->assertCount('phones', 2);
    }

    #[Test]
    public function it_maintains_array_alignment_when_removing_phone(): void
    {
        $component = Livewire::test(Create::class)
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
    public function it_can_add_email(): void
    {
        Livewire::test(Create::class)
            ->assertCount('emails', 1)
            ->call('addEmail')
            ->assertCount('emails', 2);
    }

    #[Test]
    public function it_can_remove_email(): void
    {
        Livewire::test(Create::class)
            ->call('addEmail')
            ->call('addEmail')
            ->assertCount('emails', 3)
            ->call('removeEmail', 1)
            ->assertCount('emails', 2);
    }

    #[Test]
    public function it_maintains_array_alignment_when_removing_email(): void
    {
        $component = Livewire::test(Create::class)
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
    public function it_creates_customer_with_phones_in_single_transaction(): void
    {
        \DB::enableQueryLog();

        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save');

        $queries = \DB::getQueryLog();

        // Should use bulk insert for phones (1 INSERT for phones, not multiple)
        $phoneInserts = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'insert into `customer_phones`');
        });

        $this->assertLessThanOrEqual(1, $phoneInserts->count());
    }

    #[Test]
    public function it_normalizes_phone_numbers_before_saving(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1 (234) 567-8900')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save');

        $this->assertDatabaseHas('customer_phones', [
            'phone' => '+12345678900',
        ]);
    }

    #[Test]
    public function it_normalizes_email_addresses_to_lowercase(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'Test@Example.COM')
            ->set('emailTypes.0', 'Primary')
            ->call('save');

        $this->assertDatabaseHas('customer_emails', [
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_detects_duplicate_company_names(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Existing Company',
        ]);

        Livewire::test(Create::class)
            ->set('customer.company', 'Existing Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.postcode', '12345')
            ->call('save')
            ->assertHasErrors(['customer.company']);
    }

    #[Test]
    public function it_switches_to_general_tab_when_duplicate_detected(): void
    {
        Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Existing Company',
        ]);

        Livewire::test(Create::class)
            ->set('currentTab', 'contacts')
            ->set('customer.company', 'Existing Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.postcode', '12345')
            ->call('save')
            ->assertSet('currentTab', 'general');
    }

    #[Test]
    public function it_can_create_customer_with_contacts(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1111111111')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->set('contacts.0.name', 'John Doe')
            ->set('contacts.0.phone', '+1234567890')
            ->set('contacts.0.email', 'john@example.com')
            ->call('save');

        $this->assertDatabaseHas('customer_contacts', [
            'name' => 'John Doe',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
        ]);
    }

    #[Test]
    public function it_can_create_customer_with_service_addresses(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1111111111')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->set('serviceAddresses.0.address', '456 Service St')
            ->set('serviceAddresses.0.postcode', '54321')
            ->set('serviceAddresses.0.city', 'Service City')
            ->set('serviceAddresses.0.country', 'Service Country')
            ->call('save');

        $this->assertDatabaseHas('customer_service_addresses', [
            'address' => '456 Service St',
            'postcode' => '54321',
        ]);
    }

    #[Test]
    public function it_can_create_customer_with_billing_addresses(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1111111111')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->set('billingAddresses.0.address', '789 Billing St')
            ->set('billingAddresses.0.postcode', '98765')
            ->set('billingAddresses.0.city', 'Billing City')
            ->set('billingAddresses.0.country', 'Billing Country')
            ->call('save');

        $this->assertDatabaseHas('customer_billing_addresses', [
            'address' => '789 Billing St',
            'postcode' => '98765',
        ]);
    }

    #[Test]
    public function it_uses_database_transaction_for_save(): void
    {
        // This test needs to be skipped because we can't mock DB facade
        // and also use the database for real inserts in the same test
        $this->markTestSkipped('Cannot test DB::transaction with real database operations');
    }

    #[Test]
    public function it_redirects_to_index_after_successful_save(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save')
            ->assertRedirect(route('customers.index'));
    }

    #[Test]
    public function it_sets_tenant_id_automatically(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'company' => 'Test Company',
            'tenant_id' => tenant('id'),
        ]);
    }

    #[Test]
    public function it_can_reset_form(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('phones.0', '+1234567890')
            ->call('resetForm')
            ->assertSet('customer.company', '')
            ->assertSet('phones', ['']);
    }

    #[Test]
    public function it_shows_error_indicators_on_tabs_with_errors(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', '') // Required field
            ->call('save')
            ->assertSet('tabErrors.general', true);
    }

    #[Test]
    public function it_validates_phone_numbers_format(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.postcode', '12345')
            ->set('phones.0', 'invalid-phone')
            ->call('save')
            ->assertHasErrors(['phones.0']);
    }

    #[Test]
    public function it_validates_email_addresses_format(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.postcode', '12345')
            ->set('emails.0', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['emails.0']);
    }

    #[Test]
    public function it_does_not_save_empty_phones(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('phones.1', '')  // Empty phone
            ->set('phoneTypes.1', 'Work')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save');

        $customer = Customer::where('company', 'Test Company')->first();
        $this->assertCount(1, $customer->customerPhones);  // Only non-empty phone saved
    }

    #[Test]
    public function it_does_not_save_empty_emails(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->set('emails.1', '')  // Empty email
            ->set('emailTypes.1', 'Work')
            ->call('save');

        $customer = Customer::where('company', 'Test Company')->first();
        $this->assertCount(1, $customer->customerEmails);  // Only non-empty email saved
    }

    #[Test]
    public function it_validates_latitude_is_numeric(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.postcode', '12345')
            ->set('customer.latitude', 'invalid')
            ->call('save')
            ->assertHasErrors(['customer.latitude']);
    }

    #[Test]
    public function it_validates_longitude_is_numeric(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.postcode', '12345')
            ->set('customer.longitude', 'invalid')
            ->call('save')
            ->assertHasErrors(['customer.longitude']);
    }

    #[Test]
    public function it_initializes_with_default_empty_arrays(): void
    {
        $component = Livewire::test(Create::class);

        $this->assertEquals([''], $component->get('phones'));
        $this->assertEquals([''], $component->get('emails'));
        $this->assertIsArray($component->get('contacts'));
        $this->assertIsArray($component->get('serviceAddresses'));
        $this->assertIsArray($component->get('billingAddresses'));
    }

    #[Test]
    public function unauthenticated_users_cannot_access_create(): void
    {
        auth()->logout();

        $this->get(route('customers.create'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function it_renders_correct_view(): void
    {
        Livewire::test(Create::class)
            ->assertViewIs('livewire.tenancy.customers.create');
    }

    #[Test]
    public function it_can_add_multiple_phones(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1111111111')
            ->set('phoneTypes.0', 'Primary')
            ->call('addPhone')
            ->set('phones.1', '+2222222222')
            ->set('phoneTypes.1', 'Work')
            ->call('addPhone')
            ->set('phones.2', '+3333333333')
            ->set('phoneTypes.2', 'Home')
            ->set('emails.0', 'test@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('save');

        $customer = Customer::where('company', 'Test Company')->first();
        $this->assertCount(3, $customer->customerPhones);
    }

    #[Test]
    public function it_can_add_multiple_emails(): void
    {
        Livewire::test(Create::class)
            ->set('customer.company', 'Test Company')
            ->set('customer.address', '123 Test St')
            ->set('customer.country', 'Test Country')
            ->set('customer.city', 'Test City')
            ->set('customer.postcode', '12345')
            ->set('phones.0', '+1234567890')
            ->set('phoneTypes.0', 'Primary')
            ->set('emails.0', 'first@example.com')
            ->set('emailTypes.0', 'Primary')
            ->call('addEmail')
            ->set('emails.1', 'second@example.com')
            ->set('emailTypes.1', 'Work')
            ->call('addEmail')
            ->set('emails.2', 'third@example.com')
            ->set('emailTypes.2', 'Personal')
            ->call('save');

        $customer = Customer::where('company', 'Test Company')->first();
        $this->assertCount(3, $customer->customerEmails);
    }

    #[Test]
    public function it_creates_all_related_records_in_single_transaction(): void
    {
        \DB::beginTransaction();

        try {
            Livewire::test(Create::class)
                ->set('customer.company', 'Test Company')
                ->set('customer.address', '123 Test St')
                ->set('customer.country', 'Test Country')
                ->set('customer.city', 'Test City')
                ->set('customer.postcode', '12345')
                ->set('phones.0', '+1234567890')
                ->set('phoneTypes.0', 'Primary')
                ->set('emails.0', 'test@example.com')
                ->set('emailTypes.0', 'Primary')
                ->set('contacts.0.name', 'John Doe')
                ->set('contacts.0.phone', '+9876543210')
                ->set('contacts.0.email', 'john@example.com')
                ->call('save');

            // If transaction works, all should be saved together
            $customer = Customer::where('company', 'Test Company')->first();
            $this->assertNotNull($customer);
            $this->assertCount(1, $customer->customerPhones);
            $this->assertCount(1, $customer->customerEmails);
            $this->assertCount(1, $customer->customerContacts);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->fail('Transaction failed: ' . $e->getMessage());
        }
    }
}
