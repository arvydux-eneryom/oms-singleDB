<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    protected $casts = [
        'status' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
