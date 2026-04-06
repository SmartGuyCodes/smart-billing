<?php

use Illuminate\Support\Facades\Route;
use SmartGuyCodes\Billing\Http\Controllers\Admin;

$prefix     = config('billing.admin.prefix', 'billing-admin');
$middleware = config('billing.admin.middleware', ['web', 'auth', 'billing.admin']);

// ─────────────────────────────────────────────────────────────────────────────
// Admin Routes
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix($prefix)
    ->middleware($middleware)
    ->name('billing.admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Plans
        Route::get('plans/add', [Admin\PlanController::class, 'add'])->name('plans.add');
        Route::resource('plans', Admin\PlanController::class);
        Route::patch('plans/{plan}/toggle', [Admin\PlanController::class, 'toggle'])->name('plans.toggle');

        // Subscriptions
        Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::patch('subscriptions/{subscription}/cancel', [Admin\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::patch('subscriptions/{subscription}/resume', [Admin\SubscriptionController::class, 'resume'])->name('subscriptions.resume');

        // Transactions
        Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/verify', [Admin\TransactionController::class, 'verify'])->name('transactions.verify');
        Route::post('transactions/{transaction}/refund', [Admin\TransactionController::class, 'refund'])->name('transactions.refund');

        // Invoices
        Route::get('invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        // Settings
        Route::get('settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    });