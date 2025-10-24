<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    // Customer model
    public Customer $customerModel;

    // Form data
    public $customer = [];

    public $phones = [];

    public $phoneTypes = [];

    public $isSmsEnabled = [];

    public $emails = [];

    public $emailTypes = [];

    public $isVerified = [];

    public $contacts = [];

    public $serviceAddresses = [];

    public $billingAddresses = [];

    // UI state
    public string $currentTab = 'general';

    public bool $isSubmitting = false;

    public array $tabErrors = [];

    public int $tenantId = 0;

    public string $googleMapsApiKey = '';

    // Options
    public $phoneTypeOptions = ['Primary', 'Work', 'Home', 'Emergency'];

    public $emailTypeOptions = ['Primary', 'Work', 'Personal'];

    // Track original company name for duplicate detection
    public string $originalCompanyName = '';

    // Validation messages
    protected $messages = [
        'customer.company.required' => 'Please enter the company name.',
        'customer.company.unique' => 'A customer with this company name already exists.',
        'customer.address.required' => 'Please enter the primary address.',
        'customer.country.required' => 'Please enter the country.',
        'customer.city.required' => 'Please enter the city.',
        'phones.0.required' => 'At least one phone number is required.',
        'phones.*.regex' => 'Please enter a valid phone number.',
        'phoneTypes.0.required' => 'Please select a type for the first phone.',
        'emails.0.required' => 'At least one email address is required.',
        'emails.0.email' => 'Please enter a valid email address.',
        'emailTypes.0.required' => 'Please select a type for the first email.',
    ];

    // Validation rules
    protected $rules = [
        'customer.company' => ['required', 'string', 'max:255'],
        'customer.address' => ['required', 'string'],
        'customer.country' => ['required', 'string', 'max:255'],
        'customer.city' => ['required', 'string', 'max:100'],
        'customer.postcode' => ['nullable', 'string', 'max:20'],
        'customer.latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'customer.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'customer.status' => ['boolean'],
        'phones.0' => ['required', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
        'phones.*' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
        'phoneTypes.0' => ['required'],
        'phoneTypes' => 'array',
        'isSmsEnabled' => 'array',
        'emails.0' => ['required', 'email', 'max:255'],
        'emails.*' => ['nullable', 'email', 'max:255'],
        'emailTypes.0' => ['required'],
        'emailTypes' => 'array',
        'isVerified' => 'array',
        'contacts.*.name' => ['nullable', 'string', 'max:255'],
        'contacts.*.email' => ['nullable', 'email', 'max:255'],
        'contacts.*.phone' => ['nullable', 'string', 'max:20'],
        'serviceAddresses.*.address' => ['nullable', 'string'],
        'serviceAddresses.*.country' => ['nullable', 'string', 'max:255'],
        'serviceAddresses.*.city' => ['nullable', 'string', 'max:100'],
        'serviceAddresses.*.postcode' => ['nullable', 'string', 'max:20'],
        'serviceAddresses.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'serviceAddresses.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'billingAddresses.*.address' => ['nullable', 'string'],
        'billingAddresses.*.country' => ['nullable', 'string', 'max:255'],
        'billingAddresses.*.city' => ['nullable', 'string', 'max:100'],
        'billingAddresses.*.postcode' => ['nullable', 'string', 'max:20'],
        'billingAddresses.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'billingAddresses.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
    ];

    public function mount(Customer $customer)
    {
        // Load customer with all relations
        $this->customerModel = $customer->load([
            'customerPhones',
            'customerEmails',
            'customerContacts',
            'customerServiceAddresses',
            'customerBillingAddresses',
        ]);

        $this->tenantId = tenant('id');
        $this->googleMapsApiKey = config('services.google_maps.api_key') ?? '';
        $this->originalCompanyName = $customer->company;

        // Load customer basic data
        $this->customer = [
            'company' => $customer->company,
            'address' => $customer->address,
            'country' => $customer->country,
            'city' => $customer->city,
            'postcode' => $customer->postcode,
            'latitude' => $customer->latitude,
            'longitude' => $customer->longitude,
            'status' => (bool) $customer->status,
        ];

        // Load phones with proper normalization
        $this->phones = $customer->customerPhones->pluck('phone')->toArray();
        $this->phoneTypes = $customer->customerPhones->pluck('type')
            ->map(fn ($type) => ucfirst($type ?: 'primary'))
            ->toArray();
        $this->isSmsEnabled = $customer->customerPhones->pluck('is_sms_enabled')
            ->map(fn ($val) => (bool) $val)
            ->toArray();

        // Load emails with proper normalization
        $this->emails = $customer->customerEmails->pluck('email')->toArray();
        $this->emailTypes = $customer->customerEmails->pluck('type')
            ->map(fn ($type) => ucfirst($type ?: 'primary'))
            ->toArray();
        $this->isVerified = $customer->customerEmails->pluck('is_verified')
            ->map(fn ($val) => (bool) $val)
            ->toArray();

        // Load contacts
        $this->contacts = $customer->customerContacts->map(fn ($contact) => [
            'name' => $contact->name ?? '',
            'phone' => $contact->phone ?? '',
            'email' => $contact->email ?? '',
        ])->toArray();

        // Add empty contact if none exist
        if (empty($this->contacts)) {
            $this->contacts = [['name' => '', 'phone' => '', 'email' => '']];
        }

        // Load service addresses
        $this->serviceAddresses = $customer->customerServiceAddresses->map(fn ($address) => [
            'address' => $address->address ?? '',
            'country' => $address->country ?? '',
            'city' => $address->city ?? '',
            'postcode' => $address->postcode ?? '',
            'latitude' => $address->latitude ?? '',
            'longitude' => $address->longitude ?? '',
        ])->toArray();

        // Add empty service address if none exist
        if (empty($this->serviceAddresses)) {
            $this->serviceAddresses = [[
                'address' => '', 'country' => '', 'city' => '',
                'postcode' => '', 'latitude' => '', 'longitude' => '',
            ]];
        }

        // Load billing addresses
        $this->billingAddresses = $customer->customerBillingAddresses->map(fn ($address) => [
            'address' => $address->address ?? '',
            'country' => $address->country ?? '',
            'city' => $address->city ?? '',
            'postcode' => $address->postcode ?? '',
            'latitude' => $address->latitude ?? '',
            'longitude' => $address->longitude ?? '',
        ])->toArray();

        // Add empty billing address if none exist
        if (empty($this->billingAddresses)) {
            $this->billingAddresses = [[
                'address' => '', 'country' => '', 'city' => '',
                'postcode' => '', 'latitude' => '', 'longitude' => '',
            ]];
        }
    }

    public function updated($field)
    {
        $this->validateOnly($field);
        $this->updateTabErrors();
    }

    public function updatedCurrentTab()
    {
        $this->dispatch('tab-changed', tab: $this->currentTab);
    }

    // Tab management
    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->dispatch('tab-changed', tab: $tab);
    }

    // Computed property for tab error indicators
    #[Computed]
    public function hasTabErrors(): array
    {
        return $this->tabErrors;
    }

    private function updateTabErrors(): void
    {
        $errors = $this->getErrorBag();

        $this->tabErrors = [
            'general' => $errors->has('customer.*') || $errors->has('phones.*') ||
                         $errors->has('phoneTypes.*') || $errors->has('emails.*') ||
                         $errors->has('emailTypes.*'),
            'contacts' => $errors->has('contacts.*'),
            'service' => $errors->has('serviceAddresses.*'),
            'billing' => $errors->has('billingAddresses.*'),
        ];
    }

    // Data normalization methods
    private function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/[^\d+]/', '', $phone);
        $normalized = ltrim($normalized, '+');
        if (str_starts_with($phone, '+')) {
            $normalized = '+'.$normalized;
        }

        return $normalized;
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    // Duplicate detection (excluding current customer)
    private function checkDuplicateCustomer(): ?Customer
    {
        return Customer::where('tenant_id', $this->tenantId)
            ->where('company', $this->customer['company'])
            ->where('id', '!=', $this->customerModel->id)
            ->first();
    }

    // Array management methods
    public function addPhone(): void
    {
        $this->phones[] = '';
        $this->phoneTypes[] = 'Primary';
        $this->isSmsEnabled[] = false;
    }

    public function addEmail(): void
    {
        $this->emails[] = '';
        $this->emailTypes[] = 'Primary';
        $this->isVerified[] = false;
    }

    public function addContact(): void
    {
        $this->contacts[] = ['name' => '', 'phone' => '', 'email' => ''];
    }

    public function addServiceAddress(): void
    {
        $this->serviceAddresses[] = [
            'address' => '', 'country' => '', 'city' => '',
            'postcode' => '', 'latitude' => '', 'longitude' => '',
        ];
    }

    public function addBillingAddress(): void
    {
        $this->billingAddresses[] = [
            'address' => '', 'country' => '', 'city' => '',
            'postcode' => '', 'latitude' => '', 'longitude' => '',
        ];
    }

    public function removePhone($index): void
    {
        unset($this->phones[$index], $this->phoneTypes[$index], $this->isSmsEnabled[$index]);
        $this->phones = array_values($this->phones);
        $this->phoneTypes = array_values($this->phoneTypes);
        $this->isSmsEnabled = array_values($this->isSmsEnabled);
    }

    public function removeEmail($index): void
    {
        unset($this->emails[$index], $this->emailTypes[$index], $this->isVerified[$index]);
        $this->emails = array_values($this->emails);
        $this->emailTypes = array_values($this->emailTypes);
        $this->isVerified = array_values($this->isVerified);
    }

    public function removeContact($index): void
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);
    }

    public function removeServiceAddress($index): void
    {
        unset($this->serviceAddresses[$index]);
        $this->serviceAddresses = array_values($this->serviceAddresses);
    }

    public function removeBillingAddress($index): void
    {
        unset($this->billingAddresses[$index]);
        $this->billingAddresses = array_values($this->billingAddresses);
    }

    public function update(): void
    {
        // Check for duplicate before validation (excluding current customer)
        if ($this->customer['company'] !== $this->originalCompanyName) {
            $duplicate = $this->checkDuplicateCustomer();
            if ($duplicate) {
                $this->addError('customer.company', 'A customer with this company name already exists.');
                $this->switchTab('general');

                return;
            }
        }

        $this->isSubmitting = true;

        try {
            $validated = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSubmitting = false;
            $this->updateTabErrors();

            // Switch to first tab with errors
            if (! empty($this->tabErrors['general'])) {
                $this->switchTab('general');
            } elseif (! empty($this->tabErrors['contacts'])) {
                $this->switchTab('contacts');
            } elseif (! empty($this->tabErrors['service'])) {
                $this->switchTab('service');
            } elseif (! empty($this->tabErrors['billing'])) {
                $this->switchTab('billing');
            }

            throw $e;
        }

        try {
            DB::transaction(function () use ($validated) {
                $customer = $this->customerModel;

                // Update customer basic info
                $customer->update([
                    'company' => $validated['customer']['company'],
                    'address' => $validated['customer']['address'],
                    'country' => $validated['customer']['country'],
                    'city' => $validated['customer']['city'],
                    'postcode' => $validated['customer']['postcode'] ?? null,
                    'latitude' => $validated['customer']['latitude'] ?? null,
                    'longitude' => $validated['customer']['longitude'] ?? null,
                    'status' => (bool) ($validated['customer']['status'] ?? false),
                ]);

                // Update phones efficiently (delete all, then bulk insert)
                $customer->customerPhones()->delete();
                $phonesData = [];
                foreach ($this->phones as $index => $phone) {
                    if (! empty($phone)) {
                        $phonesData[] = [
                            'customer_id' => $customer->id,
                            'phone' => $this->normalizePhone($phone),
                            'type' => strtolower($this->phoneTypes[$index] ?? 'primary'),
                            'is_sms_enabled' => (bool) ($this->isSmsEnabled[$index] ?? false),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (! empty($phonesData)) {
                    DB::table('customer_phones')->insert($phonesData);
                }

                // Update emails efficiently
                $customer->customerEmails()->delete();
                $emailsData = [];
                foreach ($this->emails as $index => $email) {
                    if (! empty($email)) {
                        $emailsData[] = [
                            'customer_id' => $customer->id,
                            'email' => $this->normalizeEmail($email),
                            'type' => strtolower($this->emailTypes[$index] ?? 'primary'),
                            'is_verified' => (bool) ($this->isVerified[$index] ?? false),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (! empty($emailsData)) {
                    DB::table('customer_emails')->insert($emailsData);
                }

                // Update contacts efficiently
                $customer->customerContacts()->delete();
                $contactsData = [];
                foreach ($this->contacts as $contact) {
                    if (! empty($contact['name']) || ! empty($contact['email']) || ! empty($contact['phone'])) {
                        $contactsData[] = [
                            'customer_id' => $customer->id,
                            'name' => $contact['name'] ?? null,
                            'email' => ! empty($contact['email']) ? $this->normalizeEmail($contact['email']) : null,
                            'phone' => ! empty($contact['phone']) ? $this->normalizePhone($contact['phone']) : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (! empty($contactsData)) {
                    DB::table('customer_contacts')->insert($contactsData);
                }

                // Update service addresses efficiently
                $customer->customerServiceAddresses()->delete();
                $serviceAddressesData = [];
                foreach ($this->serviceAddresses as $serviceAddress) {
                    if (! empty($serviceAddress['address'])) {
                        $serviceAddressesData[] = [
                            'customer_id' => $customer->id,
                            'address' => $serviceAddress['address'],
                            'country' => $serviceAddress['country'] ?? null,
                            'city' => $serviceAddress['city'] ?? null,
                            'postcode' => $serviceAddress['postcode'] ?? null,
                            'latitude' => $serviceAddress['latitude'] ?? null,
                            'longitude' => $serviceAddress['longitude'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (! empty($serviceAddressesData)) {
                    DB::table('customer_service_addresses')->insert($serviceAddressesData);
                }

                // Update billing addresses efficiently
                $customer->customerBillingAddresses()->delete();
                $billingAddressesData = [];
                foreach ($this->billingAddresses as $billingAddress) {
                    if (! empty($billingAddress['address'])) {
                        $billingAddressesData[] = [
                            'customer_id' => $customer->id,
                            'address' => $billingAddress['address'],
                            'country' => $billingAddress['country'] ?? null,
                            'city' => $billingAddress['city'] ?? null,
                            'postcode' => $billingAddress['postcode'] ?? null,
                            'latitude' => $billingAddress['latitude'] ?? null,
                            'longitude' => $billingAddress['longitude'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (! empty($billingAddressesData)) {
                    DB::table('customer_billing_addresses')->insert($billingAddressesData);
                }
            });

            session()->flash('success', 'Customer "'.$this->customerModel->company.'" successfully updated.');
            $this->redirectRoute('customers.index', navigate: true);
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            session()->flash('error', 'Failed to update customer. Please try again.');
            logger()->error('Customer update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $this->customerModel->id,
                'tenant_id' => $this->tenantId,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.tenancy.customers.edit');
    }
}
