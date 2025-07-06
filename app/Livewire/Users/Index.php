<?php

namespace App\Livewire\Users;

use App\Models\Tenant;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $tenantId;

    public function mount()
    {
        $this->tenantId = tenant('id');
    }

    public function delete(int $id)
    {
        $user = User::findOrFail($id);

        $user->tenants()->detach();

        $user->delete();

        session()->flash('success', 'User successfully deleted.');
    }



    public function render()
    {
        $tenant = Tenant::findOrFail($this->tenantId);

        $users = $tenant
            ->users()
            ->with('roles', 'permissions')
            ->paginate(5);

        return view('livewire.users.index', compact('users'));
    }
}
