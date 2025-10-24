<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public array $roles = [];

    public ?string $userRoles = '';

    public array $notAssignedSubdomains = [];

    public ?int $assignedSubdomain = null;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->roles = Role::all()->pluck('name', 'id')->toArray();
        $this->userRoles = $this->user->roles->pluck('name', 'name')->first();
        $this->notAssignedSubdomains = $this->getNotAssignedSubdomains();
        $this->assignedSubdomain = null;
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
            'userRoles' => 'string',
            'userRoles.*' => 'exists:roles,',
            'assignedSubdomain' => [
                'nullable',
            ],
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        DB::table('model_has_roles')->where('model_id', $this->user->id)->delete();

        $this->user->assignRole($this->userRoles);

        if ($this->assignedSubdomain) {
            DB::table('tenant_user')->updateOrInsert(
                ['tenant_id' => $this->assignedSubdomain, 'user_id' => $this->user->id],
            );
            $this->notAssignedSubdomains = $this->getNotAssignedSubdomains();

            session()->flash('success', 'Domain assigned successfully.');
        }

        session()->flash('success', 'User successfully updated.');

        //    $this->redirectRoute('users.index', navigate: true);
    }

    public function unassignDomain($tenantId, $userId): void
    {
        DB::table('tenant_user')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->delete();

        $this->notAssignedSubdomains = $this->getNotAssignedSubdomains();

        session()->flash('success', 'Domain unassigned successfully.');
    }

    public function render()
    {
        return view('livewire.users.edit');
    }

    private function getNotAssignedSubdomains(): array
    {
        return DB::table('domains')

            ->whereIn('tenant_id', $this->getNotAssignedTenants())
            ->where('system_id', auth()->user()->system_id)
            ->pluck('domain', 'tenant_id')->toArray();
    }

    private function getNotAssignedTenants(): array
    {
        return DB::table('tenants')
            ->whereNotIn('id', function ($query) {
                $query->select('tenant_id')
                    ->from('tenant_user')
                    ->where('user_id', $this->user->id);
            })
            ->pluck('id')
            ->toArray();
    }
}
