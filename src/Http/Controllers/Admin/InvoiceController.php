<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingInvoice;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = BillingInvoice::with(['billable', 'subscription.plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('invoice_number', 'like', "%{$q}%");
        }

        $invoices = $query->paginate(config('billing.admin.per_page', 25))->withQueryString();

        $stats = [
            'paid_total'   => BillingInvoice::paid()->sum('total'),
            'unpaid_total' => BillingInvoice::unpaid()->sum('total'),
            'overdue'      => BillingInvoice::overdue()->count(),
        ];

        return view('billing::admin.invoices.index', compact('invoices', 'stats'));
    }

    public function show(BillingInvoice $invoice)
    {
        $invoice->load(['billable', 'subscription.plan', 'transactions']);
        return view('billing::admin.invoices.show', compact('invoice'));
    }

    public function downloadPdf(BillingInvoice $invoice)
    {
        // PDF generation — requires dompdf or similar in the host app
        $invoice->load(['billable', 'subscription.plan']);
        $html = view('billing::admin.invoices.pdf', compact('invoice'))->render();

        // Return as download if dompdf available, else render HTML
        return response($html)->header('Content-Type', 'text/html');
    }
}