<div>
    <section class=" ">
        <x-alerts.success />
        <flux:heading size="xl" class="mb-3">Edit a Subdomain {{ $subdomain->domain }}</flux:heading>

        <form wire:submit="save" class="">
            <div class="flex gap-4 w-full mb-4">
                <div class="flex flex-col flex-1 h-24">
                    <flux:input
                        wire:model="name"
                        :label="__('Company name')"
                        type="text"
                        required
                        autocomplete="name"
                        :placeholder="__('Full name')"
                        class="flex-1"
                    />
                    @error('name')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex flex-col flex-1 h-24">
                    <flux:input
                        wire:model="subdomainText"
                        :label="__('Subdomain')"
                        type="text"
                        required
                        autocomplete="subdomain"
                        :placeholder="__('Subdomain')"
                        class="flex-1"
                    />
                    @error('subdomainText')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="primary" type="button" wire:click="saveAndClose">
                    {{ __('Save and close') }}
                </flux:button>
            </div>
        </form>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-6">
                <flux:heading size="xl" class="mb-3">Users Management</flux:heading>

                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Access to domains
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Roles
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($subdomain->tenant->users))
                        @foreach ($subdomain->tenant->users as $key => $user)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $user->name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4">
                                    @if(!empty($user->tenants) && $user->tenants->count() > 0)
                                        {{-- Display domains for each tenant --}}
                                        @foreach($user->tenants as $tenant)
                                            <ul class="list-none">
                                                @foreach($tenant->domains as $domain)
                                                    <li class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium text-gray-900">
                                                        {{--                                 <a href="{{ url('http://' . $subdomain->domain) . ':8000' }}"
                                                                                            target="_blank">{{ 'http://' . $subdomain->domain  . ':8000'}}</a>--}}
                                                        {{ \App\Models\Domain::where('tenant_id', $tenant->id)->first()->domain }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-red-100 text-red-800">
                                        No domains assigned
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if(!empty($user->getRoleNames()))
                                        @foreach($user->getRoleNames() as $role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium  bg-green-100 text-green-800">
                                            {{ $role }}
                                        </span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-x-2">
                                    <flux:button wire:confirm="Are you sure?" wire:click="unassignDomain({{ $tenant->id }}, {{ $user->id }})"
                                                 variant="danger" type="button">{{ __('Revoke access') }}</flux:button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
    </section>
</div>
