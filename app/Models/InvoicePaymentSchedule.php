<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoicePaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_number',
        'description',
        'percentage',
        'amount',
        'due_date',
        'status',
        'paid_amount',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_payment_schedule_id');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date < now();
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid' || $this->paid_amount >= $this->amount;
    }
}
