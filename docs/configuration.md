# Configuration

To get started with **Smart Billing**, follow these steps to configure the package in your Laravel application.

## 1. Publish the Configuration File

After installing the package via Composer, publish the configuration file:

```bash
php artisan vendor:publish --provider="SmartBilling\SmartBillingServiceProvider" --tag="config"
```

This will create a `config/billing.php` file where you can customize the package settings.

## 2. Environment Variables

Update your `.env` file with any required credentials or settings. For example:

```bash
    # SMART BILLING CONFIG
    ## 1. Basics
    BILLING_PLANS_SOURCE=database
    BILLING_DASHBOARD_ENABLED=true
    BILLING_DASHBOARD_PATH=/billing
    BILLING_DASHBOARD_MIDDLEWARE=web,auth
    BILLING_MODEL=
    BILLING_DRIVER=mpesa
    BILLING_CURRENCY=KES
    ## 2a. M-Pesa
    MPESA_ENV=sandbox
    MPESA_CONSUMER_KEY=
    MPESA_CONSUMER_SECRET=
    MPESA_SHORTCODE=
    MPESA_PASSKEY=
    MPESA_TYPE=CustomerPayBillOnline
    MPESA_CALLBACK_URL=http://localhost/billing/callback
    MPESA_TIMEOUT_URL=http://localhost/billing/timeout
    MPESA_C2B_VALIDATION_URL=http://localhost/billing/validation
    MPESA_C2B_CONFIRMATION_URL=http://localhost/billing/confirmation
    MPESA_INITIATOR_NAME=
    MPESA_INITIATOR_PASSWORD=
    ## 2b. Co-Op MPESA STK Push
    COOP_ENV=sandbox
    COOP_OPERATOR_CODE=
    COOP_AUTH_TOKEN=
    COOP_STK_PUSH_URL=http://localhost/billing/coop/stk-push
    COOP_STK_PUSH_CALLBACK_URL=http://localhost/billing/coop/stk-push/callback
    COOP_STK_TXN_STATUS_URL=http://localhost/billing/coop/stk-push/status
    ## 3. PAYPAL(Add as needed)
    PAYPAL_CLIENT_ID=
    PAYPAL_CLIENT_SECRET=
    PAYPAL_MODE=sandbox
    ## 4. Pesapal(Add as needed)
    PESAPAL_ENV=sandbox
    PESAPAL_CONSUMER_KEY=
    PESAPAL_CONSUMER_SECRET=
    PESAPAL_CALLBACK_URL=http://localhost/billing/pesapal/callback
    ## 5. Stripe(Add as needed)
    STRIPE_KEY=
    STRIPE_SECRET=
    STRIPE_WEBHOOK_SECRET=
    ## 6. Flutterwave(Add as needed)
    FLW_PUBLIC_KEY=
    FLW_SECRET_KEY=
    FLW_ENCRYPTION_HASH=
```

Refer to the published config file for all available options.

## 3. Configuration Options

Open `config/billing.php` to review and adjust options such as:

- **API credentials**
- **Default currency**
- **Webhook URLs**
- **Feature toggles**

Example:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Driver
    |--------------------------------------------------------------------------
    | The default driver used when processing payments.
    | Options: 'mpesa', 'coop', 'airtel', 'pesapal', 'paypal', 'stripe', 'flutterwave'
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

        'coop' =>[
            'environment' => getenv('COOP_ENV') ?: 'sandbox', // sandbox | production
            'operator_code' => getenv('COOP_OPERATOR_CODE'),
            'auth_token' => getenv('COOP_AUTH_TOKEN'),
            'stk_push_url' => getenv('COOP_STK_PUSH_URL'),
            'stk_push_callback_url' => getenv('COOP_STK_PUSH_CALLBACK_URL'),
            'stk_txn_status_url' => getenv('COOP_STK_TXN_STATUS_URL'),
        ],
        
        'airtel' => [
            'environment'   => getenv('AIRTEL_ENV') ?: 'sandbox', // sandbox | production
            'client_id'    => getenv('AIRTEL_CLIENT_ID'),
            'client_secret'=> getenv('AIRTEL_CLIENT_SECRET'),
            'shortcode'    => getenv('AIRTEL_SHORTCODE'),
            'callback_url' => getenv('AIRTEL_CALLBACK_URL'),
        ],

        'paypal' => [
            'client_id'     => getenv('PAYPAL_CLIENT_ID'),
            'secret'        => getenv('PAYPAL_SECRET'),
            'settings'      => [
                'mode' => getenv('PAYPAL_MODE') ?: 'sandbox', // sandbox | live
                'http.ConnectionTimeOut' => 30,
                'log.LogEnabled' => true,
                'log.FileName' =>   '../storage/logs/paypal.log',
                'log.LogLevel' => 'ERROR',
            ],
        ],

        'pesapal' => [
            'environment'   => getenv('PESAPAL_ENV') ?: 'sandbox', // sandbox | production
            'consumer_key'  => getenv('PESAPAL_CONSUMER_KEY'),
            'consumer_secret' => getenv('PESAPAL_CONSUMER_SECRET'),
            'callback_url'  => getenv('PESAPAL_CALLBACK_URL'),
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
    // other options...
];
```

## 4. Caching & Optimization

If your configuration changes, clear the config cache:

```bash
php artisan config:cache
```

## 5. Advanced Usage

For advanced configuration, such as customizing services or extending package functionality, consult the package documentation or source code comments.

---

**Tip:** Always review the configuration file after updating the package, as new options may be added in future releases.