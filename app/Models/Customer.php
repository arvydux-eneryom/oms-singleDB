<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company',
        'address',
        'country',
        'city',
        'postcode',
        'latitude',
        'longitude',
        'status',
        'tenant_id',
        'status',
    ];
    public function customerEmails(): HasMany
    {
        return $this->hasMany(CustomerEmail::class);
    }

    public function customerPhones(): HasMany
    {
        return $this->hasMany(CustomerPhone::class);
    }

    public function customerContacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function customerServiceAddresses(): HasMany
    {
        return $this->hasMany(CustomerServiceAddress::class);
    }

    public function customerBillingAddresses(): HasMany
    {
        return $this->hasMany(CustomerBillingAddress::class);
    }

    public function customerAddresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
