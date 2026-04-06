# Usage Guide

Welcome to **Smart Billing**! This guide will help Laravel developers get started and make the most of the features provided by this package.

## Requirements

- PHP 8.3 or higher
- Laravel 11.x or higher

## Installation

Install via Composer:

```bash
composer require smartguycodes/smart-billing
```

Publish the configuration file (if available):

```bash
php artisan vendor:publish --tag=smart-billing-config
```

Run migrations:

```bash
php artisan migrate
```

## Basic Usage

### 1. Creating an Invoice

```php
use SmartGuyCodes\SmartBilling\Models\Invoice;

$invoice = Invoice::create([
    'customer_id' => $customerId,
    'amount' => 100.00,
    'due_date' => now()->addDays(30),
]);
```

### 2. Adding Invoice Items

```php
$invoice->items()->create([
    'description' => 'Product Name',
    'quantity' => 2,
    'unit_price' => 50.00,
]);
```

### 3. Marking as Paid

```php
$invoice->markAsPaid();
```

## Advanced Features

- **Customizable Invoice Templates**
- **Automated Email Reminders**
- **Payment Gateway Integration**

Refer to the [full documentation](./) for details on advanced configuration and customization.

## Testing

Run tests to ensure everything works as expected:

```bash
php artisan test
```

## Support

For issues or feature requests, please open an issue on GitHub.

---

Happy billing!