<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Delete extends Component
{
    public $customerId;

    public Customer $customer;

    public bool $isDeleting = false;

    public bool $showConfirmation = true;

    // Customer info for display
    public string $customerName = '';

    public int $relatedRecordsCount = 0;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->loadCustomer();
    }

    private function loadCustomer(): void
    {
        $this->customer = Customer::with([
            'customerPhones',
            'customerEmails',
            'customerContacts',
            'customerServiceAddresses',
            'customerBillingAddresses',
        ])->findOrFail($this->customerId);

        $this->customerName = $this->customer->company;

        // Count related records
        $this->relatedRecordsCount =
            $this->customer->customerPhones->count() +
            $this->customer->customerEmails->count() +
            $this->customer->customerContacts->count() +
            $this->customer->customerServiceAddresses->count() +
            $this->customer->customerBillingAddresses->count();
    }

    public function deleteCustomer(): void
    {
        $this->isDeleting = true;

        try {
            DB::transaction(function () {
                // All related records will be deleted via cascade
                $this->customer->delete();
            });

            // Log successful deletion
            logger()->info('Customer deleted successfully', [
                'customer_id' => $this->customerId,
                'customer_name' => $this->customerName,
                'tenant_id' => tenant('id'),
            ]);

            session()->flash('success', "Customer \"{$this->customerName}\" successfully deleted.");

            $this->dispatch('customer-deleted', customerId: $this->customerId);
            $this->dispatch('close-modal');

            $this->redirectRoute('customers.index', navigate: true);
        } catch (\Exception $e) {
            $this->isDeleting = false;

            session()->flash('error', 'Failed to delete customer. Please try again.');

            logger()->error('Customer deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $this->customerId,
            ]);

            $this->dispatch('deletion-failed');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.tenancy.customers.delete');
    }
}
