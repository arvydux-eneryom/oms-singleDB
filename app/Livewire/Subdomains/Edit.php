<?php

namespace App\Livewire\Subdomains;

use App\Models\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Domain $subdomain;
    public string $domain = '';
    //TODO : rename $subdomainText to something better
    public string $subdomainText = '';
    public string $name = '';

    public function mount(Domain $subdomain)
    {
        $this->subdomain = $subdomain;
        $this->subdomainText = $subdomain->subdomain;
        $this->name = $subdomain->name;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'subdomainText' => [
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                'max:8',
                Rule::unique('domains', 'subdomain')->ignore($this->subdomainText),
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        $this->subdomain->update([
            'name' => $validated['name'],
            'domain' => $this->convertSubdomainToDomain($validated['subdomainText']),
        ]);

       // dd($this->subdomain, $this->subdomain->subdomain);

        // Update the related tenant's name
        $this->subdomain->tenant->name = $this->name;
        $this->subdomain->tenant->save();

        session()->flash('success', 'Subdomain successfully updated.');
    }

    public function saveAndClose()
    {
        $this->save();
        $this->redirectRoute('subdomains.index', navigate: true);
    }

    public function unassignDomain($tenantId, $userId): void
    {
        DB::table('tenant_user')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->delete();

        session()->flash('success', 'User revoked successfully.');
    }

    public function render()
    {
        return view('livewire.subdomains.edit');
    }

    private function convertSubdomainToDomain(string $subdomain): string
    {
        // Convert subdomain to full domain using the central domain
        return $subdomain . '.' . config('tenancy.central_domains')[0];
    }
}
