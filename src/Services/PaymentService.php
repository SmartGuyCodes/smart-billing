<?php

namespace SmartGuyCodes\Billing\Services;

use Illuminate\Database\Eloquent\Model;
use SmartGuyCodes\Billing\Events\PaymentCompleted;
use SmartGuyCodes\Billing\Events\PaymentFailed;
use SmartGuyCodes\Billing\Events\PaymentInitiated;
use SmartGuyCodes\Billing\Models\BillingInvoice;
use SmartGuyCodes\Billing\Models\BillingTransaction;
use SmartGuyCodes\Billing\Support\PaymentResult;
use SmartGuyCodes\Billing\Support\ReferenceGenerator;

class PaymentService
{
    public function __construct(protected BillingManager $manager) {}

    /**
     * Initiate a payment for a billable model.
     *
     * $payload = [
     *   'phone'          => '0712345678',       // for M-Pesa
     *   'amount'         => 999.00,
     *   'account_number' => '0712345678',       // mobile/bank/card
     *   'account_type'   => 'mobile',           // mobile|bank|card
     *   'transaction_type' => 'income',         // income|expense
     *   'description'    => 'Starter plan - Jan 2025',
     *   'driver'         => 'mpesa',            // optional override
     *   'subscription_id'=> 3,                  // optional
     *   'invoice_id'     => 7,                  // optional
     *   'client_no'      => 'CLT-001',          // optional client identifier
     * ]
     */
    public function initiate(Model $billable, array $payload): PaymentResult
    {
        $driver  = $this->manager->driver($payload['driver'] ?? null);
        $reference = ReferenceGenerator::generate();

        // Create the transaction record before calling the gateway
        $transaction = BillingTransaction::create([
            'reference_no'      => $reference,
            'invoice_number'    => $payload['invoice_number'] ?? ReferenceGenerator::generateInvoice(),
            'client_no'         => $payload['client_no'] ?? (string) $billable->getKey(),
            'account_number'    => $payload['account_number'],
            'account_type'      => $payload['account_type'] ?? 'mobile',
            'transaction_type'  => $payload['transaction_type'] ?? 'income',
            'amount'            => $payload['amount'],
            'currency'          => $payload['currency'] ?? config('billing.currency'),
            'status'            => 'pending',
            'driver'            => $driver->driverName(),
            'description'       => $payload['description'] ?? null,
            'billable_id'       => $billable->getKey(),
            'billable_type'     => get_class($billable),
            'subscription_id'   => $payload['subscription_id'] ?? null,
            'invoice_id'        => $payload['invoice_id'] ?? null,
            'meta'              => [],
        ]);

        $result = $driver->initiate(array_merge($payload, ['reference' => $reference]));

        // Update transaction with gateway response
        $transaction->update([
            'checkout_request_id' => $result->checkoutRequestId,
            'gateway_ref'         => $result->gatewayRef,
            'status'              => $result->status,
            'meta'                => $result->raw,
        ]);

        event(new PaymentInitiated($transaction, $result));

        return $result;
    }

    /**
     * Handle inbound callback from gateway.
     */
    public function handleCallback(array $payload, string $driver = null): PaymentResult
    {
        $driverInstance = $this->manager->driver($driver);
        $result = $driverInstance->handleCallback($payload);

        // Find the transaction by checkout_request_id or gateway_ref
        $transaction = BillingTransaction::where('checkout_request_id', $result->reference)
            ->orWhere('gateway_ref', $result->gatewayRef)
            ->first();

        if (!$transaction) {
            return $result; // Unknown transaction — log and ignore
        }

        if ($result->isCompleted()) {
            $transaction->markCompleted($result->gatewayRef ?? '', $result->raw);

            // Mark invoice paid if linked
            if ($transaction->invoice_id) {
                BillingInvoice::find($transaction->invoice_id)?->markPaid($transaction);
            }

            // Renew subscription if linked
            if ($transaction->subscription_id) {
                $transaction->subscription?->renew();
            }

            event(new PaymentCompleted($transaction, $result));

        } elseif ($result->isFailed()) {
            $transaction->markFailed($result->message ?? 'Payment failed', $result->raw);
            event(new PaymentFailed($transaction, $result));
        }

        return $result;
    }

    /**
     * Verify / poll a transaction's current status.
     */
    public function verify(BillingTransaction $transaction): PaymentResult
    {
        $driver = $this->manager->driver($transaction->driver);
        $identifier = $transaction->checkout_request_id ?? $transaction->gateway_ref ?? $transaction->reference_no;

        return $driver->verify($identifier);
    }

    /**
     * Issue a refund for a completed transaction.
     */
    public function refund(BillingTransaction $transaction, ?float $amount = null): PaymentResult
    {
        $refundAmount = $amount ?? $transaction->amount;
        $driver = $this->manager->driver($transaction->driver);
        $result = $driver->refund($transaction->gateway_ref ?? $transaction->reference_no, $refundAmount);

        if ($result->success) {
            $transaction->update(['status' => 'refunded', 'meta' => array_merge($transaction->meta ?? [], ['refund' => $result->raw])]);
        }

        return $result;
    }
}