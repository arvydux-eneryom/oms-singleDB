<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Livewire\Component;

class Delete extends Component
{
    public $customerId;
    public $customerIdToDelete = null;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
    }

    public function deleteCustomer()
    {
        dd(5454);
        $customer = \App\Models\Customer::find($this->customerIdToDelete);
        if ($customer) {
            $customer->delete();
            $this->customerIdToDelete = null;
            session()->flash('message', 'Customer deleted successfully.');
            $this->dispatch('close-modal');
        }
    }

    public function render()
    {
        return view('livewire.tenancy.customers.delete');
    }
}
