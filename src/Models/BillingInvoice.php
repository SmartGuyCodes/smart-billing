<?php

namespace SmartGuyCodes\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SmartGuyCodes\Billing\Support\ReferenceGenerator;

// use SmartGuyCodes\Support\ReferenceGenerator;

class BillingInvoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'      => 'float',
        'tax'         => 'float',
        'total'       => 'float',
        'line_items'  => 'array',
        'due_date'    => 'date',
        'paid_at'     => 'datetime',
        'sent_at'     => 'datetime',
    ];

    public function getTable(): string
    {
        return config('billing.tables.invoices', 'billing_invoices');
    }

    protected static function booted(): void
    {
        static::creating(function (self $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = ReferenceGenerator::generateInvoice();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function billable(): MorphTo        { return $this->morphTo(); }
    public function subscription(): BelongsTo { return $this->belongsTo(BillingSubscription::class, 'subscription_id'); }
    public function transactions(): HasMany   { return $this->hasMany(BillingTransaction::class, 'invoice_id'); }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePaid($q)     { return $q->where('status', 'paid'); }
    public function scopeUnpaid($q)   { return $q->where('status', 'unpaid'); }
    public function scopeOverdue($q)  { return $q->unpaid()->where('due_date', '<', now()); }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPaid(): bool    { return $this->status === 'paid'; }
    public function isOverdue(): bool { return !$this->isPaid() && $this->due_date->isPast(); }

    public function markPaid(BillingTransaction $transaction): bool
    {
        return $this->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function getFormattedTotalAttribute(): string
    {
        return config('billing.currency_symbol') . ' ' . number_format($this->total, 2);
    }
}