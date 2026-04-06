# Smart Billing

**Drop-in Laravel billing for the Kenyan SaaS market.**

Zero plumbing. Define your plans, configure M-Pesa, and your billing loop is sorted — subscriptions, STK Push, callbacks, dunning, renewal reminders, and an admin UI all included.

---

## Architecture

```bash
    smart-billing/
        ├── src/
        │   ├── BillingServiceProvider.php      # Auto-discovery, publishes, registers
        │   ├── Concerns/Billable.php           # Trait for your User model
        │   ├── Contracts/PaymentDriver.php     # Interface for all payment drivers
        │   ├── Drivers/MpesaDriver.php         # Full Daraja API — STK Push, C2B, Refunds
        │   ├── Facades/Billing.php
        │   ├── Services/
        │   │   ├── BillingManager.php          # Extensible driver manager
        │   │   ├── PaymentService.php          # Orchestrates initiate → callback → renew
        │   │   ├── SubscriptionService.php     # subscribe, cancel, resume, changePlan
        │   │   └── DunningService.php          # Retry logic, suspension, cancellation
        │   ├── Models/                         # 5 Eloquent models with scopes & helpers
        │   ├── Http/Controllers/               # Admin UI + REST API + Webhook handlers
        │   ├── Events/                         # 8 events for your listeners
        │   ├── Notifications/RenewalReminder
        │   ├── Console/Commands/               # billing:install/renewals/dunning/reminders
        │   └── Support/PaymentResult.php       # Typed result value object
        ├── config/billing.php                  # All configurable — driver, dunning, reminders
        ├── database/migrations/                # 6 tables created in one migration
        ├── resources/views/admin/              # Dark-themed admin UI — dashboard, all CRUD
        └── routes/web.php + api.php
```

- **Drivers**: M-Pesa (Daraja) out of the box, with a simple interface to add Stripe, Flutterwave, etc.
- **Transactions**: Every payment attempt is stored with a unique reference, status, and metadata
- **Subscriptions**: Plan-based billing with trial periods, grace periods, and dunning support
- **Admin UI**: View transactions, manage plans, and monitor subscription statuses
- **Scheduler**: Daily commands to process renewals, send reminders, and handle dunning

---

## Requirements

| | |
|---|---|
| PHP | ^8.2 |
| Laravel | ^11 \| ^12 |
| M-Pesa | Daraja API credentials |

---

## Installation

```bash
composer require smartguycodes/billing
```

```bash
php artisan billing:install
```

This publishes the config, runs migrations, and optionally seeds sample plans.

---

## Configuration

### .env

```bash
    # Core
    BILLING_DRIVER=mpesa
    BILLING_CURRENCY=KES
    BILLING_CURRENCY_SYMBOL=KSh

    # M-Pesa (Daraja)
    MPESA_ENV=sandbox                   # sandbox | production
    MPESA_CONSUMER_KEY=your_key
    MPESA_CONSUMER_SECRET=your_secret
    MPESA_SHORTCODE=174379
    MPESA_PASSKEY=your_passkey
    MPESA_TYPE=paybill                  # paybill | till
    MPESA_CALLBACK_URL=https://yourapp.com/api/billing/webhooks/mpesa/callback
    MPESA_TIMEOUT_URL=https://yourapp.com/api/billing/webhooks/mpesa/timeout

    # Admin UI
    BILLING_ADMIN_PREFIX=billing-admin

    # Invoice
    BILLING_INVOICE_PREFIX=INV
    BILLING_COMPANY_NAME="My SaaS Co"
    BILLING_REF_PREFIX=TXN
```

### Billable Model

Add the `Billable` trait to your `User` model:

```php
    use SmartGuyCodes\Billing\Concerns\Billable;

    class User extends Authenticatable
    {
        use Billable;
    }
```

---

## Usage

### Initiate an M-Pesa STK Push

```php
    use SmartGuyCodes\Billing\Facades\Billing;

    $result = $user->charge([
        'amount'           => 999,
        'account_number'   => '0712345678',   // Customer phone
        'account_type'     => 'mobile',
        'transaction_type' => 'income',
        'description'      => 'Starter Plan - March 2025',
    ]);

    if ($result->isPending()) {
        // STK Push sent — wait for callback
        $checkoutId = $result->checkoutRequestId;
    }
```

### Subscribe to a Plan

```php
    // By slug
    $plan = BillingPlan::where('slug', 'pro')->first();
    $subscription = $user->subscribeTo($plan);

    // With trial
    $subscription = $user->subscribeTo($plan, ['trial_days' => 14]);
```

### Check Subscription Status

```php
    $user->isSubscribed();           // bool
    $user->onTrial();                // bool
    $user->subscribedTo('pro');      // bool

    $sub = $user->activeSubscription();
    $sub->daysUntilRenewal();        // int
    $sub->isActive();                // bool
    $sub->onGracePeriod();           // bool
```

### Cancel & Resume

```php
    $user->cancelSubscription();                    // At period end
    $user->cancelSubscription(immediately: true);   // Right now

    // Resume within grace period
    $sub->resume();
```

### Verify a Transaction

```php
    $transaction = BillingTransaction::where('reference_no', 'TXN-...')->first();
    $result = app(\SmartGuyCodes\Billing\Services\PaymentService::class)->verify($transaction);
```

