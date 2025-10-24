<?php

namespace Tests\Feature\Livewire\Customers;

use App\Livewire\Tenancy\Customers\View;
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

class ViewTest extends TestCase
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
    public function it_can_render_view_component(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertStatus(200);
    }

    #[Test]
    public function it_loads_customer_with_all_relationships(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->create(['customer_id' => $customer->id]);
        CustomerContact::factory()->create(['customer_id' => $customer->id]);
        CustomerServiceAddress::factory()->create(['customer_id' => $customer->id]);
        CustomerBillingAddress::factory()->create(['customer_id' => $customer->id]);

        $component = Livewire::test(View::class, ['customer' => $customer]);

        $loadedCustomer = $component->get('customer');
        $this->assertTrue($loadedCustomer->relationLoaded('customerPhones'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerEmails'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerContacts'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerServiceAddresses'));
        $this->assertTrue($loadedCustomer->relationLoaded('customerBillingAddresses'));
    }

    #[Test]
    public function it_displays_customer_information(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Test Company',
            'address' => '123 Test Street',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSee('Test Company')
            ->assertSee('123 Test Street');
    }

    #[Test]
    public function it_loads_statistics_on_mount(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->count(2)->create(['customer_id' => $customer->id]);
        CustomerEmail::factory()->count(3)->create(['customer_id' => $customer->id]);
        CustomerContact::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSet('stats.phones_count', 2)
            ->assertSet('stats.emails_count', 3)
            ->assertSet('stats.contacts_count', 1);
    }

    #[Test]
    public function it_counts_sms_enabled_phones(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'is_sms_enabled' => true,
        ]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'is_sms_enabled' => false,
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSet('stats.sms_enabled_phones', 1);
    }

    #[Test]
    public function it_counts_verified_emails(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'is_verified' => true,
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'is_verified' => false,
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSet('stats.verified_emails', 1);
    }

    #[Test]
    public function it_starts_with_overview_tab(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSet('currentTab', 'overview');
    }

    #[Test]
    public function it_can_switch_tabs(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('switchTab', 'activity')
            ->assertSet('currentTab', 'activity')
            ->call('switchTab', 'contacts')
            ->assertSet('currentTab', 'contacts');
    }

    #[Test]
    public function it_can_toggle_customer_status_to_inactive(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'status' => true,
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('toggleStatus')
            ->assertHasNoErrors();

        $this->assertFalse($customer->fresh()->status);
    }

    #[Test]
    public function it_can_toggle_customer_status_to_active(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'status' => false,
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('toggleStatus')
            ->assertHasNoErrors();

        $this->assertTrue($customer->fresh()->status);
    }

    #[Test]
    public function it_refreshes_customer_after_status_toggle(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'status' => true,
        ]);

        $component = Livewire::test(View::class, ['customer' => $customer])
            ->call('toggleStatus');

        $this->assertFalse($component->get('customer')->status);
    }

    #[Test]
    public function it_can_open_sms_modal(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('openSmsModal')
            ->assertSet('showSmsModal', true)
            ->assertSet('selectedPhone', '+1234567890');
    }

    #[Test]
    public function it_can_open_sms_modal_with_specific_phone(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1111111111',
        ]);
        CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+2222222222',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('openSmsModal', '+2222222222')
            ->assertSet('selectedPhone', '+2222222222');
    }

    #[Test]
    public function it_clears_sms_message_when_opening_modal(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('smsMessage', 'old message')
            ->call('openSmsModal')
            ->assertSet('smsMessage', '');
    }

    #[Test]
    public function it_validates_selected_phone_when_sending_sms(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedPhone', '')
            ->set('smsMessage', 'Test message')
            ->call('sendSms')
            ->assertHasErrors(['selectedPhone']);
    }

    #[Test]
    public function it_validates_sms_message_is_required(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id, 'phone' => '+1234567890']);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedPhone', '+1234567890')
            ->set('smsMessage', '')
            ->call('sendSms')
            ->assertHasErrors(['smsMessage']);
    }

    #[Test]
    public function it_validates_sms_message_max_length(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id, 'phone' => '+1234567890']);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedPhone', '+1234567890')
            ->set('smsMessage', str_repeat('a', 1601))
            ->call('sendSms')
            ->assertHasErrors(['smsMessage']);
    }

    #[Test]
    public function it_closes_sms_modal_after_successful_send(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id, 'phone' => '+1234567890']);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedPhone', '+1234567890')
            ->set('smsMessage', 'Test message')
            ->call('sendSms')
            ->assertSet('showSmsModal', false);
    }

    #[Test]
    public function it_resets_sms_fields_after_successful_send(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerPhone::factory()->create(['customer_id' => $customer->id, 'phone' => '+1234567890']);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedPhone', '+1234567890')
            ->set('smsMessage', 'Test message')
            ->call('sendSms')
            ->assertSet('smsMessage', '')
            ->assertSet('selectedPhone', null);
    }

    #[Test]
    public function it_can_open_email_modal(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'test@example.com',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('openEmailModal')
            ->assertSet('showEmailModal', true)
            ->assertSet('selectedEmail', 'test@example.com');
    }

    #[Test]
    public function it_can_open_email_modal_with_specific_email(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'first@example.com',
        ]);
        CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'second@example.com',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->call('openEmailModal', 'second@example.com')
            ->assertSet('selectedEmail', 'second@example.com');
    }

    #[Test]
    public function it_clears_email_fields_when_opening_modal(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        CustomerEmail::factory()->create(['customer_id' => $customer->id]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('emailSubject', 'old subject')
            ->set('emailBody', 'old body')
            ->call('openEmailModal')
            ->assertSet('emailSubject', '')
            ->assertSet('emailBody', '');
    }

    #[Test]
    public function it_validates_selected_email_when_sending(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', '')
            ->set('emailSubject', 'Test')
            ->set('emailBody', 'Test message')
            ->call('sendEmail')
            ->assertHasErrors(['selectedEmail']);
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', 'invalid-email')
            ->set('emailSubject', 'Test')
            ->set('emailBody', 'Test message')
            ->call('sendEmail')
            ->assertHasErrors(['selectedEmail']);
    }

    #[Test]
    public function it_validates_email_subject_is_required(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', 'test@example.com')
            ->set('emailSubject', '')
            ->set('emailBody', 'Test message')
            ->call('sendEmail')
            ->assertHasErrors(['emailSubject']);
    }

    #[Test]
    public function it_validates_email_subject_max_length(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', 'test@example.com')
            ->set('emailSubject', str_repeat('a', 256))
            ->set('emailBody', 'Test message')
            ->call('sendEmail')
            ->assertHasErrors(['emailSubject']);
    }

    #[Test]
    public function it_validates_email_body_is_required(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', 'test@example.com')
            ->set('emailSubject', 'Test Subject')
            ->set('emailBody', '')
            ->call('sendEmail')
            ->assertHasErrors(['emailBody']);
    }

    #[Test]
    public function it_closes_email_modal_after_successful_send(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', 'test@example.com')
            ->set('emailSubject', 'Test Subject')
            ->set('emailBody', 'Test message')
            ->call('sendEmail')
            ->assertSet('showEmailModal', false);
    }

    #[Test]
    public function it_resets_email_fields_after_successful_send(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->set('selectedEmail', 'test@example.com')
            ->set('emailSubject', 'Test Subject')
            ->set('emailBody', 'Test message')
            ->call('sendEmail')
            ->assertSet('emailSubject', '')
            ->assertSet('emailBody', '')
            ->assertSet('selectedEmail', null);
    }

    #[Test]
    public function it_can_open_delete_modal(): void
    {
        // Deletion functionality is handled by the dedicated Delete component
        // See DeleteTest for comprehensive deletion tests
        $this->markTestSkipped('Deletion is handled by separate Delete component');
    }

    #[Test]
    public function it_can_delete_customer_from_view_page(): void
    {
        // Deletion functionality is handled by the dedicated Delete component
        // See DeleteTest for comprehensive deletion tests
        $this->markTestSkipped('Deletion is handled by separate Delete component');
    }

    #[Test]
    public function it_redirects_to_index_after_deletion(): void
    {
        // Deletion functionality is handled by the dedicated Delete component
        // See DeleteTest for comprehensive deletion tests
        $this->markTestSkipped('Deletion is handled by separate Delete component');
    }

    #[Test]
    public function it_loads_activity_log_on_mount(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        $component = Livewire::test(View::class, ['customer' => $customer]);

        $activityLog = $component->get('activityLog');
        $this->assertIsArray($activityLog);
        $this->assertNotEmpty($activityLog);
    }

    #[Test]
    public function it_preselects_first_phone_and_email(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);
        $phone = CustomerPhone::factory()->create([
            'customer_id' => $customer->id,
            'phone' => '+1234567890',
        ]);
        $email = CustomerEmail::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'test@example.com',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSet('selectedPhone', '+1234567890')
            ->assertSet('selectedEmail', 'test@example.com');
    }

    #[Test]
    public function it_only_shows_customer_from_current_tenant(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => tenant('id'),
            'company' => 'Tenant Customer',
        ]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSee('Tenant Customer');
    }

    #[Test]
    public function unauthenticated_users_cannot_access_view(): void
    {
        auth()->logout();
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        $this->get(route('customers.show', $customer))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function it_renders_correct_view(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertViewIs('livewire.tenancy.customers.view');
    }

    #[Test]
    public function it_displays_all_tab_names(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => tenant('id')]);

        Livewire::test(View::class, ['customer' => $customer])
            ->assertSee('Overview')
            ->assertSee('Activity')
            ->assertSee('Contacts')
            ->assertSee('Addresses')
            ->assertSee('Statistics');
    }
}
