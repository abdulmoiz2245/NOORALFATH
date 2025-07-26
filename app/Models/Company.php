<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'signature',
        'tax_number',
        'registration_number',
        'bank_details',
    ];

    protected $casts = [
        'bank_details' => 'array',
    ];

    public function getBankDetailsAttribute($value)
    {
        return json_decode($value, true);
    }
    
}
