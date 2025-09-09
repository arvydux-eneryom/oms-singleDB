<div x-data="{ tab: 'general' }">
    <!-- Tab Buttons (copy from edit/create) -->
    <div class="flex gap-2 mb-6">
        <template x-for="(label, key) in {
            general: 'General',
            contacts: 'Contacts',
            service: 'Service Addresses',
            billing: 'Billing Addresses'
        }" :key="key">
            <button
                :class="tab === key
        ? 'bg-gray-800 text-white shadow-lg scale-105'
        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100 hover:text-gray-900'"
                class="px-5 py-2 rounded-full font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
                @click="tab = key"
                type="button"
                x-text="label"
            ></button>
        </template>
    </div>
    <section class="max-w-4xl ">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Customer "{{$customer->company}}"</h1>
            <a href="{{ route('customers.edit', $customer->id) }}"
               class="inline-flex items-center px-2.5 py-1 text-sm bg-gray-900 text-white font-semibold rounded shadow hover:bg-gray-950 transition">
                <span class="text-lg mr-1">+</span> Edit Customer
            </a>
        </div>
   </section>
    <!-- General Tab -->
    <div x-show="tab === 'general'">
        <section class="max-w-4xl bg-white/90 border border-gray-200 rounded-xl shadow-md overflow-hidden mb-6">
            <!-- Header -->
            <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-xl shadow">🏢</span>
                <flux:heading size="md" class="mb-0 text-gray-800 font-bold tracking-tight text-base">Customer Details</flux:heading>
                <div class="flex items-center gap-2 ml-auto">
                    <input type="checkbox" disabled {{ $customer->status ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-green-600 bg-green-600 border-green-600">
                    <span class="text-xs font-medium text-gray-700">
            {{ $customer->status ? 'Active' : 'Inactive' }}
        </span>
                </div>
            </div>
            <!-- Content -->
            <div class="p-4 grid gap-4 text-sm">
                <!-- Company & Address -->
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="flex items-start gap-2 bg-gray-50 p-3 rounded-lg shadow-sm">
                        <span class="text-gray-500 text-base">🏬</span>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Company Name</p>
                            <p class="text-gray-900 font-semibold text-base">{{ $customer->company }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-gray-50 p-3 rounded-lg shadow-sm">
                        <span class="text-gray-500 text-base">📍</span>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Address</p>
                            <p class="text-gray-900 font-semibold text-base">{{ $customer->address }}</p>
                        </div>
                    </div>
                </div>
                <!-- Postcode, Lat, Lng -->
                <div class="grid sm:grid-cols-3 gap-3">
                    <div class="bg-white border border-gray-200 p-3 rounded-lg shadow-sm text-center">
                        <p class="text-xs text-gray-500">Post Code</p>
                        <p class="text-gray-900 font-semibold">{{ $customer->postcode }}</p>
                    </div>
                    <div class="bg-white border border-gray-200 p-3 rounded-lg shadow-sm text-center">
                        <p class="text-xs text-gray-500">Latitude</p>
                        <p class="text-gray-900 font-semibold">{{ $customer->latitude }}</p>
                    </div>
                    <div class="bg-white border border-gray-200 p-3 rounded-lg shadow-sm text-center">
                        <p class="text-xs text-gray-500">Longitude</p>
                        <p class="text-gray-900 font-semibold">{{ $customer->longitude }}</p>
                    </div>
                </div>
                <!-- Country & City -->
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="flex items-start gap-2 bg-gray-50 p-3 rounded-lg shadow-sm">
                        <span class="text-gray-600 text-base">🌍</span>
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Country</p>
                            <p class="text-gray-800 font-semibold text-base">{{ $customer->country }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-gray-50 p-3 rounded-lg shadow-sm">
                        <span class="text-gray-600 text-base">🏙️</span>
                        <div>
                            <p class="text-xs text-gray-600 font-medium">City</p>
                            <p class="text-gray-800 font-semibold text-base">{{ $customer->city }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="max-w-4xl bg-white/90 border border-gray-200 rounded-xl shadow-md overflow-hidden mt-6 mb-6">
            <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-xl shadow">📞</span>
                <flux:heading size="md" class="mb-0 text-gray-800 font-bold tracking-tight text-base">Phones & Emails</flux:heading>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <!-- Phones -->
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-200 text-gray-700 text-base">📞</span>
                        <flux:heading size="sm" class="text-gray-800 text-sm">Customer phones</flux:heading>
                    </div>
                    @foreach ($customer->customerPhones as $phone)
                        <div class="bg-gray-50 border border-gray-100 rounded-lg shadow-sm p-3 flex flex-col gap-2 hover:shadow-md transition">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-semibold">{{ ucfirst($phone->type) }}</span>
                                @if($phone->is_sms_enabled)
                                    <span class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-semibold">SMS Enabled</span>
                                @endif
                            </div>
                            <div class="text-base font-medium text-gray-900">{{ $phone->phone }}</div>
                        </div>
                    @endforeach
                </div>
                <!-- Emails -->
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-200 text-gray-700 text-base">📧</span>
                        <flux:heading size="sm" class="text-gray-800 text-sm">Customer emails</flux:heading>
                    </div>
                    @foreach ($customer->customerEmails as $email)
                        <div class="bg-gray-50 border border-gray-100 rounded-lg shadow-sm p-3 flex flex-col gap-2 hover:shadow-md transition">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-semibold">{{ ucfirst($email->type) }}</span>
                                @if($email->is_verified)
                                    <span class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-semibold">Verified</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-500 text-xs font-semibold">Not Verified</span>
                                @endif
                            </div>
                            <div class="text-base font-medium text-gray-900">{{ $email->email }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <!-- Contacts Tab -->
    <div x-show="tab === 'contacts'">
        <section class="max-w-4xl bg-white/90 border border-gray-200 rounded-xl shadow-md overflow-hidden mb-6">
            <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-xl shadow">👤</span>
                <flux:heading size="md" class="mb-0 text-gray-800 font-bold tracking-tight text-base">Contacts</flux:heading>
            </div>
            <div class="p-4 grid gap-4 text-sm">
                @foreach ($customer->customerContacts as $contact)
                    <div class="bg-gray-50 border border-gray-100 rounded-lg shadow-sm p-3 flex flex-col gap-2 hover:shadow-md transition">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-base">📇</span>
                            <span class="text-base font-semibold text-gray-900">{{ $contact->name }}</span>
                        </div>
                        <div class="flex gap-4 text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="font-medium">{{ $contact->phone }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="font-medium">{{ $contact->email }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Service Addresses Tab -->
    <div x-show="tab === 'service'">
        <section class="max-w-4xl bg-white/90 border border-gray-200 rounded-xl shadow-md overflow-hidden mb-6">
            <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-xl shadow">🏠</span>
                <flux:heading size="md" class="mb-0 text-gray-800 font-bold tracking-tight text-base">Service Addresses</flux:heading>
            </div>
            <div class="p-4 grid gap-4 text-sm">
                @foreach ($customer->customerServiceAddresses as $address)
                    <div class="bg-gray-50 border border-gray-100 rounded-lg shadow-sm p-3 flex flex-col gap-2 hover:shadow-md transition">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-base">📍</span>
                            <span class="text-base font-semibold text-gray-900">{{ $address->address }}</span>
                        </div>
                        <div class="grid sm:grid-cols-3 gap-3 text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Country</p>
                                <p class="font-medium">{{ $address->country }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">City</p>
                                <p class="font-medium">{{ $address->city }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Post Code</p>
                                <p class="font-medium">{{ $address->postcode }}</p>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3 text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Latitude</p>
                                <p class="font-medium">{{ $address->latitude }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Longitude</p>
                                <p class="font-medium">{{ $address->longitude }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Billing Addresses Tab -->
    <div x-show="tab === 'billing'">
        <section class="max-w-4xl bg-white/90 border border-gray-200 rounded-xl shadow-md overflow-hidden mb-6">
            <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-xl shadow">💳</span>
                <flux:heading size="md" class="mb-0 text-gray-800 font-bold tracking-tight text-base">Billing Addresses</flux:heading>
            </div>
            <div class="p-4 grid gap-4 text-sm">
                @foreach ($customer->customerBillingAddresses as $address)
                    <div class="bg-gray-50 border border-gray-100 rounded-lg shadow-sm p-3 flex flex-col gap-2 hover:shadow-md transition">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-base">📍</span>
                            <span class="text-base font-semibold text-gray-900">{{ $address->address }}</span>
                        </div>
                        <div class="grid sm:grid-cols-3 gap-3 text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Country</p>
                                <p class="font-medium">{{ $address->country }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">City</p>
                                <p class="font-medium">{{ $address->city }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Post Code</p>
                                <p class="font-medium">{{ $address->postcode }}</p>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3 text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Latitude</p>
                                <p class="font-medium">{{ $address->latitude }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Longitude</p>
                                <p class="font-medium">{{ $address->longitude }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
