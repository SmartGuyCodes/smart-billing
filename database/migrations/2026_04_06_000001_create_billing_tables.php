<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------------------------
        // Plans
        // -------------------------------------------------------------------------
        Schema::create(config('billing.tables.plans', 'billing_plans'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('KES');
            $table->enum('interval', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('trial_days')->default(0);
            $table->json('features')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // -------------------------------------------------------------------------
        // Subscriptions
        // -------------------------------------------------------------------------
        Schema::create(config('billing.tables.subscriptions', 'billing_subscriptions'), function (Blueprint $table) {
            $table->id();
            $table->morphs('billable');          // billable_id + billable_type
            $table->foreignId('plan_id')->constrained(config('billing.tables.plans', 'billing_plans'));
            $table->enum('status', ['trialing', 'active', 'past_due', 'suspended', 'cancelled', 'expired'])
                ->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('last_failed_at')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type', 'status']);
            $table->index('current_period_end');
        });

        // -------------------------------------------------------------------------
        // Invoices
        // -------------------------------------------------------------------------
        Schema::create(config('billing.tables.invoices', 'billing_invoices'), function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->morphs('billable');
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained(config('billing.tables.subscriptions', 'billing_subscriptions'))
                ->nullOnDelete();
            $table->enum('status', ['draft', 'unpaid', 'paid', 'void', 'overdue'])->default('unpaid');
            $table->decimal('amount', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 10)->default('KES');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
            $table->index('status');
        });

        // -------------------------------------------------------------------------
        // Transactions
        // -------------------------------------------------------------------------
        Schema::create(config('billing.tables.transactions', 'billing_transactions'), function (Blueprint $table) {
            $table->id();

            // API User Layer fields (as specified)
            $table->string('reference_no')->unique();           // System-generated TXN-XXXXX
            $table->string('invoice_number')->nullable();        // INV-YYYY-XXXXX
            $table->string('client_no')->nullable();             // Client identifier
            $table->string('account_number');                    // Mobile, bank, card number
            $table->enum('account_type', ['mobile', 'bank', 'card'])->default('mobile');
            $table->enum('transaction_type', ['income', 'expense'])->default('income');

            // Amounts
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('KES');

            // Status & Gateway
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('driver')->default('mpesa');         // mpesa | stripe | flutterwave
            $table->string('gateway_ref')->nullable();           // Gateway transaction ID
            $table->string('checkout_request_id')->nullable();   // M-Pesa CheckoutRequestID
            $table->text('failure_reason')->nullable();
            $table->text('description')->nullable();

            // Relations
            $table->morphs('billable');
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained(config('billing.tables.subscriptions', 'billing_subscriptions'))
                ->nullOnDelete();
            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained(config('billing.tables.invoices', 'billing_invoices'))
                ->nullOnDelete();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
            $table->index('status');
            $table->index('driver');
            $table->index('checkout_request_id');
            $table->index('created_at');
        });

        // -------------------------------------------------------------------------
        // Payment Methods
        // -------------------------------------------------------------------------
        Schema::create(config('billing.tables.payment_methods', 'billing_payment_methods'), function (Blueprint $table) {
            $table->id();
            $table->morphs('billable');
            $table->enum('type', ['mobile', 'bank', 'card'])->default('mobile');
            $table->string('account_number');        // Masked value
            $table->string('label')->nullable();     // e.g. "My Safaricom"
            $table->string('driver')->default('mpesa');
            $table->boolean('is_default')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------------------------
        // Dunning Logs
        // -------------------------------------------------------------------------
        Schema::create(config('billing.tables.dunning_logs', 'billing_dunning_logs'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')
                ->constrained(config('billing.tables.subscriptions', 'billing_subscriptions'))
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('status');           // attempted | succeeded | failed
            $table->text('notes')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('billing.tables.dunning_logs', 'billing_dunning_logs'));
        Schema::dropIfExists(config('billing.tables.payment_methods', 'billing_payment_methods'));
        Schema::dropIfExists(config('billing.tables.transactions', 'billing_transactions'));
        Schema::dropIfExists(config('billing.tables.invoices', 'billing_invoices'));
        Schema::dropIfExists(config('billing.tables.subscriptions', 'billing_subscriptions'));
        Schema::dropIfExists(config('billing.tables.plans', 'billing_plans'));
    }
};