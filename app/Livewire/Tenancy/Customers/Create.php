<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Livewire\Component;
use Illuminate\View\View;

class Create extends Component
{
    public $customer = [];

    public $phones = [''];
    public $phoneTypes = [''];
    public $isSmsEnabled = [];
    public $emails = [''];
    public $emailTypes = [''];

    public $isVerified = [];

    public $contacts = [
        ['name' => '', 'email' => ''],
    ];

    public $serviceAddresses = [
        [
            'address' => '',
            'country' => '',
            'city' => '',
            'postcode' => '',
            'latitude' => '',
            'longitude' => '',
        ]
    ];
    public $billingAddresses = [
        [
            'address' => '',
            'country' => '',
            'city' => '',
            'postcode' => '',
            'latitude' => '',
            'longitude' => '',
        ]
    ];

    public $status = false;
    public int $tenantId = 0;
    public $phoneTypeOptions = ['Primary', 'Work', 'Home', 'Emergency'];
    public $emailTypeOptions = ['Primary', 'Work', 'Personal'];

    protected $messages = [
        'customer.company.required'    => 'The company name is required.',
        'customer.company.string'      => 'The company name must be a string.',
        'customer.company.max'         => 'The company name may not be greater than 255 characters.',

        'customer.address.required'    => 'The address field is required.',
        'customer.address.string'      => 'The address must be a string.',
        'customer.address.max'         => 'The address may not be greater than 500 characters.',

        'customer.postcode.string'     => 'The post code must be a string.',
        'customer.postcode.max'        => 'The post code may not be greater than 20 characters.',

        'customer.latitude.numeric'    => 'The latitude must be a valid number.',
        'customer.latitude.between'    => 'The latitude must be between -90 and 90.',

        'customer.longitude.numeric'   => 'The longitude must be a valid number.',
        'customer.longitude.between'   => 'The longitude must be between -180 and 180.',

        'customer.country.required'    => 'The country field is required.',
        'customer.country.string'      => 'The country must be a string.',
        'customer.country.max'         => 'The country may not be greater than 100 characters.',

        'customer.city.required'       => 'The city field is required.',
        'customer.city.string'         => 'The city must be a string.',
        'customer.city.max'            => 'The city may not be greater than 100 characters.',

        'phones.0.required'            => 'At least one phone number is required.',
        'phones.0.string'              => 'The phone number must be a string.',
        'phones.0.max'                 => 'The phone number may not be greater than 20 characters.',
        'phones.0.regex'               => 'The phone number format is invalid.',
        'phones.*.string'              => 'Each phone number must be a string.',
        'phones.*.max'                 => 'Each phone number may not be greater than 20 characters.',
        'phones.*.regex'               => 'Each phone number format is invalid.',

        'emails.0.required'            => 'At least one email address is required.',
        'emails.0.email'               => 'The first email address must be a valid email.',
        'emails.0.max'                 => 'The first email address may not be greater than 255 characters.',
        'emails.*.email'               => 'Each email address must be a valid email.',
        'emails.*.max'                 => 'Each email address may not be greater than 255 characters.',

        'phoneTypes.0.required'        => 'The phone type for the first phone is required.',
        'phoneTypes.array'             => 'Phone types must be an array.',
        'isSmsEnabled.array'           => 'SMS enabled flags must be an array.',
        'emailTypes.0.required'        => 'The email type for the first email is required.',
        'emailTypes.array'             => 'Email types must be an array.',
        'isVerified.array'             => 'Verified flags must be an array.',
    ];

