<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingPlan;
use SmartGuyCodes\Billing\Services\SubscriptionService;

// ─────────────────────────────────────────────────────────────────────────────

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = $request->user()
            ->billingInvoices()
            ->latest()
            ->paginate(15);

        return response()->json($invoices);
    }

    public function show(Request $request, int $invoiceId): JsonResponse
    {
        $invoice = $request->user()
            ->billingInvoices()
            ->findOrFail($invoiceId);

        return response()->json($invoice->load('transactions'));
    }
}