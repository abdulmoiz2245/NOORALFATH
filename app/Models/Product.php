<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'unit',
        'category',
        'is_service',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_service' => 'boolean',
        'is_active' => 'boolean',
    ];
}
