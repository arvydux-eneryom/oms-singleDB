<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\View\View;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $customerIdToDelete = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updated($property)
    {
        if ($property === 'search') {
            dd(5454);
            $this->resetPage();
        }
    }

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

    public function render(): View
    {
        $search = $this->search;

        $customers = Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.tenancy.customers.index', [
            'customers' => $customers,
        ]);
    }
}
