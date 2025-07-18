<div>
    <section class="max-w-5xl">
        <form wire:submit="save" class="flex flex-col gap-6">

            <flux:input
                wire:model="name"
                :label="__('Role name')"
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
