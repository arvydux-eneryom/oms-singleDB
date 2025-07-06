<div>
    <section class="max-w-md">
        <flux:heading size="xl" class="mb-3">Create role</flux:heading>

        <form wire:submit="save" class="flex flex-col gap-6">
            <!-- Tenant ID (hidden) -->
            <flux:input
                wire:model="tenant"
                type="hidden"
                value="{{ tenant('id') }}"
            />

            <flux:input
                wire:model="name"
                :label="__('Role Name')"
                required
                badge="required"
            />

            <flux:checkbox.group wire:model="rolePermissions" label="Role permissions">
                @foreach($permissions as $value)
                    <flux:checkbox label="{{$value->name}}" value="{{$value->name}}" />
                @endforeach
            </flux:checkbox.group>

            <div>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </section>
</div>

