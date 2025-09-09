<div class="flex gap-6">
    <div class="flex-1 flex flex-col gap-4">
        <flux:heading size="lg">Customer addresses</flux:heading>

        @if(is_array($serviceAddresses) && count($serviceAddresses))
            @foreach ($serviceAddresses as $index => $serviceAddress)
                <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-semibold text-gray-700">
                            🏠 {{ __('Service address') }} {{ $index + 1 }}
                        </h4>
                        @if(count($serviceAddresses) > 1)
                            <button
                                type="button"
                                wire:click="removeServiceAddress({{ $index }})"
                                class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium
           text-gray-600 bg-gray-100 border border-gray-300 rounded-lg
           hover:bg-gray-200 hover:text-gray-900"
                            >
                                🗑️ {{ __('Remove') }}
                            </button>
                        @endif
                    </div>

                    <!-- Address inputs -->
                    <div
                        x-data="{
                            geolocations: [{ id: Date.now(), address: '', latitude: '', longitude: '' }],
                        }"
                        x-init="$nextTick(() => initAutocompleteForAll())"
                    >
                        <template x-for="(geo, i) in geolocations" :key="geo.id">
                            <div class="space-y-4">
                                <!-- Address (full width) -->
                                <div>
                                    <input
                                        :id="'autocomplete_' + geo.id"
                                        :name="'address_' + geo.id"
                                        placeholder="Enter address"
                                        class="w-full rounded-lg border border-gray-200 bg-white shadow-sm p-2
                                               focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                        x-model="geo.address"
                                        x-on:address-selected.window="
                                            if ($event.detail.id == geo.id) {
                                                geo.address = $event.detail.address;
                                                geo.latitude = $event.detail.latitude;
                                                geo.longitude = $event.detail.longitude;
                                                $wire.set('serviceAddresses.{{ $index }}', $event.detail.address);
                                            }
                                        "
                                        wire:model="serviceAddresses.{{ $index }}"
                                        required
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <input
                                        :id="'country_' + geo.id"
                                        :name="'country_' + geo.id"
                                        placeholder="Country"
                                        class="rounded-lg border border-gray-200 bg-white shadow-sm p-2
                                               focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                        x-model="geo.country"
                                    />
                                    <input
                                        :id="'city_' + geo.id"
                                        :name="'city_' + geo.id"
                                        placeholder="City"
                                        class="rounded-lg border border-gray-200 bg-white shadow-sm p-2
                                         focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                        x-model="geo.city"
                                    />
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <input
                                        :id="'postcode_' + geo.id"
                                        :name="'postcode_' + geo.id"
                                        placeholder="Post code"
                                        class="rounded-lg border border-gray-200 bg-white shadow-sm p-2
                                        focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                        x-model="geo.postcode"
                                    />
                                    <input
                                        :id="'latitude_' + geo.id"
                                        :name="'latitude_' + geo.id"
                                        placeholder="Latitude"
                                        class="rounded-lg border border-gray-200 bg-white shadow-sm p-2
                                               focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                        x-model="geo.latitude"
                                    />
                                    <input
                                        :id="'longitude_' + geo.id"
                                        :name="'longitude_' + geo.id"
                                        placeholder="Longitude"
                                        class="rounded-lg border border-gray-200 bg-white shadow-sm p-2
                                               focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                        x-model="geo.longitude"
                                    />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Add Service Address button -->
        <div>
            <button
                type="button"
                wire:click="addServiceAddress"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                       text-gray-700 bg-gray-100 border border-gray-300 rounded-lg
                       hover:bg-gray-200 hover:text-gray-900"
            >
                ➕ {{ __('Add another service address') }}
            </button>
        </div>
    </div>

    <!-- Customer contacts form/content here -->
</div>
