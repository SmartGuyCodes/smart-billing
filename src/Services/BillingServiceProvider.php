<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Driver
    |--------------------------------------------------------------------------
    | The default driver used when processing payments.
    | Options: 'mpesa', 'stripe', 'flutterwave'
    */
    'default_driver' => getenv('BILLING_DRIVER') ?: 'mpesa',

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => getenv('BILLING_CURRENCY') ?: 'KES',
    'currency_symbol' => getenv('BILLING_CURRENCY_SYMBOL') ?: 'KSh',

    /*
    |--------------------------------------------------------------------------
    | Payment Drivers
    |--------------------------------------------------------------------------
    | Configure each payment gateway. Only the active driver's credentials
    | need to be set.
    */
    'drivers' => [

        'mpesa' => [
            'environment'       => getenv('MPESA_ENV') ?: 'sandbox', // sandbox | production
            'consumer_key'      => getenv('MPESA_CONSUMER_KEY'),
            'consumer_secret'   => getenv('MPESA_CONSUMER_SECRET'),
            'shortcode'         => getenv('MPESA_SHORTCODE'),
            'passkey'           => getenv('MPESA_PASSKEY'),
            'type'              => getenv('MPESA_TYPE') ?: 'paybill', // paybill | till
            'callback_url'      => getenv('MPESA_CALLBACK_URL'),
            'timeout_url'       => getenv('MPESA_TIMEOUT_URL'),
            'c2b_validation_url'=> getenv('MPESA_C2B_VALIDATION_URL'),
            'c2b_confirmation_url' => getenv('MPESA_C2B_CONFIRMATION_URL'),
            'initiator_name'    => getenv('MPESA_INITIATOR_NAME'),
            'initiator_password'=> getenv('MPESA_INITIATOR_PASSWORD'),
            'timeout_seconds'   => 60,
            'retry_attempts'    => 3,
            'retry_delay'       => 300, // seconds between retries
        ],

        'stripe' => [
            'key'    => getenv('STRIPE_KEY'),
            'secret' => getenv('STRIPE_SECRET'),
            'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET'),
        ],

        'flutterwave' => [
            'public_key'  => getenv('FLW_PUBLIC_KEY'),
            'secret_key'  => getenv('FLW_SECRET_KEY'),
            'secret_hash' => getenv('FLW_SECRET_HASH'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    | Define your plans here or load from the database.
    | 'source' => 'config' | 'database'
    */
    'plans_source' => getenv('BILLING_PLANS_SOURCE') ?: 'database',

    'plans' => [
        // Example plan — these are used when plans_source = 'config'
        // [
        //     'name'           => 'Starter',
        //     'slug'           => 'starter',
        //     'price'          => 999,
        //     'interval'       => 'monthly', // monthly | yearly | weekly
        //     'trial_days'     => 14,
        //     'features'       => ['Up to 5 users', '10GB storage', 'Email support'],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing Intervals
    |--------------------------------------------------------------------------
    */
    'intervals' => ['daily', 'weekly', 'monthly', 'yearly'],

    /*
    |--------------------------------------------------------------------------
    | Dunning Configuration
    |--------------------------------------------------------------------------
    | How to handle failed payments and expired subscriptions.
    */
    'dunning' => [
        'enabled'             => true,
        'max_retries'         => 3,
        'retry_intervals'     => [1, 3, 7], // days after failure to retry
        'grace_period_days'   => 3,         // days after expiry before suspension
        'cancel_after_days'   => 30,        // days after suspension before cancellation
    ],

    /*
    |--------------------------------------------------------------------------
    | Renewal Reminders
    |--------------------------------------------------------------------------
    */
    'reminders' => [
        'enabled' => true,
        'days_before' => [7, 3, 1], // send reminders N days before renewal
        'channels'    => ['mail', 'database'], // notification channels
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'prefix'       => getenv('BILLING_INVOICE_PREFIX') ?: 'INV',
        'logo'         => getenv('BILLING_INVOICE_LOGO'),
        'company_name' => getenv('BILLING_COMPANY_NAME') ?: config('app.name'),
        'company_address' => getenv('BILLING_COMPANY_ADDRESS'),
        'company_phone'   => getenv('BILLING_COMPANY_PHONE'),
        'footer_note'     => getenv('BILLING_INVOICE_FOOTER') ?: 'Thank you for your business.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reference Number
    |--------------------------------------------------------------------------
    */
    'reference' => [
        'prefix' => getenv('BILLING_REF_PREFIX') ?: 'TXN',
        'length' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Interface
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'enabled'    => true,
        'prefix'     => getenv('BILLING_ADMIN_PREFIX') ?: 'billing-admin',
        'middleware' => ['web', 'auth', 'billing.admin'],
        'per_page'   => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Billable Model
    |--------------------------------------------------------------------------
    | The model that can subscribe to plans and make payments.
    */
    'billable_model' => getenv('BILLING_MODEL') ?: 'App\\Models\\User',
    
    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'transactions'    => 'billing_transactions',
        'subscriptions'   => 'billing_subscriptions',
        'plans'           => 'billing_plans',
        'invoices'        => 'billing_invoices',
        'payment_methods' => 'billing_payment_methods',
        'dunning_logs'    => 'billing_dunning_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'tolerance' => 300, // seconds
        'secret'    => getenv('BILLING_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'payment_success' => true,
        'payment_failed'  => true,
        'subscription_renewed' => true,
        'subscription_cancelled' => true,
        'trial_ending' => true,
    ],

];