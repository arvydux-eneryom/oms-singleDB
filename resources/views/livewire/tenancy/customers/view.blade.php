<!-- resources/views/livewire/tenancy/customers/view.blade.php -->
<div x-data="{ tab: $wire.entangle('currentTab') }">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $customer->company }}</h1>
            <p class="text-sm text-gray-600 mt-1">Customer ID: #{{ $customer->id }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Quick Actions -->
            <div class="relative" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    class="inline-flex items-center px-4 py-2 text-sm bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                </button>
                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                    style="display: none;"
                >
                    <button
                        wire:click="toggleStatus"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        @if($customer->status)
                            <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Deactivate Customer
                        @else
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Activate Customer
                        @endif
                    </button>
                    <button
                        wire:click="openSmsModal"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Send SMS
                    </button>
                    <button
                        wire:click="openEmailModal"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Send Email
                    </button>
                    <div class="border-t border-gray-200"></div>
                    <a
                        href="{{ route('customers.edit', $customer->id) }}"
                        wire:navigate
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Customer
                    </a>
                    <button
                        wire:click="confirmDelete"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Customer
                    </button>
                </div>
            </div>

            <a
                href="{{ route('customers.index') }}"
                wire:navigate
                class="inline-flex items-center px-4 py-2 text-sm bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full
            {{ $customer->status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ $customer->status ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            @foreach(['overview' => 'Overview', 'activity' => 'Activity', 'contacts' => 'Contacts', 'addresses' => 'Addresses', 'stats' => 'Statistics'] as $key => $label)
                <button
                    @click="tab = '{{ $key }}'"
                    :class="{
                        'border-blue-500 text-blue-600': tab === '{{ $key }}',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== '{{ $key }}'
                    }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    type="button"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Overview Tab -->
    <div x-show="tab === 'overview'" x-cloak>
        <!-- Customer Details Card -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Customer Details</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $customer->company }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $customer->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Post Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $customer->postcode }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">City</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $customer->city }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Country</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $customer->country }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Coordinates</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $customer->latitude }}, {{ $customer->longitude }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Phones & Emails Card -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Contact Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Phones -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Phone Numbers
                        </h3>
                        @forelse($customer->customerPhones as $phone)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-600 uppercase">{{ $phone->type }}</span>
                                    @if($phone->is_sms_enabled)
                                        <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">SMS</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-900 font-medium">{{ $phone->phone }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No phone numbers</p>
                        @endforelse
                    </div>

                    <!-- Emails -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email Addresses
                        </h3>
                        @forelse($customer->customerEmails as $email)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-600 uppercase">{{ $email->type }}</span>
                                    @if($email->is_verified)
                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">Verified</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-900 font-medium">{{ $email->email }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No email addresses</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Tab -->
    <div x-show="tab === 'activity'" x-cloak>
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Activity Timeline</h2>
            </div>
            <div class="p-6">
                @if(!empty($activityLog))
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($activityLog as $index => $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-900">{{ $activity['description'] }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ $activity['timestamp'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No activity recorded yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Contacts Tab -->
    <div x-show="tab === 'contacts'" x-cloak>
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Customer Contacts</h2>
            </div>
            <div class="p-6">
                @forelse($customer->customerContacts as $contact)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">{{ $contact->name }}</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contact->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contact->email }}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No contacts found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Addresses Tab -->
    <div x-show="tab === 'addresses'" x-cloak>
        <!-- Service Addresses -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Service Addresses</h2>
            </div>
            <div class="p-6">
                @forelse($customer->customerServiceAddresses as $address)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-base font-semibold text-gray-900 mb-3">{{ $address->address }}</p>
                        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">City</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->city }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Post Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->postcode }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Country</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->country }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Latitude</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->latitude }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Longitude</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->longitude }}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-500 py-8">No service addresses</p>
                @endforelse
            </div>
        </div>

        <!-- Billing Addresses -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Billing Addresses</h2>
            </div>
            <div class="p-6">
                @forelse($customer->customerBillingAddresses as $address)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-base font-semibold text-gray-900 mb-3">{{ $address->address }}</p>
                        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">City</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->city }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Post Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->postcode }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Country</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->country }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Latitude</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->latitude }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Longitude</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address->longitude }}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-500 py-8">No billing addresses</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Statistics Tab -->
    <div x-show="tab === 'stats'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Phone Numbers</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['phones_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Email Addresses</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['emails_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Contacts</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['contacts_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Service Addresses</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['service_addresses_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Billing Addresses</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['billing_addresses_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">SMS-Enabled Phones</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['sms_enabled_phones'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-teal-100 rounded-full">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Modal -->
    @if($showSmsModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Send SMS</h3>
                <button wire:click="$set('showSmsModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Phone Number</label>
                <select wire:model="selectedPhone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Choose a phone number...</option>
                    @foreach($customer->customerPhones->where('is_sms_enabled', true) as $phone)
                        <option value="{{ $phone->phone }}">{{ $phone->phone }} ({{ $phone->type }})</option>
                    @endforeach
                </select>
                @error('selectedPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea
                    wire:model="smsMessage"
                    rows="4"
                    maxlength="1600"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter your message..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">{{ strlen($smsMessage ?? '') }}/1600 characters</p>
                @error('smsMessage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button
                    wire:click="$set('showSmsModal', false)"
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                    Cancel
                </button>
                <button
                    wire:click="sendSms"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Send SMS
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Email Modal -->
    @if($showEmailModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Send Email</h3>
                <button wire:click="$set('showEmailModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Email Address</label>
                <select wire:model="selectedEmail" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Choose an email address...</option>
                    @foreach($customer->customerEmails as $email)
                        <option value="{{ $email->email }}">{{ $email->email }} ({{ $email->type }})</option>
                    @endforeach
                </select>
                @error('selectedEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <input
                    type="text"
                    wire:model="emailSubject"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Email subject..."
                />
                @error('emailSubject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea
                    wire:model="emailBody"
                    rows="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter your message..."
                ></textarea>
                @error('emailBody') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button
                    wire:click="$set('showEmailModal', false)"
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                    Cancel
                </button>
                <button
                    wire:click="sendEmail"
                    class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                >
                    Send Email
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-red-600">Confirm Deletion</h3>
                <button wire:click="$set('showDeleteModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-gray-700 mb-6">
                Are you sure you want to delete customer "{{ $customer->company }}"? This action cannot be undone.
            </p>

            <div class="flex justify-end gap-3">
                <button
                    wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                    Cancel
                </button>
                <button
                    wire:click="deleteCustomer"
                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700"
                >
                    Delete Customer
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700 font-medium">Processing...</span>
            </div>
        </div>
    </div>
</div>
