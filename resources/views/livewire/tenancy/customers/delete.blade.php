<!-- resources/views/livewire/tenancy/customers/delete.blade.php -->
<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-lg font-semibold text-gray-800">Confirm Deletion</h2>
    <p class="mt-2 text-gray-600">Are you sure you want to delete this customer? This action cannot be undone.</p>

    <div class="mt-4 flex justify-end gap-4">
        <button
            wire:click="$emit('cancelDelete')"
            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
            Cancel
        </button>
        <button
            wire:click="deleteCustomer"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            Delete
        </button>
    </div>
</div>
