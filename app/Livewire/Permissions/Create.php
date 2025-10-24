<?php

namespace App\Livewire\Permissions;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Create extends Component
{
    public string $tenant = '';

    public string $name = '';

    public function mount()
    {
        $this->tenant = tenant('id');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')],
            'tenant' => ['numeric'],
        ]);

        Permission::create($validated);

        session()->flash('success', 'Permission successfully created.');

        $this->redirectRoute('permissions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.permissions.create');
    }
}
