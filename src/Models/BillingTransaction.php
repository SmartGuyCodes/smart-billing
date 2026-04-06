<?php

namespace SmartGuyCodes\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * BillingTransaction
 *
 * @property string $reference_no     System-generated (TXN-XXXXXX)
 * @property string $invoice_number   INV-YYYY-XXXXX
 * @property string $client_no        Foreign key to billable model
 * @property string $account_number   Mobile no, bank account, card no
 * @property string $account_type     mobile | bank | card
 * @property string $transaction_type expense | income
 * @property float  $amount
 * @property string $currency
 * @property string $status           pending | completed | failed | refunded | cancelled
 * @property string $driver           mpesa | stripe | flutterwave
 * @property string $gateway_ref      Gateway transaction ID
 * @property string $checkout_request_id  M-Pesa CheckoutRequestID
 * @property array  $meta             Raw gateway payload
 */
class BillingTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'amount'     => 'float',
        'meta'       => 'array',
        'paid_at'    => 'datetime',
        'failed_at'  => 'datetime',
    ];

    public function getTable(): string
    {
        return config('billing.tables.transactions', 'billing_transactions');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BillingSubscription::class, 'subscription_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeCompleted($q)  { return $q->where('status', 'completed'); }
    public function scopePending($q)    { return $q->where('status', 'pending'); }
    public function scopeFailed($q)     { return $q->where('status', 'failed'); }
    public function scopeIncome($q)     { return $q->where('transaction_type', 'income'); }
    public function scopeExpense($q)    { return $q->where('transaction_type', 'expense'); }
    public function scopeByDriver($q, string $driver) { return $q->where('driver', $driver); }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFormattedAmountAttribute(): string
    {
        return config('billing.currency_symbol') . ' ' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'pending'   => 'warning',
            'failed'    => 'danger',
            'refunded'  => 'info',
            'cancelled' => 'secondary',
            default     => 'secondary',
        };
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function markCompleted(string $gatewayRef, array $meta = []): bool
    {
        return $this->update([
            'status'      => 'completed',
            'gateway_ref' => $gatewayRef,
            'paid_at'     => now(),
            'meta'        => array_merge($this->meta ?? [], $meta),
        ]);
    }

    public function markFailed(string $reason, array $meta = []): bool
    {
        return $this->update([
            'status'    => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'meta'      => array_merge($this->meta ?? [], $meta),
        ]);
    }

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
}