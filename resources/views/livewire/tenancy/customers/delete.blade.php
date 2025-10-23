<!-- resources/views/livewire/tenancy/customers/delete.blade.php -->
<div class="bg-white rounded-lg shadow-xl max-w-2xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="ml-4">
            <h2 class="text-xl font-bold text-gray-900">Confirm Customer Deletion</h2>
            <p class="text-sm text-gray-600">This action cannot be undone</p>
        </div>
    </div>

    <!-- Warning Message -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-800">
                    <strong class="font-semibold">Warning:</strong> You are about to permanently delete customer "{{ $customerName }}".
                </p>
            </div>
        </div>
    </div>

    <!-- Customer Details Card -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Customer Information</h3>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="font-medium text-gray-500">Company Name</dt>
                <dd class="mt-1 text-gray-900 font-semibold">{{ $customer->company }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Customer ID</dt>
                <dd class="mt-1 text-gray-900">#{{ $customer->id }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Address</dt>
                <dd class="mt-1 text-gray-900">{{ $customer->address }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $customer->status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $customer->status ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <!-- Related Records Warning -->
    @if($relatedRecordsCount > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold text-red-800">Related Records Will Be Deleted</h4>
                    <p class="mt-1 text-sm text-red-700">
                        This customer has <strong>{{ $relatedRecordsCount }}</strong> related record(s) (phones, emails, contacts, addresses) that will also be permanently deleted.
                    </p>

                    <!-- Related Records Breakdown -->
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        @if($customer->customerPhones->count() > 0)
                            <div class="flex items-center text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $customer->customerPhones->count() }} Phone(s)
                            </div>
                        @endif
                        @if($customer->customerEmails->count() > 0)
                            <div class="flex items-center text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $customer->customerEmails->count() }} Email(s)
                            </div>
                        @endif
                        @if($customer->customerContacts->count() > 0)
                            <div class="flex items-center text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $customer->customerContacts->count() }} Contact(s)
                            </div>
                        @endif
                        @if($customer->customerServiceAddresses->count() > 0)
                            <div class="flex items-center text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $customer->customerServiceAddresses->count() }} Service Address(es)
                            </div>
                        @endif
                        @if($customer->customerBillingAddresses->count() > 0)
                            <div class="flex items-center text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                {{ $customer->customerBillingAddresses->count() }} Billing Address(es)
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
        <a
            href="{{ route('customers.index') }}"
            wire:navigate
            class="px-4 py-2 text-sm bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
        >
            Cancel
        </a>
        <button
            wire:click="deleteCustomer"
            wire:loading.attr="disabled"
            class="px-6 py-2 text-sm bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
        >
            <span wire:loading.remove wire:target="deleteCustomer">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Yes, Delete Customer
            </span>
            <span wire:loading wire:target="deleteCustomer" class="flex items-center">
                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Deleting...
            </span>
        </button>
    </div>
</div>
