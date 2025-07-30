<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'trn_number',
        'phone',
        'address',
        'services',
        'products',
        'payment_status',
        'total_amount_owed',
        'contact_person',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'services' => 'array',
        'products' => 'array',
        'total_amount_owed' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
