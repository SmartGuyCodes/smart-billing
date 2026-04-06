# Installation Guide

Follow these steps to install and set up the **Smart Billing** Laravel package in your Laravel application.

## 1. Install via Composer

Require the package using Composer:

```bash
composer require smartguycodes/smart-billing
```

## 2. Publish Configuration (If Available)

If the package provides a configuration file, publish it:

```bash
php artisan vendor:publish --tag="smart-billing-config"
```

Check the `config/smart-billing.php` file and adjust settings as needed.

## 3. Run Migrations (If Provided)

If the package includes database migrations, run:

```bash
php artisan migrate
```

## 4. Environment Variables

If required, copy any relevant variables from `vendor/smartguycodes/smart-billing/.env.example` to your main `.env` file and update as needed.

## 5. Frontend Assets (If Applicable)

If the package provides frontend assets, publish and build them:

```bash
php artisan vendor:publish --tag="smart-billing-assets"
npm install
npm run build
```

Or, for development:

```bash
npm run dev
```

## 6. Usage

Refer to the package documentation for usage instructions, available features, and integration examples.

---

**Notes:**

- Ensure your Laravel application meets the package's PHP and Laravel version requirements.
- For advanced configuration (mail, queue, etc.), review the published config file and `.env` variables.
- If you encounter issues with frontend assets, try rebuilding with `npm run build` or `npm run dev`.
- For troubleshooting or more details, consult the package's README or documentation.
