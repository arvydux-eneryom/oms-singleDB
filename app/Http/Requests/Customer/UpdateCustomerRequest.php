<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $this->user()->can('update', $customer);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'customer.company' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'company')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($customerId)
                    ->whereNull('deleted_at'),
            ],
            'customer.address' => ['required', 'string'],
            'customer.country' => ['required', 'string', 'max:255'],
            'customer.city' => ['required', 'string', 'max:100'],
            'customer.postcode' => ['nullable', 'string', 'max:20'],
            'customer.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'customer.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'customer.status' => ['boolean'],

            // Phone validation
            'phones' => ['array'],
            'phones.*' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'phoneTypes' => ['array'],
            'phoneTypes.*' => ['nullable', 'string'],
            'isSmsEnabled' => ['array'],
            'isSmsEnabled.*' => ['boolean'],

            // Email validation
            'emails' => ['array'],
            'emails.*' => ['nullable', 'email', 'max:255'],
            'emailTypes' => ['array'],
            'emailTypes.*' => ['nullable', 'string'],
            'isVerified' => ['array'],
            'isVerified.*' => ['boolean'],

            // Contacts validation
            'contacts' => ['array'],
            'contacts.*.name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:20'],

            // Service addresses validation
            'serviceAddresses' => ['array'],
            'serviceAddresses.*.address' => ['nullable', 'string'],
            'serviceAddresses.*.country' => ['nullable', 'string', 'max:255'],
            'serviceAddresses.*.city' => ['nullable', 'string', 'max:100'],
            'serviceAddresses.*.postcode' => ['nullable', 'string', 'max:20'],
            'serviceAddresses.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'serviceAddresses.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Billing addresses validation
            'billingAddresses' => ['array'],
            'billingAddresses.*.address' => ['nullable', 'string'],
            'billingAddresses.*.country' => ['nullable', 'string', 'max:255'],
            'billingAddresses.*.city' => ['nullable', 'string', 'max:100'],
            'billingAddresses.*.postcode' => ['nullable', 'string', 'max:20'],
            'billingAddresses.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'billingAddresses.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer.company.required' => 'Please enter the company name.',
            'customer.company.unique' => 'A customer with this company name already exists.',
            'customer.address.required' => 'Please enter the primary address.',
            'customer.country.required' => 'Please enter the country.',
            'customer.city.required' => 'Please enter the city.',
            'phones.*.regex' => 'Please enter a valid phone number.',
            'emails.*.email' => 'Please enter a valid email address.',
        ];
    }
}
