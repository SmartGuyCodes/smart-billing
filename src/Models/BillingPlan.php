<?php

namespace SmartGuyCodes\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price'       => 'float',
        'features'    => 'array',
        'metadata'    => 'array',
        'is_active'   => 'boolean',
        'is_popular'  => 'boolean',
        'trial_days'  => 'integer',
    ];

    public function getTable(): string
    {
        return config('billing.tables.plans', 'billing_plans');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BillingSubscription::class, 'plan_id');
    }

    public function scopeActive($q) { return $q->where('is_active', true); }

    public function getFormattedPriceAttribute(): string
    {
        return config('billing.currency_symbol') . ' ' . number_format($this->price, 2) . '/' . $this->interval;
    }
}