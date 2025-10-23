<div x-data="{ tab: $wire.entangle('currentTab'), formDirty: false }" x-init="
    window.addEventListener('beforeunload', (e) => {
        if (formDirty && !@js($isSubmitting)) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    $watch('tab', value => $wire.switchTab(value));
" @input.window="formDirty = true">

    <!-- Tab Navigation with Error Indicators -->
    <div class="flex gap-2 mb-6">
        @php
            $tabs = [
                'general' => 'General Info',
                'contacts' => 'Contacts',
                'service' => 'Service Addresses',
                'billing' => 'Billing Addresses'
            ];
        @endphp

        @foreach($tabs as $key => $label)
            <button
                type="button"
                @click="tab = '{{ $key }}'"
                :class="{
                    'bg-gray-900 text-white shadow-lg scale-105': tab === '{{ $key }}',
                    'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50': tab !== '{{ $key }}',
                    'border-red-500 bg-red-50': {{ json_encode($tabErrors[$key] ?? false) }}
                }"
                class="relative px-5 py-2 rounded-full font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
                {{ $label }}
                @if($tabErrors[$key] ?? false)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                @endif
            </button>
        @endforeach
    </div>

    <!-- Error Summary -->
    @if ($errors->any())
        <div class="mb-6 rounded-lg border-2 border-red-200 bg-red-50 px-6 py-4 text-red-800 shadow-md animate-shake">
            <div class="flex items-center gap-3 mb-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z"/>
                </svg>
                <span class="font-bold text-lg">Please fix {{ $errors->count() }} {{ Str::plural('error', $errors->count()) }}:</span>
            </div>
            <ul class="list-disc list-inside space-y-2 pl-2">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success Message -->
    @if (session()->has('success'))
        <div class="mb-6 rounded-lg border-2 border-green-200 bg-green-50 px-6 py-4 text-green-800 shadow-md">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="flex flex-col gap-6">
        <!-- Loading Overlay -->
        <div wire:loading.flex wire:target="save" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
            <div class="bg-white rounded-lg px-8 py-6 shadow-2xl">
                <div class="flex items-center gap-4">
                    <svg class="animate-spin h-8 w-8 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg font-semibold text-gray-900">Creating customer...</span>
                </div>
            </div>
        </div>

        <!-- General Tab -->
        <div x-show="tab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <section class="max-w-4xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <flux:heading size="xl">Customer Information</flux:heading>
                </div>

                <div class="flex gap-6">
                    <div class="flex-1 flex flex-col gap-6">
                        <div class="grid grid-cols-6 gap-x-6 items-center">
                            <div class="col-span-5">
                                <flux:input
                                    wire:model.live.debounce.500ms="customer.company"
                                    :label="__('Company Name')"
                                    id="company"
                                    badge="required"
                                    placeholder="Enter company name"
                                />
                            </div>
                            <div>
                                <flux:field variant="inline" class="flex items-center mb-0">
                                    <label class="flex items-center cursor-pointer gap-2">
                                        <flux:checkbox wire:model="customer.status" class="mt-0.5" />
                                        <span class="text-gray-700 font-medium">Active</span>
                                    </label>
                                    <flux:error name="status" class="ml-3" />
                                </flux:field>
                            </div>
                        </div>

                        <flux:input
                            wire:model="customer.address"
                            :label="__('Primary Address')"
                            id="autocomplete_address"
                            badge="required"
                            placeholder="Start typing address..."
                        />

                        <div class="grid grid-cols-3 gap-4">
                            <flux:input
                                wire:model="customer.postcode"
                                :label="__('Post Code')"
                                id="postcode_address"
                                placeholder="Postal code"
                            />
                            <flux:input
                                wire:model="customer.latitude"
                                :label="__('Latitude')"
                                id="latitude_address"
                                placeholder="Auto-filled"
                                readonly
                            />
                            <flux:input
                                wire:model="customer.longitude"
                                :label="__('Longitude')"
                                id="longitude_address"
                                placeholder="Auto-filled"
                                readonly
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <flux:input
                                wire:model="customer.country"
                                :label="__('Country')"
                                id="country_address"
                                x-model="geo.country"
                                badge="required"
                                placeholder="Country"
                            />
                            <flux:input
                                wire:model="customer.city"
                                :label="__('City')"
                                id="city_address"
                                x-model="geo.city"
                                badge="required"
                                placeholder="City"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Phones & Emails Section -->
            <section class="max-w-4xl mt-10">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Phones -->
                    <div class="flex-1 flex flex-col gap-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <flux:heading size="lg">Phone Numbers</flux:heading>
                        </div>
                        @foreach ($phones as $index => $phone)
                            <div class="relative bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span class="flex items-center justify-center w-6 h-6 bg-gray-900 text-white text-xs rounded-full">{{ $index + 1 }}</span>
                                        {{ __('Phone') }}
                                    </h4>
                                    @if(count($phones) > 1)
                                        <button
                                            type="button"
                                            wire:click="removePhone({{ $index }})"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors"
                                            wire:loading.attr="disabled"
                                            wire:target="removePhone({{ $index }})"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            {{ __('Remove') }}
                                        </button>
                                    @endif
                                </div>

                                <flux:input
                                    wire:model.live.debounce.500ms="phones.{{ $index }}"
                                    :label="__('Phone number')"
                                    id="phone-{{ $index }}"
                                    :badge="$index === 0 ? 'required' : null"
                                    placeholder="+1 (555) 123-4567"
                                />

                                <div class="flex items-center gap-6 mt-4">
                                    <div class="flex-2">
                                        <flux:select
                                            size="sm"
                                            wire:model.live.debounce.500ms="phoneTypes.{{ $index }}"
                                            :label="__('Type')"
                                            placeholder="Select type"
                                            :badge="$index === 0 ? 'required' : null"
                                        >
                                            @foreach($phoneTypeOptions as $type)
                                                <flux:select.option>{{ $type }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                    <div class="flex-1">
                                        <flux:field variant="inline">
                                            <flux:checkbox id="sms-enabled-{{ $index }}" wire:model="isSmsEnabled.{{ $index }}" />
                                            <flux:label for="sms-enabled-{{ $index }}">SMS Enabled</flux:label>
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <button
                                type="button"
                                wire:click="addPhone"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
                                wire:loading.attr="disabled"
                                wire:target="addPhone"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('Add another phone') }}
                            </button>
                        </div>
                    </div>

                    <!-- Emails -->
                    <div class="flex-1 flex flex-col gap-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <flux:heading size="lg">Email Addresses</flux:heading>
                        </div>
                        @foreach ($emails as $index => $email)
                            <div class="relative bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span class="flex items-center justify-center w-6 h-6 bg-gray-900 text-white text-xs rounded-full">{{ $index + 1 }}</span>
                                        {{ __('Email') }}
                                    </h4>
                                    @if(count($emails) > 1)
                                        <button
                                            type="button"
                                            wire:click="removeEmail({{ $index }})"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors"
                                            wire:loading.attr="disabled"
                                            wire:target="removeEmail({{ $index }})"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            {{ __('Remove') }}
                                        </button>
                                    @endif
                                </div>
                                <flux:input
                                    wire:model.live.debounce.500ms="emails.{{ $index }}"
                                    :label="__('Email address')"
                                    id="email-{{ $index }}"
                                    :badge="$index === 0 ? 'required' : null"
                                    placeholder="email@example.com"
                                />
                                <div class="flex items-center gap-6 mt-4">
                                    <div class="flex-3">
                                        <flux:select
                                            size="sm"
                                            wire:model.live.debounce.500ms="emailTypes.{{ $index }}"
                                            :label="__('Type')"
                                            placeholder="Select type"
                                            :badge="$index === 0 ? 'required' : null"
                                        >
                                            @foreach($emailTypeOptions as $type)
                                                <flux:select.option>{{ $type }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                    <div class="flex-1">
                                        <flux:field variant="inline">
                                            <flux:checkbox id="verified-{{ $index }}" wire:model="isVerified.{{ $index }}" />
                                            <flux:label for="verified-{{ $index }}">Verified</flux:label>
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <button
                                type="button"
                                wire:click="addEmail"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
                                wire:loading.attr="disabled"
                                wire:target="addEmail"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('Add another email') }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Contacts Tab -->
        <div x-show="tab === 'contacts'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <section class="max-w-4xl space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <flux:heading size="xl">Contact Persons</flux:heading>
                </div>
                <p class="text-sm text-gray-600 mb-4">Add key contact persons for this customer.</p>

                @foreach ($contacts as $index => $contact)
                    <div class="relative bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 bg-gray-900 text-white text-xs rounded-full">{{ $index + 1 }}</span>
                                {{ __('Contact Person') }}
                            </h4>
                            @if(count($contacts) > 1)
                                <button
                                    type="button"
                                    wire:click="removeContact({{ $index }})"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('Remove') }}
                                </button>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <flux:input
                                wire:model="contacts.{{ $index }}.name"
                                :label="__('Full name')"
                                id="contact-name-{{ $index }}"
                                placeholder="John Doe"
                            />
                            <flux:input
                                wire:model="contacts.{{ $index }}.phone"
                                :label="__('Phone number')"
                                id="contact-phone-{{ $index }}"
                                placeholder="+1 (555) 123-4567"
                            />
                            <flux:input
                                wire:model="contacts.{{ $index }}.email"
                                :label="__('Email address')"
                                id="contact-email-{{ $index }}"
                                placeholder="john@example.com"
                            />
                        </div>
                    </div>
                @endforeach
                <div>
                    <button
                        type="button"
                        wire:click="addContact"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('Add contact person') }}
                    </button>
                </div>
            </section>
        </div>

        <!-- Service Addresses Tab -->
        <div x-show="tab === 'service'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <section class="max-w-4xl space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <flux:heading size="xl">Service Locations</flux:heading>
                </div>
                <p class="text-sm text-gray-600 mb-4">Where services will be provided to the customer.</p>

                @foreach ($serviceAddresses as $index => $serviceAddress)
                    <div class="relative bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 bg-gray-900 text-white text-xs rounded-full">{{ $index + 1 }}</span>
                                {{ __('Service Address') }}
                            </h4>
                            @if(count($serviceAddresses) > 1)
                                <button
                                    type="button"
                                    wire:click="removeServiceAddress({{ $index }})"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('Remove') }}
                                </button>
                            @endif
                        </div>

                        <div x-data="{
                            geo: {
                                id: Date.now() + {{ $index }},
                                address: '',
                                latitude: '',
                                longitude: ''
                            }
                        }" x-init="$nextTick(() => initAutocompleteForAll())">
                            <div class="space-y-4">
                                <input
                                    :id="'autocomplete_' + geo.id"
                                    :name="'address_' + geo.id"
                                    placeholder="Start typing address..."
                                    class="w-full rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                                    x-model="geo.address"
                                    x-on:address-selected.window="
                                        if ($event.detail.id == geo.id) {
                                            geo.address = $event.detail.address;
                                            geo.country = $event.detail.country;
                                            geo.city = $event.detail.city;
                                            geo.postcode = $event.detail.postcode;
                                            geo.latitude = $event.detail.latitude;
                                            geo.longitude = $event.detail.longitude;
                                            $wire.set('serviceAddresses.{{ $index }}.address', $event.detail.address);
                                            $wire.set('serviceAddresses.{{ $index }}.country', $event.detail.country);
                                            $wire.set('serviceAddresses.{{ $index }}.city', $event.detail.city);
                                            $wire.set('serviceAddresses.{{ $index }}.postcode', $event.detail.postcode);
                                            $wire.set('serviceAddresses.{{ $index }}.latitude', $event.detail.latitude);
                                            $wire.set('serviceAddresses.{{ $index }}.longitude', $event.detail.longitude);
                                        }
                                    "
                                    wire:model="serviceAddresses.{{ $index }}.address"
                                />
                                <div class="grid grid-cols-3 gap-4">
                                    <input
                                        :id="'country_' + geo.id"
                                        placeholder="Country"
                                        class="rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        x-model="geo.country"
                                        wire:model="serviceAddresses.{{ $index }}.country"
                                    />
                                    <input
                                        :id="'city_' + geo.id"
                                        placeholder="City"
                                        class="rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        x-model="geo.city"
                                        wire:model="serviceAddresses.{{ $index }}.city"
                                    />
                                    <input
                                        :id="'postcode_' + geo.id"
                                        placeholder="Post code"
                                        class="rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        x-model="geo.postcode"
                                        wire:model="serviceAddresses.{{ $index }}.postcode"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <input
                                        :id="'latitude_' + geo.id"
                                        placeholder="Latitude (auto-filled)"
                                        class="rounded-lg border border-gray-300 bg-gray-50 shadow-sm px-4 py-2.5 focus:outline-none"
                                        x-model="geo.latitude"
                                        wire:model="serviceAddresses.{{ $index }}.latitude"
                                        readonly
                                    />
                                    <input
                                        :id="'longitude_' + geo.id"
                                        placeholder="Longitude (auto-filled)"
                                        class="rounded-lg border border-gray-300 bg-gray-50 shadow-sm px-4 py-2.5 focus:outline-none"
                                        x-model="geo.longitude"
                                        wire:model="serviceAddresses.{{ $index }}.longitude"
                                        readonly
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <button
                        type="button"
                        wire:click="addServiceAddress"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('Add service address') }}
                    </button>
                </div>
            </section>
        </div>

        <!-- Billing Addresses Tab -->
        <div x-show="tab === 'billing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <section class="max-w-4xl space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <flux:heading size="xl">Billing Addresses</flux:heading>
                </div>
                <p class="text-sm text-gray-600 mb-4">Where invoices should be sent.</p>

                @foreach ($billingAddresses as $index => $billingAddress)
                    <div class="relative bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 bg-gray-900 text-white text-xs rounded-full">{{ $index + 1 }}</span>
                                {{ __('Billing Address') }}
                            </h4>
                            @if(count($billingAddresses) > 1)
                                <button
                                    type="button"
                                    wire:click="removeBillingAddress({{ $index }})"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('Remove') }}
                                </button>
                            @endif
                        </div>

                        <div x-data="{
                            geo: {
                                id: Date.now() + Math.random() + {{ $index + 1000 }},
                                address: @js($billingAddress['address'] ?? ''),
                                country: @js($billingAddress['country'] ?? ''),
                                city: @js($billingAddress['city'] ?? ''),
                                postcode: @js($billingAddress['postcode'] ?? ''),
                                latitude: @js($billingAddress['latitude'] ?? ''),
                                longitude: @js($billingAddress['longitude'] ?? ''),
                            }
                        }" x-init="$nextTick(() => initAutocompleteForAll())">
                            <div class="space-y-4">
                                <input
                                    :id="'autocomplete_' + geo.id"
                                    placeholder="Start typing address..."
                                    class="w-full rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                                    x-model="geo.address"
                                    x-on:address-selected.window="
                                        if ($event.detail.id == geo.id) {
                                            geo.address = $event.detail.address;
                                            geo.country = $event.detail.country;
                                            geo.city = $event.detail.city;
                                            geo.postcode = $event.detail.postcode;
                                            geo.latitude = $event.detail.latitude;
                                            geo.longitude = $event.detail.longitude;
                                            $wire.set('billingAddresses.{{ $index }}.address', $event.detail.address);
                                            $wire.set('billingAddresses.{{ $index }}.country', $event.detail.country);
                                            $wire.set('billingAddresses.{{ $index }}.city', $event.detail.city);
                                            $wire.set('billingAddresses.{{ $index }}.postcode', $event.detail.postcode);
                                            $wire.set('billingAddresses.{{ $index }}.latitude', $event.detail.latitude);
                                            $wire.set('billingAddresses.{{ $index }}.longitude', $event.detail.longitude);
                                        }
                                    "
                                    wire:model="billingAddresses.{{ $index }}.address"
                                />
                                <div class="grid grid-cols-3 gap-4">
                                    <input
                                        :id="'country_' + geo.id"
                                        placeholder="Country"
                                        class="rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        x-model="geo.country"
                                        wire:model="billingAddresses.{{ $index }}.country"
                                    />
                                    <input
                                        :id="'city_' + geo.id"
                                        placeholder="City"
                                        class="rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        x-model="geo.city"
                                        wire:model="billingAddresses.{{ $index }}.city"
                                    />
                                    <input
                                        :id="'postcode_' + geo.id"
                                        placeholder="Post code"
                                        class="rounded-lg border border-gray-300 bg-white shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        x-model="geo.postcode"
                                        wire:model="billingAddresses.{{ $index }}.postcode"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <input
                                        :id="'latitude_' + geo.id"
                                        placeholder="Latitude (auto-filled)"
                                        class="rounded-lg border border-gray-300 bg-gray-50 shadow-sm px-4 py-2.5 focus:outline-none"
                                        x-model="geo.latitude"
                                        wire:model="billingAddresses.{{ $index }}.latitude"
                                        readonly
                                    />
                                    <input
                                        :id="'longitude_' + geo.id"
                                        placeholder="Longitude (auto-filled)"
                                        class="rounded-lg border border-gray-300 bg-gray-50 shadow-sm px-4 py-2.5 focus:outline-none"
                                        x-model="geo.longitude"
                                        wire:model="billingAddresses.{{ $index }}.longitude"
                                        readonly
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <button
                        type="button"
                        wire:click="addBillingAddress"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('Add billing address') }}
                    </button>
                </div>
            </section>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-200">
            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Customers
            </a>

            <flux:button
                variant="primary"
                type="submit"
                class="px-6 py-3 text-base font-semibold"
                :disabled="$isSubmitting"
            >
                <span wire:loading.remove wire:target="save">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ __('Create Customer') }}
                </span>
                <span wire:loading wire:target="save" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating...
                </span>
            </flux:button>
        </div>
    </form>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
    <script>
        function clearAddressFields(id) {
            ['country', 'city', 'postcode', 'latitude', 'longitude'].forEach(field => {
                const el = document.getElementById(field + '_' + id);
                if (el) el.value = '';
            });
        }

        function initAutocompleteForAll() {
            document.querySelectorAll('input[id^="autocomplete_"]').forEach(input => {
                if (!input._autocompleteInitialized) {
                    const autocomplete = new google.maps.places.Autocomplete(input, {types: ['geocode']});
                    autocomplete.addListener('place_changed', function () {
                        const place = autocomplete.getPlace();
                        if (!place.geometry) return;
                        const id = input.id.replace('autocomplete_', '');
                        const address = input.value;
                        const latitude = place.geometry.location.lat();
                        const longitude = place.geometry.location.lng();

                        clearAddressFields(id);

                        let country = '';
                        let city = '';
                        let postcode = '';
                        if (place.address_components) {
                            const countryComponent = place.address_components.find(comp =>
                                comp.types.includes('country')
                            );
                            if (countryComponent) {
                                country = countryComponent.long_name;
                            }
                            const cityComponent = place.address_components.find(comp =>
                                comp.types.includes('locality') ||
                                comp.types.includes('postal_town') ||
                                comp.types.includes('administrative_area_level_2')
                            );
                            if (cityComponent) {
                                city = cityComponent.long_name;
                            }
                            const postcodeComponent = place.address_components.find(comp =>
                                comp.types.includes('postal_code')
                            );
                            if (postcodeComponent) {
                                postcode = postcodeComponent.long_name;
                            }
                        }

                        document.getElementById('latitude_' + id).value = latitude;
                        document.getElementById('longitude_' + id).value = longitude;
                        document.getElementById('country_' + id).value = country;
                        document.getElementById('city_' + id).value = city;
                        document.getElementById('postcode_' + id).value = postcode;

                        window.dispatchEvent(new CustomEvent('address-selected', {
                            detail: {id, address, latitude, longitude, country, city, postcode}
                        }));
                    });
                    input._autocompleteInitialized = true;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('autocomplete_address');
            if (input && !input._autocompleteInitialized) {
                const autocomplete = new google.maps.places.Autocomplete(input, { types: ['geocode'] });
                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    if (!place.geometry) return;

                    clearAddressFields('address');

                    const latitude = place.geometry.location.lat();
                    const longitude = place.geometry.location.lng();

                    let country = '';
                    let city = '';
                    let postcode = '';
                    if (place.address_components) {
                        for (const comp of place.address_components) {
                            if (comp.types.includes('country')) country = comp.long_name;
                            if (
                                comp.types.includes('locality') ||
                                comp.types.includes('postal_town') ||
                                comp.types.includes('administrative_area_level_2')
                            ) city = comp.long_name;
                            if (comp.types.includes('postal_code')) postcode = comp.long_name;
                        }
                    }

                    document.getElementById('country_address').value = country;
                    document.getElementById('city_address').value = city;
                    document.getElementById('postcode_address').value = postcode;
                    document.getElementById('latitude_address').value = latitude;
                    document.getElementById('longitude_address').value = longitude;

                    ['country', 'city', 'postcode', 'latitude', 'longitude'].forEach(field => {
                        const el = document.getElementById(field + '_address');
                        if (el) el.dispatchEvent(new Event('input', { bubbles: true }));
                    });

                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
                input._autocompleteInitialized = true;
            }
        });
    </script>

    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</div>
