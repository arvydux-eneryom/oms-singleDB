<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public User $user;

    public string $userRoles = '';

    public array $roles = [];

    public array $notAssignedSubdomains = [];

    public ?int $assignedSubdomain = null;

    public function mount()
    {
        $this->roles = Role::all()->pluck('name', 'id')->toArray();
        $this->notAssignedSubdomains = $this->getNotAssignedSubdomains();
        $this->assignedSubdomain = null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'userRoles' => 'string',
            'userRoles.*' => 'exists:roles,',
            'assignedSubdomain' => [
                'nullable',
            ],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($this->user = User::create($validated + ['is_tenant' => true, 'system_id' => auth()->user()->system_id]))));

        DB::table('model_has_roles')->where('model_id', $this->user->id)->delete();

        $this->user->assignRole($this->userRoles);

        if ($this->assignedSubdomain) {
            DB::table('tenant_user')->updateOrInsert(
                ['tenant_id' => $this->assignedSubdomain, 'user_id' => $this->user->id],
            );
            $this->notAssignedSubdomains = $this->getNotAssignedSubdomains();

            session()->flash('success', 'Domain assigned successfully.');
        }

        session()->flash('success', 'User successfully created.');

        $this->redirectRoute('users.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.users.create');
    }

    private function getNotAssignedSubdomains(): array
    {
        return DB::table('domains')
            ->where('system_id', auth()->user()->system_id)
            ->pluck('domain', 'tenant_id')->toArray();
    }
}
