<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'notes',
        'terms',
        'is_recurring',
        'recurring_frequency',
        'next_recurring_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'next_recurring_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
