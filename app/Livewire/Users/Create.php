<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Component;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public string $tenant = '';
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public User $user;
    public string $userRoles = '';
    public array $roles = [];

    public function mount()
    {
        $this->tenant = tenant('id');
        $this->roles = Role::all()->pluck('name', 'id')->toArray();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'tenant' => ['numeric'],
            'userRoles' => 'string',
            'userRoles.*' => 'exists:roles,',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($this->user = User::create($validated))));

        DB::table('model_has_roles')->where('model_id', $this->user->id)->delete();

        $this->user->assignRole($this->userRoles);

        $this->user->tenants()->attach($this->tenant);

        session()->flash('success', 'User successfully created.');

        $this->redirectRoute('users.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.users.create');
    }
}
