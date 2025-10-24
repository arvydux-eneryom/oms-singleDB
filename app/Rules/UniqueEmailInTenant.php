<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueEmailInTenant implements Rule
{
    protected $tenantId;

    protected $userIdToIgnore;

    public function __construct($tenantId, $userIdToIgnore = null)
    {
        $this->tenantId = $tenantId;
        $this->userIdToIgnore = $userIdToIgnore;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table('users')
            ->join('tenant_user', 'users.id', '=', 'tenant_user.user_id')
            ->where('tenant_user.tenant_id', $this->tenantId)
            ->where('users.email', $value);

        if ($this->userIdToIgnore) {
            $query->where('users.id', '!=', $this->userIdToIgnore);
        }

        return $query->count() === 0;
    }

    public function message()
    {
        return 'The email has already been taken by another user in this tenant.';
    }
}
