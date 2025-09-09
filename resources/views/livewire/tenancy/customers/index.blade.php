<!-- resources/views/livewire/tenancy/customers/index.blade.php -->
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
        <a href="{{ route('customers.create') }}"
           class="inline-flex items-center px-2.5 py-1 text-sm bg-gray-900 text-white font-semibold rounded shadow hover:bg-gray-950 transition">
            <span class="text-lg mr-1">+</span> Add Customer
        </a>
    </div>

{{--    <div class="mb-4">
        <input
            type="text"
            wire:model.debounce.300ms="search"
            placeholder="Search by company or address..."
            class="w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
    </div>--}}

    <table class="min-w-full bg-white border border-gray-200 rounded-xl shadow text-sm">
        <thead>
        <tr class="bg-gray-100">
            <th class="px-2 py-1 text-left font-semibold text-gray-600 uppercase tracking-wider">Company name</th>
            <th class="px-2 py-1 text-left font-semibold text-gray-600 uppercase tracking-wider">Address</th>
            <th class="px-2 py-1 text-left font-semibold text-gray-600 uppercase tracking-wider">Post code</th>
            <th class="px-2 py-1 text-left font-semibold text-gray-600 uppercase tracking-wider">Phones</th>
            <th class="px-2 py-1 text-left font-semibold text-gray-600 uppercase tracking-wider">Emails</th>
            <th class="px-2 py-1 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($customers as $customer)
            <tr
                    class="transition hover:bg-gray-50 {{ $loop->even ? 'bg-gray-50' : '' }} cursor-pointer"
                    onclick="window.location='{{ route('customers.show', $customer->id) }}'"
            >
                <td class="px-4 py-1 min-w-[200px] border-b border-gray-200 font-medium text-gray-900">{{ $customer->company }}</td>                <td class="px-2 py-1 border-b border-gray-200 text-gray-700">
                    {{ $customer->address }}
                </td>
                <td class="px-2 py-1 border-b border-gray-200 text-gray-700">
                    {{$customer->postcode }}
                </td>
                <td class="px-2 py-1 border-b border-gray-200 text-gray-700">
                    {{ is_iterable($customer->customerPhones)
                        ? implode(', ', collect($customer->customerPhones)->pluck('phone')->all())
                        : '' }}
                </td>
                <td class="px-2 py-1 border-b border-gray-200 text-gray-700">
                    {{ is_iterable($customer->customerEmails)
                        ? implode(', ', collect($customer->customerEmails)->pluck('email')->all())
                        : '' }}
                </td>
                <td class="px-2 py-1 border-b border-gray-200">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $customer->status ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                    {{ $customer->status ? 'Active' : 'Inactive' }}
                </span>
                </td>
                {{--
                                <td class="px-2 py-1 border-b border-gray-200">
                                    <a href="{{ route('customers.show', $customer->id) }}"
                                       class="inline-block px-2 py-0.5 text-xs text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition mr-1"
                                       onclick="event.stopPropagation();">View</a>
                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                       class="inline-block px-2 py-0.5 text-xs text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition mr-1"
                                       onclick="event.stopPropagation();">Edit</a>
                                    <button
                                        @click="$wire.set('customerIdToDelete', {{ $customer->id }}); $dispatch('open-modal')"
                                        class="inline-block px-2 py-0.5 text-xs text-white bg-red-500 rounded hover:bg-red-600 transition"
                                        type="button"
                                        onclick="event.stopPropagation();"
                                    >
                                        Delete
                                    </button>
                                </td>
                --}}
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-sm text-gray-500">No customers found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $customers->links() }}
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        x-data="{ open: false }"
        x-show="open"
        @open-modal.window="open = true"
        @close-modal.window="open = false"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50"
        style="display: none;"
    >
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-lg font-semibold text-gray-800">Confirm Deletion</h2>
            <p class="mt-2 text-gray-600">Are you sure you want to delete this customer? This action cannot be undone.</p>

            <div class="mt-4 flex justify-end gap-4">
                <button
                    @click="$dispatch('close-modal')"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
                >
                    Cancel
                </button>
                <button
                    wire:click="deleteCustomer"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
