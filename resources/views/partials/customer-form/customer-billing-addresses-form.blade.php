<div class="space-y-6">
    @if(is_array($billingAddresses) && count($billingAddresses))
        @foreach ($billingAddresses as $index => $billingAddress)
            <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-semibold text-gray-700">
                        🏠 {{ __('Billing address') }} {{ $index + 1 }}
                    </h4>
                    @if(count($billingAddresses) > 1)
                        <button
                            type="button"
                            wire:click="removeBillingAddress({{ $index }})"
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
                                    class="w-full rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                    x-model="geo.address"
                                    x-on:address-selected.window="
                                        if ($event.detail.id == geo.id) {
                                            geo.address = $event.detail.address;
                                            geo.latitude = $event.detail.latitude;
                                            geo.longitude = $event.detail.longitude;
                                            $wire.set('billingAddresses.{{ $index }}', $event.detail.address);
                                        }
                                    "
                                    wire:model="billingAddresses.{{ $index }}"
                                    required
                                />
                            </div>
                            <!-- Latitude and Longitude (side by side) -->
                            <div class="grid grid-cols-2 gap-4">
                                <input
                                    disabled
                                    :id="'latitude_' + geo.id"
                                    :name="'latitude_' + geo.id"
                                    placeholder="Latitude"
                                    class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                    x-model="geo.latitude"
                                />
                                <input
                                    disabled
                                    :id="'longitude_' + geo.id"
                                    :name="'longitude_' + geo.id"
                                    placeholder="Longitude"
                                    class="rounded-lg border border-gray-200 bg-white shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                                    x-model="geo.longitude"
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Add Billing Address button -->
    <div>
        <button
            type="button"
            wire:click="addBillingAddress"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-gray-700 bg-gray-100 border border-gray-300 rounded-lg
                   hover:bg-gray-200 hover:text-gray-900"
        >
            ➕ {{ __('Add another billing address') }}
        </button>
    </div>
</div>
