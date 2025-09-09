<div class="flex gap-6">
    <div class="flex-1 flex flex-col gap-6">
        <flux:input
            wire:model="customer.company"
            :label="__('Company Name')"
            id="company"
            required
            badge="required"
        />

        <flux:input
            wire:model="customer.address"
            :label="__('Address')"
            id="autocomplete_address"
            required
            badge="required"
        />

        <div class="grid grid-cols-3 gap-4">
            <input
                wire:model="customer.postcode"
                id="postcode_address"
                name="postcode_address"
                placeholder="Post code"
                class="rounded-lg border border-gray-200 bg-white shadow-sm p-3 text-sm  text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
            />
            @error('customer.postcode')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
            <div class="relative">
                <input
                    wire:model="customer.latitude"
                    id="latitude_address"
                    name="latitude_address"
                    placeholder="Latitude"
                    class="rounded-lg border border-gray-200 bg-white shadow-sm p-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent w-full"
                />
                @error('customer.latitude')
                <div class="absolute left-0 top-full mt-1 w-full text-red-600 text-xs">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="relative">
                <input
                    wire:model="customer.longitude"
                    id="longitude_address"
                    name="longitude_address"
                    placeholder="Longitude"
                    class="rounded-lg border border-gray-200 bg-white shadow-sm p-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent w-full"
                />
                @error('customer.longitude')
                <div class="absolute left-0 top-full mt-1 w-full text-red-600 text-xs">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="relative">
                <input
                    wire:model="customer.country"
                    id="country_address"
                    name="country_address"
                    placeholder="Country"
                    class="w-full rounded-lg border border-gray-200 bg-white shadow-sm p-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                    x-model="geo.country"
                />
                @error('customer.country')
                <div class="absolute left-0 top-full mt-1 w-full text-red-600 text-xs">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="relative">
                <input
                    wire:model="customer.city"
                    id="city_address"
                    name="city_address"
                    placeholder="City"
                    class="w-full rounded-lg border border-gray-200 bg-white shadow-sm p-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                    x-model="geo.city"
                />
                @error('customer.city')
                <div class="absolute left-0 top-full mt-1 w-full text-red-600 text-xs">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">

            <div class="flex-1 flex flex-col gap-6">
                @if(is_array($phones) && count($phones))
                    @foreach ($phones as $index => $phone)
                        <div
                            class="border border-gray-200 bg-white rounded-lg shadow-sm p-4 flex flex-col gap-4 mb-3">
                            <flux:input
                                wire:model="phones.{{ $index }}"
                                :label="__('Phone') . ' ' . ($index + 1)"
                                id="phone-{{ $index }}"
                                required
                                badge="required"
                            />
                            @error("phones.$index")
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                            <div class="flex items-center gap-6">
                                <div class="flex-2">
                                    <flux:select size="sm" wire:model="phoneTypes.{{ $index }}"
                                                 placeholder="Type" required>
                                        @foreach($phoneTypeOptions as $type)
                                            <flux:select.option>{{ $type }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    @error("phoneTypes.$index")
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="flex-1">
                                    <flux:field variant="inline">
                                        <flux:checkbox id="sms-enabled-{{ $index }}"
                                                       wire:model="isSmsEnabled.{{ $index }}"/>
                                        <flux:label for="sms-enabled-{{ $index }}">Sms Enabled</flux:label>
                                        <flux:error name="isSmsEnabled.{{ $index }}"/>
                                    </flux:field>
                                </div>
                            </div>
                        </div>
                        @if(count($phones) > 1)
                            <flux:button
                                type="button"
                                wire:click="removePhone({{ $index }})"
                                size="sm"
                                class="px-3 py-1 text-sm font-medium text-red-600 bg-red-100 rounded hover:bg-red-200"
                            >
                                {{ __('Remove phone') }}
                            </flux:button>
                        @endif
                    @endforeach
                @endif
                <div class="flex gap-2 mt-1">
                    <flux:button
                        type="button"
                        wire:click="addPhone"
                        size="sm"
                        class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded hover:bg-blue-200"
                    >
                        {{ __('Add another phone') }}
                    </flux:button>
                </div>
            </div>

            <div class="flex-1 flex flex-col gap-6">
                @if(is_array($emails) && count($emails))
                    @foreach ($emails as $index => $email)
                        <div
                            class="border border-gray-200 bg-white rounded-lg shadow-sm p-4 flex flex-col gap-4 mb-3">
                            <flux:input
                                wire:model="emails.{{ $index }}"
                                :label="__('Email') . ' ' . ($index + 1)"
                                id="email-{{ $index }}"
                                required
                                badge="required"
                            />
                            @error("emails.$index")
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                            <div class="flex items-center gap-6">
                                <div class="flex-3">
                                    <flux:select size="sm" wire:model="emailTypes.{{ $index }}"
                                                 placeholder="Type">
                                        @foreach($emailTypeOptions as $type)
                                            <flux:select.option>{{ $type }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    @error("emailTypes.$index")
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="flex-1">
                                    <flux:field variant="inline">
                                        <flux:checkbox id="verified-{{ $index }}"
                                                       wire:model="isVerified.{{ $index }}"/>
                                        <flux:label for="verified-{{ $index }}">Verified</flux:label>
                                        <flux:error name="isVerified.{{ $index }}"/>
                                    </flux:field>
                                </div>
                            </div>
                        </div>
                        @if(count($emails) > 1)
                            <flux:button
                                type="button"
                                wire:click="removeEmail({{ $index }})"
                                size="sm"
                                class="px-3 py-1 text-sm font-medium text-red-600 bg-red-100 rounded hover:bg-red-200"
                            >
                                {{ __('Remove email') }}
                            </flux:button>
                        @endif
                    @endforeach
                @endif
                <div class="flex gap-2 mt-1">
                    <flux:button
                        type="button"
                        wire:click="addEmail"
                        size="sm"
                        class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded hover:bg-blue-200"
                    >
                        {{ __('Add another email') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" wire:model="tenant_id">
</div>

