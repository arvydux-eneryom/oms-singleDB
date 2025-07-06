<div>
    <section class="max-w-5xl">
        <form wire:submit="save" class="flex flex-col gap-6">
            <!-- Tenant ID (hidden) -->
            <flux:input
                wire:model="tenant"
                type="hidden"
                value="{{ tenant('id') }}"
            />

            <flux:input
                wire:model="name"
                :label="__('Permission name')"
                required
                badge="required"
            />

            <div>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </section>
</div>
