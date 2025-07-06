<?php

namespace App\Livewire\Permissions;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public string $tenant = '';
    public Permission $permission;
    public string $name = '';

    public function mount(Permission $permission)
    {
        $this->tenant = tenant('id');
        $this->permission = $permission;
        $this->name = $permission->name;
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->ignore($this->permission),
            ],
            'tenant' => ['numeric']
        ]);

        $this->permission->update([
            'name'         => $this->name,
        ]);

        session()->flash('success', 'Permission successfully updated.');

        $this->redirectRoute('permissions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.permissions.edit');
    }
}
