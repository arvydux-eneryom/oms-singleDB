<div>
    <section class="max-w-md">
        <form wire:submit="save" class="flex flex-col gap-6">
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

            <flux:select wire:model="assignedSubdomain" :label="__('Subdomains')" placeholder="Choose subdomain to assign...">
                <flux:select.option value="">{{ __('Choose subdomain to assign...') }}</flux:select.option>
                @foreach ($this->notAssignedSubdomains as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Assigned domains
                    <th scope="col" class="px-6 py-3">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($user->tenants) && $user->tenants->count() > 0)
                    {{-- Display domains for each tenant --}}
                    @foreach($user->tenants as $tenant)
                        @foreach($tenant->domains as $domain)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                                <td class="px-6 py-4">
                                    {{ \App\Models\Domain::where('tenant_id', $tenant->id)->first()->domain }}
                                </td>
                                <td class="px-6 py-4 space-x-2">
                                    <flux:button wire:confirm="Are you sure?" wire:click="unassignDomain({{ $tenant->id }}, {{ $user->id }})"
                                                 variant="danger" type="button">{{ __('Revoke access') }}</flux:button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    <td class="px-6 py-4">
                        No domains assigned
                    </td>
                @endif
                </tbody>
            </table>
            <div>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </section>
</div>
