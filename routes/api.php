<?php

use Illuminate\Support\Facades\Route;
use SmartGuyCodes\Billing\Http\Controllers\Api;

// ─────────────────────────────────────────────────────────────────────────────
// M-Pesa Callbacks (no auth — Safaricom calls these directly)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('api/billing/webhooks')
    ->name('billing.webhooks.')
    ->group(function () {
        Route::post('mpesa/callback',     [Api\WebhookController::class, 'mpesaCallback'])->name('mpesa.callback');
        Route::post('mpesa/timeout',      [Api\WebhookController::class, 'mpesaTimeout'])->name('mpesa.timeout');
        Route::post('mpesa/validation',   [Api\WebhookController::class, 'mpesaValidation'])->name('mpesa.validation');
        Route::post('mpesa/confirmation', [Api\WebhookController::class, 'mpesaConfirmation'])->name('mpesa.confirmation');
    });

// ─────────────────────────────────────────────────────────────────────────────
// Billing API (auth:sanctum or your preferred guard)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('api/billing')
    ->middleware(['api', 'auth:sanctum'])
    ->name('billing.api.')
    ->group(function () {

        // Initiate payment (STK push etc.)
        Route::post('pay',                [Api\PaymentController::class, 'initiate'])->name('pay');
        Route::get('transactions/{ref}',  [Api\PaymentController::class, 'status'])->name('transaction.status');
        Route::post('transactions/{ref}/verify', [Api\PaymentController::class, 'verify'])->name('transaction.verify');

        // Subscriptions
        Route::get('subscription',        [Api\SubscriptionController::class, 'current'])->name('subscription.current');
        Route::post('subscribe',          [Api\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::delete('subscription',     [Api\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::post('subscription/resume',[Api\SubscriptionController::class, 'resume'])->name('subscription.resume');

        // Plans
        Route::get('plans',               [Api\PlanController::class, 'index'])->name('plans.index');

        // Invoices
        Route::get('invoices',            [Api\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}',  [Api\InvoiceController::class, 'show'])->name('invoices.show');

    });