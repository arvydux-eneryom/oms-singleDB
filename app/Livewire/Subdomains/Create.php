<?php

namespace App\Livewire\Subdomains;

use App\Models\Tenant;
use Livewire\Component;
use Illuminate\View\View;

class Create extends Component
{
    public string $subdomain = '';
    public string $companyName = '';

    public function mount()
    {}

    public function save(): void
    {
        $validated = $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'subdomain' => ['required',  'regex:/^[a-zA-Z0-9]+$/', 'max:8', 'unique:domains,subdomain'],
        ]);

        $tenant = Tenant::create([
            'companyName' => $validated['companyName'],
        ]);

        $tenant->domains()->create([
            'name' => $validated['companyName'],
            'subdomain' => $validated['subdomain'],
            'domain' => $validated['subdomain'] . '.' . config('tenancy.central_domains')[0],
            'system_id' => auth()->user()->system_id,
        ]);

        auth()->user()->update(['is_tenant' => true]);
        auth()->user()->tenants()->attach($tenant->id);

        session()->flash('success', 'Subdomain successfully created.');

        // TODO: update later
        $this->redirectRoute('subdomains.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.subdomains.create');
    }
}
