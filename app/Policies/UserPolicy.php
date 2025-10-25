<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // System users with proper permissions can view users
        return $user->hasPermissionTo('view users') || $user->is_tenant;
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Can view if same system or has permission
        return $user->system_id === $model->system_id || $user->hasPermissionTo('view users');
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        // Must be tenant user or have create permission
        return $user->is_tenant || $user->hasPermissionTo('create users');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Can update if same system or has permission
        return $user->system_id === $model->system_id || $user->hasPermissionTo('edit users');
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself, must be same system or have permission
        return $user->id !== $model->id &&
            ($user->system_id === $model->system_id || $user->hasPermissionTo('delete users'));
    }
}
