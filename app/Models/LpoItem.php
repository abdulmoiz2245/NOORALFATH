<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpoItem extends Model
{
    protected $fillable = [
        'lpo_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'total_price',
    ];

    public function lpo(): BelongsTo
    {
        return $this->belongsTo(Lpo::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
