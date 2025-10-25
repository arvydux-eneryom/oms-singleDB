<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubdomainPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any subdomains.
     */
    public function viewAny(User $user): bool
    {
        // Users can view subdomains in their system
        return true;
    }

    /**
     * Determine if the user can view the subdomain.
     */
    public function view(User $user, Domain $domain): bool
    {
        // Can view if belongs to user's system
        return $domain->system_id === $user->system_id;
    }

    /**
     * Determine if the user can create subdomains.
     */
    public function create(User $user): bool
    {
        // Tenant users can create subdomains
        return $user->is_tenant;
    }

    /**
     * Determine if the user can update the subdomain.
     */
    public function update(User $user, Domain $domain): bool
    {
        // Can update if belongs to user's system
        return $domain->system_id === $user->system_id;
    }

    /**
     * Determine if the user can delete the subdomain.
     */
    public function delete(User $user, Domain $domain): bool
    {
        // Can delete if belongs to user's system and user is tenant
        return $domain->system_id === $user->system_id && $user->is_tenant;
    }
}
