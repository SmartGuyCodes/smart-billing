<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingInvoice;
use SmartGuyCodes\Billing\Models\BillingPlan;
use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Models\BillingTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_revenue'        => BillingTransaction::completed()->income()->sum('amount'),
            'revenue_this_month'   => BillingTransaction::completed()->income()
                ->whereMonth('paid_at', now()->month)->sum('amount'),
            'active_subscriptions' => BillingSubscription::active()->count(),
            'trialing'             => BillingSubscription::trialing()->count(),
            'past_due'             => BillingSubscription::pastDue()->count(),
            'total_transactions'   => BillingTransaction::count(),
            'pending_transactions' => BillingTransaction::pending()->count(),
            'failed_transactions'  => BillingTransaction::failed()->count(),
            'unpaid_invoices'      => BillingInvoice::unpaid()->count(),
        ];

        $recentTransactions = BillingTransaction::with('billable')
            ->latest()
            ->limit(10)
            ->get();

        $revenueChart = $this->getRevenueChart();
        $planBreakdown = BillingPlan::withCount(['subscriptions' => fn($q) => $q->active()])->get();

        return view('billing::admin.dashboard', compact(
            'stats', 'recentTransactions', 'revenueChart', 'planBreakdown'
        ));
    }

    protected function getRevenueChart(): array
    {
        $months = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $revenue = BillingTransaction::completed()
                ->income()
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');

            return [
                'month'   => $date->format('M Y'),
                'revenue' => (float) $revenue,
            ];
        });

        return $months->values()->toArray();
    }
}