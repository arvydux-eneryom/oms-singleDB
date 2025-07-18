<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function systemUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSubdomainAttribute()
    {
        $centralDomain = config('tenancy.central_domains')[0];
        // Remove the central domain part to get the subdomain
        if (str_ends_with($this->domain, '.' . $centralDomain)) {
            return substr($this->domain, 0, -strlen('.' . $centralDomain));
        }
        return $this->domain;
    }
}
