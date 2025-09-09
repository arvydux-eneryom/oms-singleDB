<div x-data="{ tab: 'general' }">
    <!-- Tab Buttons -->
    <div class="flex gap-2 mb-6">
        <template x-for="(label, key) in {
            general: 'General',
            contacts: 'Contacts',
            service: 'Service Addresses',
            billing: 'Billing Addresses'
        }" :key="key">
            <button
                :class="tab === key
                ? 'bg-gray-900 text-white shadow-lg scale-105'
                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:text-gray-900'"
                class="px-5 py-2 rounded-full font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400"
                @click="tab = key"
                type="button"
                x-text="label"
            ></button>
        </template>
    </div>

    <form wire:submit="update" class="flex flex-col gap-6">
        <!-- General Tab -->
        <div x-show="tab === 'general'">
            <section class="max-w-4xl">
                <flux:heading size="xl" class="mb-3">Edit customer "{{$customer['company']}}"</flux:heading>
                <div class="flex gap-6">
                    <div class="flex-1 flex flex-col gap-6">
                        <div class="grid grid-cols-6 gap-x-6 items-center">
                            <div class="col-span-5">
                                <flux:input wire:model="customer.company" :label="__('Company Name')" id="company" required badge="required" />
                            </div>
                            <div>
                                <flux:field variant="inline" class="flex items-center mb-0">
                                    <label class="flex items-center cursor-pointer gap-2">
                                        <flux:checkbox wire:model="customer.status" class="mt-0.5" />
                                        <span class="text-gray-700">Active</span>
                                    </label>
                                    <flux:error name="status" class="ml-3" />
                                </flux:field>
                            </div>
                        </div>
                        <flux:input wire:model="customer.address" :label="__('Address')" id="autocomplete_address" required badge="required" />
                        <div class="grid grid-cols-3 gap-4">
                            <flux:input wire:model="customer.postcode" :label="__('Post Code')" id="postcode_address" />
                            <flux:input wire:model="customer.latitude" :label="__('Latitude')" id="latitude_address" />
                            <flux:input wire:model="customer.longitude" :label="__('Longitude')" id="longitude_address" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <flux:input wire:model="customer.country" :label="__('Country')" id="country_address" />
                            <flux:input wire:model="customer.city" :label="__('City')" id="city_address" />
                        </div>
                    </div>
                    <input type="hidden" wire:model="tenant_id">
                </div>
            </section>
            <section class="max-w-4xl mt-6">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Phones -->
                    <div class="flex-1 flex flex-col gap-4">
                        <flux:heading size="lg">Customer phones</flux:heading>
                        @if(is_array($phones) && count($phones))
                            @foreach ($phones as $index => $phone)
                                <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700">📞 {{ __('Phone') }} {{ $index + 1 }}</h4>
                                        @if(count($phones) > 1)
                                            <button type="button" wire:click="removePhone({{ $index }})" class="text-red-500 hover:text-red-700 text-sm flex items-center gap-1">🗑️ {{ __('Remove') }}</button>
                                        @endif
                                    </div>
                                    <flux:input wire:model="phones.{{ $index }}" :label="__('Phone number')" id="phone-{{ $index }}" required badge="required" />
                                    @error("phones.$index") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                    <div class="flex items-center gap-6 mt-4">
                                        <div class="flex-2">
                                            <flux:select size="sm" wire:model="phoneTypes.{{ $index }}" placeholder="Type" required>
                                                @foreach($phoneTypeOptions as $type)
                                                    <flux:select.option>{{ $type }}</flux:select.option>
                                                @endforeach
                                            </flux:select>
                                            @error("phoneTypes.$index") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="flex-1">
                                            <flux:field variant="inline">
                                                <flux:checkbox id="sms-enabled-{{ $index }}" wire:model="isSmsEnabled.{{ $index }}" />
                                                <flux:label for="sms-enabled-{{ $index }}">SMS Enabled</flux:label>
                                                <flux:error name="isSmsEnabled.{{ $index }}" />
                                            </flux:field>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <div>
                            <button type="button" wire:click="addPhone" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">➕ {{ __('Add another phone') }}</button>
                        </div>
                    </div>
                    <!-- Emails -->
                    <div class="flex-1 flex flex-col gap-4">
                        <flux:heading size="lg">Customer emails</flux:heading>
                        @if(is_array($emails) && count($emails))
                            @foreach ($emails as $index => $email)
                                <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700">📧 {{ __('Email') }} {{ $index + 1 }}</h4>
                                        @if(count($emails) > 1)
                                            <button type="button" wire:click="removeEmail({{ $index }})" class="text-red-500 hover:text-red-700 text-sm flex items-center gap-1">🗑️ {{ __('Remove') }}</button>
                                        @endif
                                    </div>
                                    <flux:input wire:model="emails.{{ $index }}" :label="__('Email address')" id="email-{{ $index }}" required badge="required" />
                                    @error("emails.$index") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                    <div class="flex items-center gap-6 mt-4">
                                        <div class="flex-3">
                                            <flux:select size="sm" wire:model="emailTypes.{{ $index }}" placeholder="Type">
                                                @foreach($emailTypeOptions as $type)
                                                    <flux:select.option>{{ $type }}</flux:select.option>
                                                @endforeach
                                            </flux:select>

                                            @error("emailTypes.$index") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="flex-1">
                                            <flux:field variant="inline">
                                                <flux:checkbox id="verified-{{ $index }}" wire:model="isVerified.{{ $index }}" />
                                                <flux:label for="verified-{{ $index }}">Verified</flux:label>
                                                <flux:error name="isVerified.{{ $index }}" />
                                            </flux:field>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <div>
                            <button type="button" wire:click="addEmail" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">➕ {{ __('Add another email') }}</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Contacts Tab -->
        <div x-show="tab === 'contacts'">
            <section class="max-w-4xl space-y-6">
                @if(is_array($contacts) && count($contacts))
                    @foreach ($contacts as $index => $contact)
                        <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-gray-700">👤 {{ __('Contact') }} {{ $index + 1 }}</h4>
                                @if(count($contacts) > 1)
                                    <button type="button" wire:click="removeContact({{ $index }})" class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">🗑️ {{ __('Remove') }}</button>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <flux:input wire:model="contacts.{{ $index }}.name" :label="__('Contact name')" id="contact-name-{{ $index }}" required badge="required" />
                                @error("contacts.$index.name") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                <flux:input wire:model="contacts.{{ $index }}.phone" :label="__('Contact phone')" id="contact-phone-{{ $index }}" />
                                @error("contacts.$index.phone") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                <flux:input wire:model="contacts.{{ $index }}.email" :label="__('Contact email')" id="contact-email-{{ $index }}" />
                                @error("contacts.$index.email") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    @endforeach
                @endif
                <div>
                    <button type="button" wire:click="addContact" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">
                        <x-heroicon-o-plus class="w-4 h-4"/>
                        {{ __('Add another contact') }}
                    </button>
                </div>
            </section>
        </div>

        <!-- Service Addresses Tab -->
        <div x-show="tab === 'service'">
            <section class="max-w-4xl space-y-6">
                @if(is_array($serviceAddresses) && count($serviceAddresses))
                    @foreach ($serviceAddresses as $index => $serviceAddress)
                        <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-gray-700">🏠 {{ __('Service address') }} {{ $index + 1 }}</h4>
                                @if(count($serviceAddresses) > 1)
                                    <button type="button" wire:click="removeServiceAddress({{ $index }})" class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">🗑️ {{ __('Remove') }}</button>
                                @endif
                            </div>
                            <div
                                x-data="{
                                    geolocations: [{
                                        id: Date.now() + Math.random(),
                                        address: @js($serviceAddress['address'] ?? ''),
                                        country: @js($serviceAddress['country'] ?? ''),
                                        city: @js($serviceAddress['city'] ?? ''),
                                        postcode: @js($serviceAddress['postcode'] ?? ''),
                                        latitude: @js($serviceAddress['latitude'] ?? ''),
                                        longitude: @js($serviceAddress['longitude'] ?? ''),
                                    }]
                                }"
                                x-init="$nextTick(() => initAutocompleteForAll())"
                            >
                                <template x-for="(geo, i) in geolocations" :key="geo.id">
                                    <div class="space-y-4">
                                        <div>
                                            <input
                                                :id="'autocomplete_' + geo.id"
                                                :name="'address_' + geo.id"
                                                placeholder="Enter address"
                                                class="w-full rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
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
                                                required
                                            />
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <input :id="'country_' + geo.id" :name="'country_' + geo.id" placeholder="Country" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.country" wire:model="serviceAddresses.{{ $index }}.country" />
                                            <input :id="'city_' + geo.id" :name="'city_' + geo.id" placeholder="City" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.city" wire:model="serviceAddresses.{{ $index }}.city" />
                                            <input :id="'postcode_' + geo.id" :name="'postcode_' + geo.id" placeholder="Post code" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.postcode" wire:model="serviceAddresses.{{ $index }}.postcode" />
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <input :id="'latitude_' + geo.id" :name="'latitude_' + geo.id" placeholder="Latitude" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.latitude" wire:model="serviceAddresses.{{ $index }}.latitude" />
                                            <input :id="'longitude_' + geo.id" :name="'longitude_' + geo.id" placeholder="Longitude" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.longitude" wire:model="serviceAddresses.{{ $index }}.longitude" />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @endforeach
                @endif
                <div>
                    <button type="button" wire:click="addServiceAddress" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">➕ {{ __('Add another service address') }}</button>
                </div>
            </section>
        </div>

        <!-- Billing Addresses Tab -->
        <div x-show="tab === 'billing'">
            <section class="max-w-4xl space-y-6">
                @if(is_array($billingAddresses) && count($billingAddresses))
                    @foreach ($billingAddresses as $index => $billingAddress)
                        <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-gray-700">🏠 {{ __('Billing address') }} {{ $index + 1 }}</h4>
                                @if(count($billingAddresses) > 1)
                                    <button type="button" wire:click="removeBillingAddress({{ $index }})" class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">🗑️ {{ __('Remove') }}</button>
                                @endif
                            </div>
                            <div
                                x-data="{
                                    geo: {
                                        id: Date.now() + Math.random(),
                                        address: @js($billingAddress['address'] ?? ''),
                                        country: @js($billingAddress['country'] ?? ''),
                                        city: @js($billingAddress['city'] ?? ''),
                                        postcode: @js($billingAddress['postcode'] ?? ''),
                                        latitude: @js($billingAddress['latitude'] ?? ''),
                                        longitude: @js($billingAddress['longitude'] ?? ''),
                                    }
                                }"
                                x-init="$nextTick(() => initAutocompleteForAll())"
                            >
                                <div class="space-y-4">
                                    <input :id="'autocomplete_' + geo.id" :name="'address_' + geo.id" placeholder="Enter address" class="w-full rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.address" x-on:address-selected.window="
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
                                    " wire:model="billingAddresses.{{ $index }}.address" required />
                                    <div class="grid grid-cols-3 gap-4">
                                        <input :id="'country_' + geo.id" :name="'country_' + geo.id" placeholder="Country" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.country" wire:model="billingAddresses.{{ $index }}.country" />
                                        <input :id="'city_' + geo.id" :name="'city_' + geo.id" placeholder="City" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.city" wire:model="billingAddresses.{{ $index }}.city" />
                                        <input :id="'postcode_' + geo.id" :name="'postcode_' + geo.id" placeholder="Post code" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.postcode" wire:model="billingAddresses.{{ $index }}.postcode" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <input :id="'latitude_' + geo.id" :name="'latitude_' + geo.id" placeholder="Latitude" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.latitude" wire:model="billingAddresses.{{ $index }}.latitude" />
                                        <input :id="'longitude_' + geo.id" :name="'longitude_' + geo.id" placeholder="Longitude" class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" x-model="geo.longitude" wire:model="billingAddresses.{{ $index }}.longitude" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                <div>
                    <button type="button" wire:click="addBillingAddress" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900">➕ {{ __('Add another billing address') }}</button>
                </div>
            </section>
        </div>

        <div class="mt-6">
            <flux:button variant="primary" type="submit">{{ __('Update customer') }}</flux:button>
        </div>
    </form>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places"></script>
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
</div>
