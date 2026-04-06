<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function index(Request $request)
    {
        $query = BillingSubscription::with(['billable', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHasMorph('billable', '*', function ($builder) use ($q) {
                $builder->where('email', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $subscriptions = $query->paginate(config('billing.admin.per_page', 25))->withQueryString();

        $stats = [
            'active'   => BillingSubscription::active()->count(),
            'trialing' => BillingSubscription::trialing()->count(),
            'past_due' => BillingSubscription::pastDue()->count(),
            'cancelled'=> BillingSubscription::cancelled()->count(),
        ];

        return view('billing::admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    public function show(BillingSubscription $subscription)
    {
        $subscription->load(['billable', 'plan', 'transactions', 'invoices']);
        return view('billing::admin.subscriptions.show', compact('subscription'));
    }

    public function cancel(Request $request, BillingSubscription $subscription)
    {
        $immediately = $request->boolean('immediately', false);
        $this->subscriptionService->cancel($subscription, $immediately);

        return redirect()
            ->route('billing.admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled.');
    }

    public function resume(BillingSubscription $subscription)
    {
        try {
            $this->subscriptionService->resume($subscription);
            return redirect()
                ->route('billing.admin.subscriptions.show', $subscription)
                ->with('success', 'Subscription resumed.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}