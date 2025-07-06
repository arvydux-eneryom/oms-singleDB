<div>
    <section class="max-w-md">
        <flux:heading size="xl" class="mb-3">Create user</flux:heading>

        <form wire:submit="save" class="flex flex-col gap-6">
            <!-- Tenant ID (hidden) -->
            <flux:input
                wire:model="tenant"
                type="hidden"
                value="{{ tenant('id') }}"
            />

            <flux:input
                wire:model="name"
                :label="__('User Name')"
                required
                badge="required"
            />

            <flux:input
                wire:model="email"
                :label="__('Email')"
                required
                badge="required"
            />

            <flux:input
                wire:model="password"
                :label="__('Password')"
                required
                badge="required"
                type="password"
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
