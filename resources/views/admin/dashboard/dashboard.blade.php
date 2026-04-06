@extends('billing::admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Revenue overview and billing health</p>
    </div>
    <a href="{{ route('billing.admin.transactions.index') }}" class="btn btn-outline">
        View all transactions →
    </a>
</div>

{{-- ── Stat Cards ── --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value accent">{{ config('billing.currency_symbol') }} {{ number_format($stats['total_revenue'], 0) }}</div>
        <div class="stat-change">All time</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">This Month</div>
        <div class="stat-value">{{ config('billing.currency_symbol') }} {{ number_format($stats['revenue_this_month'], 0) }}</div>
        <div class="stat-change">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Subscriptions</div>
        <div class="stat-value">{{ number_format($stats['active_subscriptions']) }}</div>
        <div class="stat-change">+ {{ $stats['trialing'] }} trialing</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Past Due</div>
        <div class="stat-value" style="color:var(--danger)">{{ $stats['past_due'] }}</div>
        <div class="stat-change">Needs attention</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Transactions</div>
        <div class="stat-value">{{ number_format($stats['total_transactions']) }}</div>
        <div class="stat-change">{{ $stats['pending_transactions'] }} pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Unpaid Invoices</div>
        <div class="stat-value" style="color:var(--warning)">{{ $stats['unpaid_invoices'] }}</div>
        <div class="stat-change">Awaiting payment</div>
    </div>
</div>

{{-- ── Revenue Chart + Plans ── --}}
<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Revenue — Last 6 Months</span>
        </div>
        <div class="card-body">
            @php $max = max(array_column($revenueChart, 'revenue')) ?: 1; @endphp
            <div style="display:flex;align-items:flex-end;gap:10px;height:140px;">
                @foreach($revenueChart as $bar)
                    @php $pct = ($bar['revenue'] / $max) * 100; @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;">
                        <div style="flex:1;width:100%;display:flex;align-items:flex-end;">
                            <div style="
                                width:100%;
                                height:{{ max($pct, 4) }}%;
                                background: linear-gradient(180deg, var(--accent) 0%, rgba(0,200,150,.3) 100%);
                                border-radius:4px 4px 0 0;
                                position:relative;
                            ">
                                <div style="
                                    position:absolute;top:-20px;left:50%;transform:translateX(-50%);
                                    font-size:10px;font-family:var(--font-mono);color:var(--text-muted);
                                    white-space:nowrap;
                                ">
                                    @if($bar['revenue'] > 0)
                                        {{ number_format($bar['revenue'] / 1000, 1) }}K
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="font-size:10px;color:var(--text-dim);text-align:center;">{{ $bar['month'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Active by Plan</span>
            <a href="{{ route('billing.admin.plans.index') }}" class="btn btn-ghost btn-sm">Manage</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($planBreakdown as $plan)
                <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-weight:600;font-size:13px;">{{ $plan->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $plan->formatted_price }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:700;font-family:var(--font-mono);color:var(--accent);">{{ $plan->subscriptions_count }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">active</div>
                    </div>
                </div>
            @empty
                <div class="empty" style="padding:32px;">
                    <div class="empty-icon">📋</div>
                    <div class="empty-title">No plans yet</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Recent Transactions ── --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Transactions</span>
        <a href="{{ route('billing.admin.transactions.index') }}" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Client</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                    <tr>
                        <td><span class="mono">{{ $tx->reference_no }}</span></td>
                        <td>{{ $tx->client_no }}</td>
                        <td>
                            <span class="mono">{{ $tx->account_number }}</span>
                            <span class="tag" style="margin-left:4px;">{{ $tx->account_type }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $tx->transaction_type === 'income' ? 'badge-success' : 'badge-warning' }}">
                                {{ $tx->transaction_type }}
                            </span>
                        </td>
                        <td class="mono" style="font-weight:600;">{{ $tx->formatted_amount }}</td>
                        <td>
                            <span class="badge badge-{{ $tx->status_badge }}">{{ $tx->status }}</span>
                        </td>
                        <td style="color:var(--text-muted);font-size:12px;">{{ $tx->created_at->format('d M, H:i') }}</td>
                        <td>
                            <a href="{{ route('billing.admin.transactions.show', $tx) }}" class="btn btn-ghost btn-sm">→</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">💳</div>
                                <div class="empty-title">No transactions yet</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection