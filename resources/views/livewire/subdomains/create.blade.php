<div>
    <section class="max-w-md">
        <flux:heading size="xl" class="mb-3">Create a Subdomain</flux:heading>

        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:input
                wire:model="subdomain"
                :label="__('Subdomain')"
                type="text"
                required
                autofocus
                autocomplete="subdomain"
                :placeholder="__('Subdomain')"
            />

            <flux:input
                wire:model="companyName"
                :label="__('Company name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Company name')"
            />

            <div>
                <flux:button variant="primary" type="submit">{{ __('Create account') }}</flux:button>
            </div>
        </form>
    </section>
</div>
