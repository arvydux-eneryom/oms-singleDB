<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public string $tenant = '';
    public User $user;
    public string $name = '';
    public string $email = '';
    public array $roles = [];
    public ?string $userRoles = '';

    public function mount(User $user)
    {
        $this->tenant = tenant('id');
        $this->user = $user;

        $this->name = $user->name;
        $this->email = $user->email;

        $this->roles = Role::all()->pluck('name', 'id')->toArray();
        $this->userRoles = $this->user->roles->pluck('name','name')->first();
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($this->user),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user),
            ],
            'tenant' => ['numeric'],
            'userRoles' => 'string',
            'userRoles.*' => 'exists:roles,',
        ]);


        $this->user->update([
            'name'         => $this->name,
            'email'        => $this->email,
        ]);

        DB::table('model_has_roles')->where('model_id', $this->user->id)->delete();

        $this->user->assignRole($this->userRoles);

        session()->flash('success', 'User successfully updated.');

        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.users.edit');
    }
}