    protected $rules = [
        'customer.company' => 'required|string|max:255',
        'customer.address' => 'required|string|max:255',
        'phones.0' => ['required', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
        'phones.*' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
        'phoneTypes.0' => ['required'],
        'phoneTypes' => 'array',
        'emails.*' => 'required|email',
        // Add other fields as needed
    ];

    public function mount()
    {
        $this->tenantId = tenant('id');
        $this->customer['status'] = true;
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function addPhone(): void
    {
        $this->phones[] = '';
    }

    public function addEmail(): void
    {
        $this->emails[] = '';
    }

    public function addContact(): void
    {
        $this->contacts[] = ['name' => '', 'phone' => '', 'email' => ''];
    }

    public function addServiceAddress(): void
    {
        $this->serviceAddresses[] = [
            'address' => '',
            'country' => '',
            'city' => '',
            'postcode' => '',
            'latitude' => '',
            'longitude' => '',
        ];
    }

    public function addBillingAddress()
    {
        $this->billingAddresses[] = [
            'address' => '',
            'country' => '',
            'city' => '',
            'postcode' => '',
            'latitude' => '',
            'longitude' => '',
        ];
    }

    public function removePhone($index)
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function removeEmail($index)
    {
        unset($this->emails[$index]);
        $this->emails = array_values($this->emails);
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
        $validated = $this->validate([
            'customer.company' => [
                'required',
                'string',
                'max:255'],
            'customer.address' => [
                'required',
                'string',
            ],
            'customer.country' => ['required', 'string', 'max:255'],
            'customer.city' => ['required', 'string', 'max:100'],
            'customer.postcode' => ['string', 'max:20'],
            'customer.latitude' => ['numeric', 'between:-90,90'],
            'customer.longitude' => ['numeric', 'between:-180,180'],
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
            /*            'contacts' => 'required|array|min:1',
                        'contacts.*.name' => 'required|string|max:255',
                        'contacts.*.email' => 'nullable|email|max:255',
                        'contacts.*.phone' => 'nullable|string|max:20',*/

            /*            'serviceAddresses' => 'array',
                        'serviceAddresses.*.address' => 'required|string',
                        'serviceAddresses.*.country' => 'nullable|string|max:255',
                        'serviceAddresses.*.city' => 'nullable|string|max:100',
                        'serviceAddresses.*.postcode' => 'nullable|string|max:20',
                        'serviceAddresses.*.latitude' => 'nullable|numeric|between:-90,90',
                        'serviceAddresses.*.longitude' => 'nullable|numeric|between:-180,180',*/
        ]);

        $customer = Customer::create([
            'company' => $validated['customer']['company'],
            'address' => $validated['customer']['address'],
            'country' => $validated['customer']['country'],
            'city' => $validated['customer']['city'],
            'postcode' => $validated['customer']['postcode'] ?? null,
            'latitude' => $validated['customer']['latitude'] ?? null,
            'longitude' => $validated['customer']['longitude'] ?? null,
            'status' => (bool)($validated['customer']['status'] ?? false),
        ]);

        // Save each phone
        foreach ($this->phones as $phone) {
            if (!empty($phone)) {
                $customer->customerPhones()->create(['phone' => $phone]);
            }
        }

        foreach ($this->phoneTypes as $index => $type) {
            if (isset($this->phones[$index]) && !empty($this->phones[$index])) {
                $customer->customerPhones()->where('phone', $this->phones[$index])->update(['type' => $type]);
            }
        }

        foreach ($this->isSmsEnabled as $index => $isSmsEnabled) {
            if (isset($this->phones[$index]) && !empty($this->phones[$index])) {
                $customer->customerPhones()->where('phone', $this->phones[$index])->update(['is_sms_enabled' => (bool)$isSmsEnabled]);
            }
        }

        // Save each email
        foreach ($this->emails as $email) {
            if (!empty($email)) {
                $customer->customerEmails()->create(['email' => $email]);
            }
        }

        foreach ($this->emailTypes as $index => $type) {
            if (isset($this->emails[$index]) && !empty($this->emails[$index])) {
                $customer->customerEmails()->where('email', $this->emails[$index])->update(['type' => $type]);
            }
        }

        foreach ($this->isVerified as $index => $isVerified) {
            if (isset($this->emails[$index]) && !empty($this->emails[$index])) {
                $customer->customerEmails()->where('email', $this->emails[$index])->update(['is_verified' => (bool)$isVerified]);
            }
        }

        // Save each contact
        foreach ($this->contacts as $contact) {
            if (!empty($contact)) {
                $customer->customerContacts()->create(['name' => $contact['name'], 'phone' => $contact['phone'], 'email' => $contact['email']]);
            }
        }

        foreach ($this->serviceAddresses as $serviceAddress) {
            if (!empty($serviceAddress['address'])) {
                $customer->customerServiceAddresses()->create([
                    'address' => $serviceAddress['address'] ?? '',
                    'country' => $serviceAddress['country'] ?? '',
                    'city' => $serviceAddress['city'] ?? '',
                    'postcode' => $serviceAddress['postcode'] ?? '',
                    'latitude' => $serviceAddress['latitude'] ?? null,
                    'longitude' => $serviceAddress['longitude'] ?? null,
                ]);
            }
        }

        foreach ($this->billingAddresses as $billingAddress) {
            if (!empty($billingAddress['address'])) {
                $customer->customerBillingAddresses()->create([
                    'address' => $billingAddress['address'] ?? '',
                    'country' => $billingAddress['country'] ?? '',
                    'city' => $billingAddress['city'] ?? '',
                    'postcode' => $billingAddress['postcode'] ?? '',
                    'latitude' => $billingAddress['latitude'] ?? null,
                    'longitude' => $billingAddress['longitude'] ?? null,
                ]);
            }
        }

        session()->flash('success', 'Customer successfully created.');

        $this->redirectRoute('customers.create', navigate: true);
    }


    public function render(): View
    {
        return view('livewire.tenancy.customers.create');
    }
}
