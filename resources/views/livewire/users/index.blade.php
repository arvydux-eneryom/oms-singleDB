<div>
    <section>
        <x-alerts.success />

        <div class="flex flex-grow gap-x-4 mb-4">
            <flux:button href="{{ route('users.create') }}" variant="filled">{{ __('Create User') }}</flux:button>
        </div>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
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
                        Roles
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($users))
                    @foreach ($users as $key => $user)
                        <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $user->name }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $user->email }}
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
                                <flux:button href="{{ route('users.edit', $user) }}" variant="filled">{{ __('Edit') }}</flux:button>
                                <flux:button wire:confirm="Are you sure?" wire:click="delete({{ $user->id }})" variant="danger" type="button">{{ __('Delete') }}</flux:button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="mt-5">
                {{ $users->links() }}
            </div>
        @endif
    </section>
</div>
