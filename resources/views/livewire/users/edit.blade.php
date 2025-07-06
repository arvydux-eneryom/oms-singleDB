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
                :label="__('User name')"
                required
                badge="required"
            />

            <flux:input
                wire:model="email"
                :label="__('Email')"
                required
                badge="required"
            />

            <flux:select wire:model="userRoles" :label="__('Role')" placeholder="Choose role...">
                @foreach ($roles as $value => $label)
                    <flux:select.option>{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <div>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </section>
</div>