---

## Transaction Data Model

Every transaction stores the full API User Layer:

| Field | Description |
|---|---|
| `reference_no` | System-generated — `TXN-250101120000-ABCD` |
| `invoice_number` | `INV-2025-00042` |
| `client_no` | Your identifier for the customer |
| `account_number` | Mobile number, bank account, or card number |
| `account_type` | `mobile` \| `bank` \| `card` |
| `transaction_type` | `income` \| `expense` |
| `amount` | Float |
| `currency` | e.g. `KES` |
| `status` | `pending` → `completed` \| `failed` \| `refunded` |
| `driver` | `mpesa` \| `stripe` \| `flutterwave` |
| `gateway_ref` | M-Pesa receipt number or Stripe charge ID |

---

## M-Pesa Webhook URLs

Register these in the Safaricom Daraja portal:

| Type | URL |
|---|---|
| STK Callback | `https://yourapp.com/api/billing/webhooks/mpesa/callback` |
| STK Timeout | `https://yourapp.com/api/billing/webhooks/mpesa/timeout` |
| C2B Validation | `https://yourapp.com/api/billing/webhooks/mpesa/validation` |
| C2B Confirmation | `https://yourapp.com/api/billing/webhooks/mpesa/confirmation` |

---

## REST API

All routes are prefixed `/api/billing` and require `auth:sanctum`.

```bash
    POST   /api/billing/pay
    GET    /api/billing/transactions/{ref}
    POST   /api/billing/transactions/{ref}/verify

    GET    /api/billing/plans
    POST   /api/billing/subscribe
    GET    /api/billing/subscription
    DELETE /api/billing/subscription
    POST   /api/billing/subscription/resume

    GET    /api/billing/invoices
    GET    /api/billing/invoices/{id}
```

### Example: Initiate Payment

```bash
    POST /api/billing/pay
    Authorization: Bearer {token}
    Content-Type: application/json

    {
    "amount": 999,
    "account_number": "0712345678",
    "account_type": "mobile",
    "transaction_type": "income",
    "description": "Pro Plan Renewal"
    }
```

Response:

```json
    {
    "success": true,
    "status": "pending",
    "reference": "TXN-250401120000-XKQZ",
    "checkout_request_id": "ws_CO_...",
    "message": "Enter your M-Pesa PIN to complete the payment."
    }
```

---

## Admin Interface

Visit `/{BILLING_ADMIN_PREFIX}` (default: `/billing-admin`).

The middleware `billing.admin` gates access. By default it checks `$user->is_admin`. Override by publishing the middleware:

```bash
    php artisan vendor:publish --tag=billing-views
```

Or define a gate in your `AuthServiceProvider`:

```php
    Gate::define('billing-admin', fn($user) => $user->role === 'superadmin');
```

---

## Scheduler

Add to `routes/console.php` (Laravel 11+):

```php
    use Illuminate\Support\Facades\Schedule;

    Schedule::command('billing:renewals')->dailyAt('00:05');
    Schedule::command('billing:dunning')->dailyAt('02:00');
    Schedule::command('billing:reminders')->dailyAt('08:00');
```

| Command | Purpose |
|---|---|
| `billing:renewals` | Charge subscriptions due today |
| `billing:dunning` | Retry failed payments per dunning config |
| `billing:reminders` | Send renewal reminder notifications |
| `billing:install` | One-time setup wizard |

---

## Adding a Custom Driver

```php
    // In a ServiceProvider
    app('billing')->extend('mpesa', function ($app) {
        return new MpesaDriver(config('billing.drivers.mpesa'));
    });
```

Your driver must implement `SmartGuyCodes\Billing\Contracts\PaymentDriver`:

```php
    interface PaymentDriver
    {
        public function initiate(array $payload): PaymentResult;
        public function verify(string $reference): PaymentResult;
        public function handleCallback(array $payload): PaymentResult;
        public function refund(string $reference, float $amount): PaymentResult;
        public function driverName(): string;
        public function validateConfig(): void;
    }
```

---

## Events

Listen to these events in your application:

```php
    use SmartGuyCodes\Billing\Events\PaymentCompleted;

    Event::listen(PaymentCompleted::class, function ($event) {
        // $event->transaction — BillingTransaction
        // $event->result      — PaymentResult
        Log::info("Payment received: {$event->transaction->reference_no}");
    });
```

| Event | Fired When |
|---|---|
| `PaymentInitiated` | STK Push sent |
| `PaymentCompleted` | Callback confirms success |
| `PaymentFailed` | Callback confirms failure |
| `SubscriptionCreated` | User subscribes to a plan |
| `SubscriptionRenewed` | Subscription period renewed |
| `SubscriptionCancelled` | Subscription cancelled |
| `SubscriptionSuspended` | Dunning suspension |
| `DunningAttempted` | Retry payment attempted |

---

## Plans Config (Optional)

If you prefer config-driven plans over database plans, set `BILLING_PLANS_SOURCE=config` and define in `config/billing.php`:

```php
'plans' => [
    [
        'name'       => 'Starter',
        'slug'       => 'starter',
        'price'      => 999,
        'interval'   => 'monthly',
        'trial_days' => 14,
        'features'   => ['Up to 5 users', '10GB storage'],
    ],
],
```

---

## License

MIT