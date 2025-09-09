<div class="space-y-6">
    @if(is_array($contacts) && count($contacts))
        @foreach ($contacts as $index => $contact)
            <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-semibold text-gray-700">
                        👤 {{ __('Contact') }} {{ $index + 1 }}
                    </h4>
                    @if(count($contacts) > 1)
                        <button
                            type="button"
                            wire:click="removeContact({{ $index }})"
                            class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium
           text-gray-600 bg-gray-100 border border-gray-300 rounded-lg
           hover:bg-gray-200 hover:text-gray-900"
                        >
                            🗑️ {{ __('Remove') }}
                        </button>
                    @endif
                </div>

                <!-- Contact fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input
                        wire:model="contacts.{{ $index }}.name"
                        :label="__('Contact name')"
                        id="contact-name-{{ $index }}"
                        required
                        badge="required"
                    />

                    <flux:input
                        wire:model="contacts.{{ $index }}.phone"
                        :label="__('Contact phone')"
                        id="contact-phone-{{ $index }}"
                        required
                        badge="required"
                    />

                    <flux:input
                        wire:model="contacts.{{ $index }}.email"
                        :label="__('Contact email')"
                        id="contact-email-{{ $index }}"
                        required
                        badge="required"
                    />
                </div>
            </div>
        @endforeach
    @endif

    <!-- Add Contact button -->

    <!-- Add Contact button -->
    <div>
        <button
            type="button"
            wire:click="addContact"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
           text-gray-700 bg-gray-100 border border-gray-300 rounded-lg
           hover:bg-gray-200 hover:text-gray-900"
        >
            <x-heroicon-o-plus class="w-4 h-4"/>
            {{ __('Add another contact') }}
        </button>

    </div>
</div>
