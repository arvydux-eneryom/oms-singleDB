<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Customer::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer.company' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'company')
                    ->where('tenant_id', tenant('id'))
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
            'phones.0' => ['required', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'phones.*' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'phoneTypes' => ['array'],
            'phoneTypes.0' => ['required', 'string'],
            'phoneTypes.*' => ['nullable', 'string'],
            'isSmsEnabled' => ['array'],
            'isSmsEnabled.*' => ['boolean'],

            // Email validation
            'emails' => ['array'],
            'emails.0' => ['required', 'email', 'max:255'],
            'emails.*' => ['nullable', 'email', 'max:255'],
            'emailTypes' => ['array'],
            'emailTypes.0' => ['required', 'string'],
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
            'phones.0.required' => 'At least one phone number is required.',
            'phones.*.regex' => 'Please enter a valid phone number.',
            'phoneTypes.0.required' => 'Please select a type for the first phone.',
            'emails.0.required' => 'At least one email address is required.',
            'emails.0.email' => 'Please enter a valid email address.',
            'emailTypes.0.required' => 'Please select a type for the first email.',
        ];
    }
}
