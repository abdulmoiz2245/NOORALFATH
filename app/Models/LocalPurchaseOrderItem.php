<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalPurchaseOrderItem extends Model
{
    protected $table = 'local_purchase_order_items';
    protected $fillable = [
        'local_purchase_order_id',
        'product_id',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'tax_rate',
        'total_price_before_tax',
        'total_price_after_tax',
    ];

    public function localPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(LocalPurchaseOrder::class, 'local_purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
