<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public $tenantId;

    public function mount()
    {
        $this->tenantId = tenant('id');
    }

    public function delete(int $id)
    {
        Permission::findOrFail($id)->delete();

        session()->flash('success', 'Permission successfully deleted.');
    }

    public function render()
    {
        $permissions = Permission::all();

        return view('livewire.permissions.index', compact('permissions'));
    }
}
