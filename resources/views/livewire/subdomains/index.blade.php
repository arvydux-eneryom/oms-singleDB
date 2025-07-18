@php use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
@endphp
<div>
    <section>
        <x-alerts.success/>

        <div class="flex flex-grow gap-x-4 mb-4">
            <flux:button href="{{ route('subdomains.create') }}"
                         variant="filled">{{ __('Create Subdomain') }}</flux:button>
        </div>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <flux:heading size="xl" class="mb-3">Subdomains Management</flux:heading>

            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Company name
                    </th>
                    <th scope="col" class="px-6 py-3">
                        URL
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Users amount
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Created
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($subdomains))
                    @foreach ($subdomains as $subdomain)
                        <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                            {{--                            <td class="px-6 py-4">
                                                            {{ explode('.', $subdomain->domain)[0] }}
                                                        </td>--}}
                            <td class="px-6 py-4">
                                {{ $subdomain->name }}
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @php
                                    // Generate a signed route for auto-login
                                    $fullUrl = URL::temporarySignedRoute(
                                        'auto-login',
                                        now()->addMinutes(10),
                                        [
                                            'user' => Auth::user()->id,
                                            'subdomain' => $subdomain->subdomain, // just the subdomain part, e.g. 'client1'
                                        ]
                                    );
                                @endphp
                                <a href="{{ $fullUrl }}" target="_blank">{{ request()->getScheme() . '://' . $subdomain->domain . ':' . request()->getPort()}}</a>
                            </th>
                            <td class="px-6 py-4">
                                {{ $subdomain->tenant->users_count }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $subdomain->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <flux:button href="{{ route('subdomains.edit', $subdomain) }}"
                                             variant="filled">{{ __('Edit') }}</flux:button>
                                <flux:button wire:confirm="All users will also be removed. Are you sure?"
                                             wire:click="delete({{ $subdomain->id }})" variant="danger"
                                             type="button">{{ __('Delete') }}</flux:button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
        @if($subdomains->hasPages())
            <div class="mt-5">
                {{ $subdomains->links() }}
            </div>
        @endif
    </section>
</div>
