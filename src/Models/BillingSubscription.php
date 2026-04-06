<?php
    
    namespace SmartGuyCodes\Billing\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Carbon\Carbon;
    
    class BillingSubscription extends Model
    {
        use SoftDeletes;
    
        protected $guarded = [];
    
        protected $casts = [
            'trial_ends_at'   => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end'   => 'datetime',
            'cancelled_at'    => 'datetime',
            'ends_at'         => 'datetime',
            'meta'            => 'array',
        ];
    
        public function getTable(): string
        {
            return config('billing.tables.subscriptions', 'billing_subscriptions');
        }
    
        // -------------------------------------------------------------------------
        // Relationships
        // -------------------------------------------------------------------------
        public function billable(): MorphTo    { return $this->morphTo(); }
        public function plan(): BelongsTo     { return $this->belongsTo(BillingPlan::class, 'plan_id'); }
        public function transactions(): HasMany { return $this->hasMany(BillingTransaction::class, 'subscription_id'); }
        public function invoices(): HasMany    { return $this->hasMany(BillingInvoice::class, 'subscription_id'); }
    
        // -------------------------------------------------------------------------
        // Scopes
        // -------------------------------------------------------------------------
    
        public function scopeActive($q)    { return $q->where('status', 'active'); }
        public function scopeTrialing($q)  { return $q->where('status', 'trialing'); }
        public function scopePastDue($q)   { return $q->where('status', 'past_due'); }
        public function scopeCancelled($q) { return $q->where('status', 'cancelled'); }
        public function scopeExpiring($q, int $days = 7)
        {
            return $q->active()->whereBetween('current_period_end', [now(), now()->addDays($days)]);
        }
    
        // -------------------------------------------------------------------------
        // Status Helpers
        // -------------------------------------------------------------------------
    
        public function isActive(): bool    { return $this->status === 'active'; }
        public function isTrialing(): bool  { return $this->status === 'trialing'; }
        public function isPastDue(): bool   { return $this->status === 'past_due'; }
        public function isCancelled(): bool { return $this->status === 'cancelled'; }
        public function onGracePeriod(): bool
        {
            return $this->isCancelled() && $this->ends_at && $this->ends_at->isFuture();
        }
    
        public function daysUntilRenewal(): int
        {
            return (int) now()->diffInDays($this->current_period_end, false);
        }
    
        // -------------------------------------------------------------------------
        // Actions
        // -------------------------------------------------------------------------
    
        public function cancel(bool $immediately = false): bool
        {
            if ($immediately) {
                return $this->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now(),
                    'ends_at'      => now(),
                ]);
            }
    
            return $this->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
                'ends_at'      => $this->current_period_end,
            ]);
        }
    
        public function resume(): bool
        {
            if (!$this->onGracePeriod()) {
                throw new \RuntimeException('Subscription is not within its grace period.');
            }
    
            return $this->update([
                'status'       => 'active',
                'cancelled_at' => null,
                'ends_at'      => null,
            ]);
        }
    
        public function renew(): bool
        {
            $plan   = $this->plan;
            $start  = $this->current_period_end ?? now();
            $end    = $this->calculateNextPeriodEnd($start, $plan->interval);
    
            return $this->update([
                'status'               => 'active',
                'current_period_start' => $start,
                'current_period_end'   => $end,
                'retry_count'          => 0,
            ]);
        }
    
        protected function calculateNextPeriodEnd(Carbon $from, string $interval): Carbon
        {
            return match($interval) {
                'daily'   => $from->copy()->addDay(),
                'weekly'  => $from->copy()->addWeek(),
                'monthly' => $from->copy()->addMonth(),
                'yearly'  => $from->copy()->addYear(),
                default   => $from->copy()->addMonth(),
            };
        }
    }