<?php

namespace App\Livewire\Roles;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public string $tenant = '';
    public string $name = '';
    public Collection $permissions;
    public array $rolePermissions = [];

    public function mount()
    {
        $this->tenant = tenant('id');
        $this->permissions = Permission::all();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'tenant' => ['numeric'],
            'rolePermissions.*' => ['required'],
        ]);

        $role = Role::create($validated);
        $role->syncPermissions($this->rolePermissions);
        session()->flash('success', 'Role successfully created.');

        $this->redirectRoute('roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.roles.create');
    }
}
