# How It Works

The **Smart Billing** package streamlines billing operations by providing a set of tools and conventions for managing invoices, payments, and customer subscriptions. Below is an overview of how the package operates within your application.

## 1. Installation & Setup

- Install the package via Composer.
- Publish the configuration file and run the provided migrations to set up the necessary database tables.
- Configure your billing providers and environment variables as needed.

## 2. Core Concepts

- **Invoices:** Automatically generated for billable events or can be created manually via the API.
- **Payments:** Linked to invoices and tracked for status (pending, paid, failed).
- **Subscriptions:** Managed through the package, supporting recurring billing cycles and plan changes.
- **Customers:** Associated with your application's user model, storing billing information and payment methods.

## 3. Usage Workflow

1. **Creating Customers:**  
    When a new user registers, a corresponding billing customer record is created.

2. **Generating Invoices:**  
    Invoices are generated automatically for subscription renewals or can be triggered manually for one-time charges.

3. **Processing Payments:**  
    Payments are processed using the configured payment gateway. Webhooks update invoice and payment statuses in real time.

4. **Managing Subscriptions:**  
    Users can subscribe, upgrade, downgrade, or cancel plans. The package handles proration and renewal logic.

5. **Notifications:**  
    Customers receive email notifications for new invoices, payment receipts, and subscription changes.

## 4. Extending Functionality

- You can listen for package events (e.g., `InvoicePaid`, `SubscriptionCancelled`) to trigger custom business logic.
- The package provides customizable views for invoice and subscription management.

## 5. API Endpoints

- RESTful endpoints are available for managing invoices, payments, and subscriptions.
- All endpoints are protected by authentication middleware.

## 6. Testing

- The package includes factories and test helpers to facilitate integration testing in your application.

---

For detailed configuration and advanced usage, refer to the [full documentation](./).
