<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\View\View as RenderView;

class View extends Component
{
    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer->load([
            'customerPhones',
            'customerEmails',
            'customerContacts',
            'customerServiceAddresses',
            'customerBillingAddresses',
        ]);
    }

    public function render(): RenderView
    {
        return view('livewire.tenancy.customers.view');
    }
}
