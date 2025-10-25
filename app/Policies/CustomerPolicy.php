<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        // Users can only view customers in their tenant
        return $user->is_tenant;
    }

    /**
     * Determine if the user can view the customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Ensure customer belongs to user's tenant
        return $customer->tenant_id === tenant('id');
    }

    /**
     * Determine if the user can create customers.
     */
    public function create(User $user): bool
    {
        // Only tenant users can create customers
        return $user->is_tenant;
    }

    /**
     * Determine if the user can update the customer.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Can only update customers in their tenant
        return $customer->tenant_id === tenant('id');
    }

    /**
     * Determine if the user can delete the customer.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Can only delete customers in their tenant
        return $customer->tenant_id === tenant('id');
    }

    /**
     * Determine if the user can restore the customer.
     */
    public function restore(User $user, Customer $customer): bool
    {
        // Can only restore customers in their tenant
        return $customer->tenant_id === tenant('id');
    }

    /**
     * Determine if the user can permanently delete the customer.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        // Only allow force delete in same tenant
        return $customer->tenant_id === tenant('id');
    }
}
