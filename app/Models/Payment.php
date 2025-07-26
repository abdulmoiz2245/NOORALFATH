<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'invoice_payment_schedule_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'status',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentSchedule(): BelongsTo
    {
        return $this->belongsTo(InvoicePaymentSchedule::class, 'invoice_payment_schedule_id');
    }
}
