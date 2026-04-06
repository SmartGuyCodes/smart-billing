# Getting Started with Smart Billing

Welcome to **Smart Billing**! This guide will help Laravel developers quickly integrate and use the Smart Billing package in their Laravel applications.

## Requirements

- **PHP**: 8.3 or higher
- **Laravel**: 9.x, 10.x, or 11.x (check your composer.json for compatibility)
- **Composer**: Latest version

## Installation

Install the package via Composer:

```bash
composer require smartguycodes/smart-billing
```

> **Tip:** Run `composer update` if you encounter dependency issues.

## Publishing Configuration

Publish the package configuration (if available):

```bash
php artisan vendor:publish --tag="smart-billing-config"
```

This will create a `config/billing.php` file for customization.

## Environment Setup

Add the required environment variables to your `.env` file:

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
    ...
```

## Running Migrations

If the package provides migrations, run:

```bash
php artisan migrate
```

## Basic Usage

Import and use the Smart Billing facade or service class:

```php
use SmartGuyCodes\SmartBilling\Facades\SmartBilling;

// Example: Create an invoice
$invoice = SmartBilling::createInvoice([
    'customer_id' => 1,
    'amount' => 100.00,
    // ...other fields
]);
```

## Testing

To run package tests:

```bash
php artisan test --compact
```

## Troubleshooting

- Ensure your `.env` variables are set correctly.
- Clear config cache if changes are not reflected:
  
  ```bash
    php artisan config:clear
  ```

- For Vite or asset issues, run:
  
  ```bash
    npm run dev
  ```

## Further Documentation

- [API Reference](./api.md)
- [Advanced Configuration](./configuration.md)

---

For more help, check the package's README or open an issue on GitHub.