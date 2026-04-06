<?php

namespace SmartGuyCodes\Billing\Facades;

use Illuminate\Support\Facades\Facade;
use SmartGuyCodes\Billing\Services\BillingManager;

/**
 * @method static \SmartGuyCodes\Billing\Contracts\PaymentDriver driver(string $driver = null)
 * @method static \SmartGuyCodes\Billing\Support\PaymentResult initiate(\Illuminate\Database\Eloquent\Model $billable, array $payload)
 * @method static \SmartGuyCodes\Billing\Support\PaymentResult handleCallback(array $payload, string $driver = null)
 * @method static \SmartGuyCodes\Billing\Support\PaymentResult verify(\SmartGuyCodes\Billing\Models\BillingTransaction $transaction)
 * @method static \SmartGuyCodes\Billing\Models\BillingSubscription subscribe(\Illuminate\Database\Eloquent\Model $billable, mixed $plan, array $options = [])
 *
 * @see BillingManager
 */
class Billing extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'billing';
    }
}