<?php

namespace SmartGuyCodes\Billing\Services;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use SmartGuyCodes\Billing\Contracts\PaymentDriver;
use SmartGuyCodes\Billing\Drivers\MpesaDriver;

/**
 * BillingManager
 *
 * Resolves payment drivers. Extend with your own driver:
 *   app('billing')->extend('mypay', fn($app) => new MyPayDriver($config));
 */
class BillingManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('billing.default_driver', 'mpesa');
    }

    protected function createMpesaDriver(): PaymentDriver
    {
        $config = config('billing.drivers.mpesa', []);
        return new MpesaDriver($config);
    }

    protected function createStripeDriver(): PaymentDriver
    {
        $config = config('billing.drivers.stripe', []);
        // Placeholder — implement StripeDriver similarly to MpesaDriver
        throw new InvalidArgumentException("Stripe driver not yet implemented. Extend BillingManager.");
    }

    protected function createFlutterwaveDriver(): PaymentDriver
    {
        $config = config('billing.drivers.flutterwave', []);
        throw new InvalidArgumentException("Flutterwave driver not yet implemented.");
    }

    /**
     * Get a configured driver instance.
     */
    public function driver($driver = null): PaymentDriver
    {
        return parent::driver($driver);
    }
}