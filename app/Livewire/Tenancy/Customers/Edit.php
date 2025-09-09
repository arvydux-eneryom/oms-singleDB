<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\View\View;

class Edit extends Component
{
    public Customer $customerModel;
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
    public $status = false;

    public int $tenantId = 0;
    public $phoneTypeOptions = ['Primary', 'Work', 'Home', 'Emergency'];
    public $emailTypeOptions = ['Primary', 'Work', 'Personal'];

    public function mount(Customer $customer)
    {
        $this->customerModel = $customer;
        $this->tenantId = tenant('id');

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

        $this->phones = $customer->customerPhones->pluck('phone')->toArray();
        $this->phoneTypes = $customer->customerPhones->pluck('type')->map(function ($type) {
            return $type ? trim(strtolower($type)) : strtolower($this->phoneTypeOptions[0]);
        })->toArray();

        $this->phoneTypeOptions = collect($this->phoneTypeOptions)->map(fn($type) => trim(strtolower($type)))->toArray();

        $this->isSmsEnabled = $customer->customerPhones->pluck('is_sms_enabled')->map(function ($val) {
            return (bool)$val;
        })->toArray();
        $this->emails = $customer->customerEmails->pluck('email')->toArray();

        $this->emailTypeOptions = collect($this->emailTypeOptions)
            ->map(fn($type) => trim(strtolower($type)))
            ->toArray();

        $this->emailTypes = $customer->customerEmails->pluck('type')
            ->map(fn($type) => $type ? trim(strtolower($type)) : $this->emailTypeOptions[0])
            ->toArray();

        $this->isVerified = $customer->customerEmails->pluck('is_verified')->map(function ($val) {
            return (bool)$val;
        })->toArray();

        $this->contacts = ($customer->customerContacts ?? collect())->map(function ($contact) {
            return [
                'name' => $contact->name ?? '',
                'phone' => $contact->phone ?? '',
                'email' => $contact->email ?? '',
            ];
        })->toArray();

        $this->serviceAddresses = ($customer->customerServiceAddresses ?? collect())->map(function ($address) {
            return [
                'address' => $address->address ?? '',
                'country' => $address->country ?? '',
                'city' => $address->city ?? '',
                'postcode' => $address->postcode ?? '',
                'latitude' => $address->latitude ?? '',
                'longitude' => $address->longitude ?? '',
            ];
        })->toArray();

        $this->billingAddresses = ($customer->customerBillingAddresses ?? collect())->map(function ($address) {
            return [
                'address' => $address->address ?? '',
                'country' => $address->country ?? '',
                'city' => $address->city ?? '',
                'postcode' => $address->postcode ?? '',
                'latitude' => $address->latitude ?? '',
                'longitude' => $address->longitude ?? '',
            ];
        })->toArray();

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

    public function addBillingAddress(): void
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

    public function removePhone($index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function removeEmail($index): void
    {
        unset($this->emails[$index]);
        $this->emails = array_values($this->emails);
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
        $validated = $this->validate([
            'customer.company' => ['required', 'string', 'max:255'],
            'customer.address' => ['required', 'string'],
            'customer.country' => ['required', 'string', 'max:255'],
            'customer.city' => ['required', 'string', 'max:100'],
            'customer.postcode' => ['string', 'max:20'],
            'customer.latitude' => ['numeric', 'between:-90,90'],
            'customer.longitude' => ['numeric', 'between:-180,180'],
            'customer.status' => ['boolean'],
            'phones' => 'required|array|min:1',
            'phones.*' => 'nullable|string|max:20',
            'phoneTypes' => 'array',
            'isSmsEnabled' => 'array',
            'emails' => 'required|array|min:1',
            'emails.*' => 'nullable|email|max:255',
            'emailTypes' => 'array',
            'isVerified' => 'array',
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
        ]);

        $customer = $this->customerModel;
        $customer->update([
            'company' => $validated['customer']['company'],
            'address' => $validated['customer']['address'],
            'country' => $validated['customer']['country'],
            'city' => $validated['customer']['city'],
            'postcode' => $validated['customer']['postcode'] ?? null,
            'latitude' => $validated['customer']['latitude'] ?? null,
            'longitude' => $validated['customer']['longitude'] ?? null,
            'status' => $validated['customer']['status'] ?? false,
        ]);

        // Phones
        $customer->customerPhones()->delete();
        foreach ($this->phones as $index => $phone) {
            if (!empty($phone)) {
                $phoneModel = $customer->customerPhones()->create([
                    'phone' => $phone,
                    'type' => $this->phoneTypes[$index] ?? null,
                    'is_sms_enabled' => (bool)($this->isSmsEnabled[$index] ?? false),
                ]);
            }
        }

        // Emails
        $customer->customerEmails()->delete();
        foreach ($this->emails as $index => $email) {
            if (!empty($email)) {
                $customer->customerEmails()->create([
                    'email' => $email,
                    'type' => $this->emailTypes[$index] ?? null,
                    'is_verified' => (bool)($this->isVerified[$index] ?? false),
                ]);
            }
        }

        // Contacts
        $customer->customerContacts()->delete();
        foreach ($this->contacts as $contact) {
            if (!empty($contact['name'])) {
                $customer->customerContacts()->create([
                    'name' => $contact['name'],
                    'phone' => $contact['phone'] ?? null,
                    'email' => $contact['email'] ?? null,
                ]);
            }
        }

        // Service Addresses
        $customer->customerServiceAddresses()->delete();
        foreach ($this->serviceAddresses as $serviceAddress) {
            if (!empty($serviceAddress['address'])) {
                $customer->customerServiceAddresses()->create([
                    'address'   => $serviceAddress['address'] ?? '',
                    'country'   => $serviceAddress['country'] ?? '',
                    'city'      => $serviceAddress['city'] ?? '',
                    'postcode'  => $serviceAddress['postcode'] ?? '',
                    'latitude'  => $serviceAddress['latitude'] ?? null,
                    'longitude' => $serviceAddress['longitude'] ?? null,
                ]);
            }
        }

        // Billing Addresses
        $customer->customerBillingAddresses()->delete();
        foreach ($this->billingAddresses as $billingAddress) {
            if (!empty($billingAddress['address'])) {
                $customer->customerBillingAddresses()->create([
                    'address'   => $billingAddress['address'] ?? '',
                    'country'   => $billingAddress['country'] ?? '',
                    'city'      => $billingAddress['city'] ?? '',
                    'postcode'  => $billingAddress['postcode'] ?? '',
                    'latitude'  => $billingAddress['latitude'] ?? null,
                    'longitude' => $billingAddress['longitude'] ?? null,
                ]);
            }
        }

        session()->flash('success', 'Customer successfully updated.');
        $this->redirectRoute('customers.edit', ['customer' => $customer->id], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tenancy.customers.edit');
    }
}
