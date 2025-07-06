<div>
    <section class="max-w-md">
        <flux:heading size="xl" class="mb-3">Create permission</flux:heading>

        <form wire:submit="save" class="flex flex-col gap-6">
            <!-- Tenant ID (hidden) -->
            <flux:input
                wire:model="tenant"
                type="hidden"
                value="{{ tenant('id') }}"
            />

            <flux:input
                wire:model="name"
                :label="__('Permission Name')"
                required
                badge="required"
            />
            <div>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </section>
</div>

