<?php

namespace App\Livewire\Roles;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public string $tenant = '';
    public Role $role;
    public string $name = '';
    public Collection $permissions;
    public array $rolePermissions = [];

    public function mount(Role $role)
    {
        $this->tenant = tenant('id');
        $this->role = $role;
        $this->name = $role->name;
        $this->permissions = Permission::all();
        $this->rolePermissions = $role->permissions()->pluck('name')->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role),
            ],
            'tenant' => ['numeric']
        ]);

        $this->role->update([
            'name'         => $this->name,
        ]);

        $this->role->syncPermissions($this->rolePermissions);

        session()->flash('success', 'Role successfully updated.');

        $this->redirectRoute('roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.roles.edit');
    }
}
