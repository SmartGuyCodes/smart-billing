\
# Changelog

## Overview

This changelog documents the features developed for the Smart Billing package, providing context and details for each release. The package was created to streamline and automate billing processes for modern SaaS and subscription-based applications, focusing on flexibility, extensibility, and developer experience.

---

## [1.0.0] – 2026-04-06

### Added – Initial Release

- Project initiation
- Billing configuration files
- Core billing service
- Payment driver implementation
- Billing Manager for handling multiple drivers
- Payment Service (orchestrates payments, creates transactions, invokes drivers)
- SubscriptionService for managing plan subscriptions
- DunningService for handling failed payment retries
- Events for the billing package
- Subscription events
- Billing Facade & `Billable` trait for the User model
- Database migrations for all billing tables
- Routes, Controllers & Middleware

### Changed

- SubscriptionService modified and improved
- `README.md` refactored

---

[1.0.0]: https://github.com/SmartGuyCodes/smart-billing/commits/main/

#### Context

The Smart Billing package was developed to address the need for a robust, developer-friendly billing solution that integrates seamlessly with modern web applications. The focus was on providing a comprehensive set of features out-of-the-box, while allowing for customization to fit unique business requirements. The package is designed to be framework-agnostic, with adapters for popular PHP frameworks and clear documentation for integration.

---

## [Unreleased]

- Planned features include support for additional payment gateways, advanced analytics, and multi-currency billing.

---

*For upgrade instructions and migration guides, refer to the official documentation.*