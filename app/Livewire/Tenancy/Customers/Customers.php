<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Livewire\Component;

class Customers extends Component
{
    public $customerIdToDelete = null;

    public function deleteCustomer()
    {
        $customer = Customer::find($this->customerIdToDelete);
        if ($customer) {
            $customer->delete();
            $this->customerIdToDelete = null;
            session()->flash('message', 'Customer deleted successfully.');
            $this->dispatch('close-modal');
        }
    }

    public function render()
    {
        return view('livewire.tenancy.customers.index', [
            // pass customers data here
        ]);
    }
}
