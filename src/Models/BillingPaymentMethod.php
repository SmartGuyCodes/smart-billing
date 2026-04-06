<?php

namespace SmartGuyCodes\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BillingPaymentMethod extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'meta'       => 'array',
    ];

    public function getTable(): string
    {
        return config('billing.tables.payment_methods', 'billing_payment_methods');
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mask the account number for display.
     * e.g. 0712345678 → 0712****78
     */
    public function getMaskedAccountAttribute(): string
    {
        $n = $this->account_number;
        if (strlen($n) <= 4) return $n;
        return substr($n, 0, 4) . str_repeat('*', strlen($n) - 6) . substr($n, -2);
    }
}