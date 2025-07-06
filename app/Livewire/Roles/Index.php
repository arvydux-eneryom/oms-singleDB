<?php

namespace App\Livewire\Roles;

use Livewire\Component;
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
        Role::findOrFail($id)->delete();

        session()->flash('success', 'Role successfully deleted.');
    }

    public function render()
    {
        $roles = Role::all();

        return view('livewire.roles.index', compact('roles'));
    }
}
