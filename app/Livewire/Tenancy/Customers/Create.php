<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class Create extends Component
{
    // Form data
    public $customer = [];
    public $phones = [''];
    public $phoneTypes = [''];
    public $isSmsEnabled = [];
    public $emails = [''];
    public $emailTypes = [''];
    public $isVerified = [];
    public $contacts = [['name' => '', 'email' => '', 'phone' => '']];
    public $serviceAddresses = [[
        'address' => '', 'country' => '', 'city' => '',
        'postcode' => '', 'latitude' => '', 'longitude' => '',
    ]];
    public $billingAddresses = [[
        'address' => '', 'country' => '', 'city' => '',
        'postcode' => '', 'latitude' => '', 'longitude' => '',
    ]];

    // UI state
    public string $currentTab = 'general';
    public bool $isSubmitting = false;
    public array $tabErrors = [];
    public $status = false;
    public int $tenantId = 0;
    public string $googleMapsApiKey = '';

    // Options
    public $phoneTypeOptions = ['Primary', 'Work', 'Home', 'Emergency'];
    public $emailTypeOptions = ['Primary', 'Work', 'Personal'];

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

    public function mount()
    {
        $this->tenantId = tenant('id');
        $this->customer['status'] = true;
        $this->googleMapsApiKey = config('services.google_maps.api_key') ?? '';
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
        // Remove all non-digit characters except + at the start
        $normalized = preg_replace('/[^\d+]/', '', $phone);
        // Ensure + only at the start
        $normalized = ltrim($normalized, '+');
        if (str_starts_with($phone, '+')) {
            $normalized = '+' . $normalized;
        }
        return $normalized;
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    // Duplicate detection
    private function checkDuplicateCustomer(): ?Customer
    {
        return Customer::where('tenant_id', $this->tenantId)
            ->where('company', $this->customer['company'])
            ->first();
    }

    // Array management methods
    public function addPhone(): void
    {
        $this->phones[] = '';
        $this->phoneTypes[] = '';
        $this->isSmsEnabled[] = false;
    }

    public function addEmail(): void
    {
        $this->emails[] = '';
        $this->emailTypes[] = '';
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

    public function removePhone($index)
    {
        unset($this->phones[$index], $this->phoneTypes[$index], $this->isSmsEnabled[$index]);
        $this->phones = array_values($this->phones);
        $this->phoneTypes = array_values($this->phoneTypes);
        $this->isSmsEnabled = array_values($this->isSmsEnabled);
    }

    public function removeEmail($index)
    {
        unset($this->emails[$index], $this->emailTypes[$index], $this->isVerified[$index]);
        $this->emails = array_values($this->emails);
        $this->emailTypes = array_values($this->emailTypes);
        $this->isVerified = array_values($this->isVerified);
    }

    public function removeContact($index)
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);
    }

    public function removeBillingAddress($index)
    {
        unset($this->billingAddresses[$index]);
        $this->billingAddresses = array_values($this->billingAddresses);
    }

    public function removeServiceAddress($index)
    {
        unset($this->serviceAddresses[$index]);
        $this->serviceAddresses = array_values($this->serviceAddresses);
    }

    public function save(): void
    {
        // Check for duplicate before validation
        $duplicate = $this->checkDuplicateCustomer();
        if ($duplicate) {
            $this->addError('customer.company', 'A customer with this company name already exists.');
            $this->switchTab('general');
            return;
        }

        $this->isSubmitting = true;

        try {
            $validated = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSubmitting = false;
            $this->updateTabErrors();

            // Switch to first tab with errors
            if (!empty($this->tabErrors['general'])) {
                $this->switchTab('general');
            } elseif (!empty($this->tabErrors['contacts'])) {
                $this->switchTab('contacts');
            } elseif (!empty($this->tabErrors['service'])) {
                $this->switchTab('service');
            } elseif (!empty($this->tabErrors['billing'])) {
                $this->switchTab('billing');
            }

            throw $e;
        }

        try {
            $customer = DB::transaction(function () use ($validated) {
                // Create customer with tenant_id
                $customer = Customer::create([
                    'company' => $validated['customer']['company'],
                    'address' => $validated['customer']['address'],
                    'country' => $validated['customer']['country'],
                    'city' => $validated['customer']['city'],
                    'postcode' => $validated['customer']['postcode'] ?? null,
                    'latitude' => $validated['customer']['latitude'] ?? null,
                    'longitude' => $validated['customer']['longitude'] ?? null,
                    'status' => (bool)($validated['customer']['status'] ?? false),
                    'tenant_id' => $this->tenantId,
                ]);

                // Bulk insert phones with normalization
                $phonesData = [];
                foreach ($this->phones as $index => $phone) {
                    if (!empty($phone)) {
                        $phonesData[] = [
                            'customer_id' => $customer->id,
                            'phone' => $this->normalizePhone($phone),
                            'type' => strtolower($this->phoneTypes[$index] ?? 'primary'),
                            'is_sms_enabled' => (bool)($this->isSmsEnabled[$index] ?? false),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($phonesData)) {
                    DB::table('customer_phones')->insert($phonesData);
                }

                // Bulk insert emails with normalization
                $emailsData = [];
                foreach ($this->emails as $index => $email) {
                    if (!empty($email)) {
                        $emailsData[] = [
                            'customer_id' => $customer->id,
                            'email' => $this->normalizeEmail($email),
                            'type' => strtolower($this->emailTypes[$index] ?? 'primary'),
                            'is_verified' => (bool)($this->isVerified[$index] ?? false),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($emailsData)) {
                    DB::table('customer_emails')->insert($emailsData);
                }

                // Bulk insert contacts
                $contactsData = [];
                foreach ($this->contacts as $contact) {
                    if (!empty($contact['name']) || !empty($contact['email']) || !empty($contact['phone'])) {
                        $contactsData[] = [
                            'customer_id' => $customer->id,
                            'name' => $contact['name'] ?? null,
                            'email' => !empty($contact['email']) ? $this->normalizeEmail($contact['email']) : null,
                            'phone' => !empty($contact['phone']) ? $this->normalizePhone($contact['phone']) : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($contactsData)) {
                    DB::table('customer_contacts')->insert($contactsData);
                }

                // Bulk insert service addresses
                $serviceAddressesData = [];
                foreach ($this->serviceAddresses as $serviceAddress) {
                    if (!empty($serviceAddress['address'])) {
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
                if (!empty($serviceAddressesData)) {
                    DB::table('customer_service_addresses')->insert($serviceAddressesData);
                }

                // Bulk insert billing addresses
                $billingAddressesData = [];
                foreach ($this->billingAddresses as $billingAddress) {
                    if (!empty($billingAddress['address'])) {
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
                if (!empty($billingAddressesData)) {
                    DB::table('customer_billing_addresses')->insert($billingAddressesData);
                }

                return $customer;
            });

            session()->flash('success', 'Customer "' . $customer->company . '" successfully created.');

            // Reset form
            $this->resetForm();

            $this->redirectRoute('customers.index', navigate: true);
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            session()->flash('error', 'Failed to create customer. Please try again.');
            logger()->error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => $this->tenantId,
            ]);
        }
    }

    public function resetForm(): void
    {
        $this->customer = [];
        $this->phones = [''];
        $this->phoneTypes = [''];
        $this->isSmsEnabled = [];
        $this->emails = [''];
        $this->emailTypes = [''];
        $this->isVerified = [];
        $this->contacts = [['name' => '', 'email' => '', 'phone' => '']];
        $this->serviceAddresses = [[
            'address' => '', 'country' => '', 'city' => '',
            'postcode' => '', 'latitude' => '', 'longitude' => '',
        ]];
        $this->billingAddresses = [[
            'address' => '', 'country' => '', 'city' => '',
            'postcode' => '', 'latitude' => '', 'longitude' => '',
        ]];
        $this->customer['status'] = true;
        $this->currentTab = 'general';
        $this->isSubmitting = false;
        $this->tabErrors = [];
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.tenancy.customers.create');
    }
}
