<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lpo extends Model
{
    protected $fillable = [
        'vendor_id',
        'lpo_number',
        'issue_date',
        'trn_number',
        'subject',
        'terms',
        'subtotal',
        'total_tax',
        'total_before_tax',
        'total_after_tax',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LpoItem::class);
    }
}
