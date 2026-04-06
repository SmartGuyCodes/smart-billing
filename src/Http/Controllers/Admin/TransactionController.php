<?php
    namespace SmartGuyCodes\Billing\Http\Controllers\Admin;

    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller;
    use SmartGuyCodes\Billing\Models\BillingTransaction;
    use SmartGuyCodes\Billing\Services\PaymentService;

    class TransactionController extends Controller
    {
        public function __construct(protected PaymentService $paymentService) {}

        public function index(Request $request)
        {
            $query = BillingTransaction::with('billable')
                ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('driver')) {
                $query->where('driver', $request->driver);
            }
            if ($request->filled('account_type')) {
                $query->where('account_type', $request->account_type);
            }
            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }
            if ($request->filled('search')) {
                $q = $request->search;
                $query->where(function ($builder) use ($q) {
                    $builder->where('reference_no', 'like', "%{$q}%")
                        ->orWhere('invoice_number', 'like', "%{$q}%")
                        ->orWhere('client_no', 'like', "%{$q}%")
                        ->orWhere('account_number', 'like', "%{$q}%")
                        ->orWhere('gateway_ref', 'like', "%{$q}%");
                });
            }

            $transactions = $query->paginate(config('billing.admin.per_page', 25))
                ->withQueryString();

            $summary = [
                'total'     => BillingTransaction::sum('amount'),
                'completed' => BillingTransaction::completed()->sum('amount'),
                'pending'   => BillingTransaction::pending()->count(),
                'failed'    => BillingTransaction::failed()->count(),
            ];

            return view('billing::admin.transactions.index', compact('transactions', 'summary'));
        }

        public function show(BillingTransaction $transaction)
        {
            $transaction->load('billable', 'subscription.plan', 'invoice');
            return view('billing::admin.transactions.show', compact('transaction'));
        }

        public function verify(BillingTransaction $transaction)
        {
            $result = $this->paymentService->verify($transaction);

            return redirect()
                ->route('billing.admin.transactions.show', $transaction)
                ->with('success', "Verification: {$result->message}");
        }

        public function refund(Request $request, BillingTransaction $transaction)
        {
            $request->validate(['amount' => 'nullable|numeric|min:0.01']);

            $result = $this->paymentService->refund($transaction, $request->amount);

            return redirect()
                ->route('billing.admin.transactions.show', $transaction)
                ->with($result->success ? 'success' : 'error', $result->message);
        }
    }