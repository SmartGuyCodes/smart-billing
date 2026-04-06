# Smart Billing Package

Smart Billing Package is a modular Laravel package that brings advanced billing and invoicing capabilities to your Laravel applications. Designed for flexibility, automation, and seamless integration, it empowers you to manage billing workflows with ease.

## Features

- Rapid invoice generation with customizable templates
- Automated recurring billing and subscription management
- Customer and product catalog support
- Payment tracking, reporting, and analytics
- Integration-ready REST API for external systems
- Secure, scalable, and extensible Laravel architecture

## Installation

1. **Require the package via Composer:**

    ```bash
    composer require smartguycodes/smart-billing
    ```

2. **Publish and run migrations:**

    ```bash
    php artisan vendor:publish --tag="smart-billing-migrations"
    php artisan migrate
    ```

3. **Publish configuration (optional):**

    ```bash
    php artisan vendor:publish --tag="smart-billing-config"
    ```

4. **Install frontend dependencies (if using UI components):**

    ```bash
    npm install
    npm run build
    ```

## Usage

- Integrate billing features into your Laravel app using provided facades, models, and API endpoints.
- Access the billing dashboard at `/billing` (if enabled).
- Refer to the [documentation](https://github.com/SmartGuyCodes/smart-billing/blob/main/docs/index.md) for advanced usage and API details.

## Contributing

Contributions are welcome! Please open issues or submit pull requests to help improve the package.

## License

This package is open-sourced software licensed under the MIT License.
