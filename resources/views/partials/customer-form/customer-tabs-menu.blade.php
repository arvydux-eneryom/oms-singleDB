<div x-data="{ activeTab: 'main' }" class="w-full">
    <div class="flex space-x-2 bg-gray-100 rounded-lg p-1 mb-6">
        <button
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200 focus:outline-none"
            :class="activeTab === 'main'
            ? 'bg-black text-white shadow'
            : 'text-black hover:bg-gray-200'"
            @click="activeTab = 'main'"
            type="button"
        >
            {{ __('Main') }}
        </button>
        <button
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200 focus:outline-none"
            :class="activeTab === 'contacts'
            ? 'bg-black text-white shadow'
            : 'text-black hover:bg-gray-200'"
            @click="activeTab = 'contacts'"
            type="button"
        >
            {{ __('Contacts') }}
        </button>
        <button
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200 focus:outline-none"
            :class="activeTab === 'details'
            ? 'bg-black text-white shadow'
            : 'text-black hover:bg-gray-200'"
            @click="activeTab = 'details'"
            type="button"
        >
            {{ __('Details') }}
        </button>
        <button
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200 focus:outline-none"
            :class="activeTab === 'notes'
            ? 'bg-black text-white shadow'
            : 'text-black hover:bg-gray-200'"
            @click="activeTab = 'notes'"
            type="button"
        >
            {{ __('Notes') }}
        </button>
    </div>
    <form wire:submit="save" class="flex flex-col gap-6">
    <div x-show="activeTab === 'main'">
        <!-- Customer details form/content here -->


        <section class="max-w-4xl">
            <flux:heading size="xl" class="mb-3">Create customer</flux:heading>
                @include('partials.customer-form.customer-main-form')

                <div>
                    <flux:button variant="primary" type="submit">{{ __('Create customer') }}</flux:button>
                </div>
        </section>
    </div>
    </form>
    <div x-show="activeTab === 'contacts'">
        <section class="max-w-4xl">
            <flux:heading size="xl" class="mb-3">Create customer addresses</flux:heading>
                @include('partials.customer-form.customer-contacts-form')
                <div>
                    <flux:button variant="primary" type="submit">{{ __('Create customer') }}</flux:button>
                </div>

        </section>
    </div>
    <div x-show="activeTab === 'details'">
        <!-- Customer details form/content here -->
        <section class="max-w-4xl">
            <flux:heading size="xl" class="mb-3">Create customer</flux:heading>

                @include('partials.customer-form.customer-contact-addresses-form')
                <div>
                    <flux:button variant="primary" type="submit">{{ __('Create customer') }}</flux:button>
                </div>

        </section>
    </div>
    <div x-show="activeTab === 'notes'">
        <section class="max-w-4xl">
            <flux:heading size="xl" class="mb-3">Create customer addresses</flux:heading>

                @include('partials.customer-form.customer-billing-addresses-form')
                <div>
                    <flux:button variant="primary" type="submit">{{ __('Create customer') }}</flux:button>
                </div>

        </section>
        <!-- Customer notes form/content here -->
    </div>

</div>
