@extends('billing::admin.layouts.app')

@section('title', 'Subscription #' . $subscription->id)
@section('breadcrumb')
    <a href="{{ route('billing.admin.subscriptions.index') }}">Subscriptions</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">#{{ $subscription->id }}</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $subscription->billable->name ?? 'Subscription #' . $subscription->id }}</h1>
        <p class="page-subtitle">
            {{ $subscription->plan->name ?? 'Unknown Plan' }} &middot;
            <span class="badge badge-{{ match($subscription->status) { 'active' => 'success', 'trialing' => 'info', 'past_due' => 'warning', default => 'secondary' } }}">
                {{ $subscription->status }}
            </span>
        </p>
    </div>
    <div style="display:flex;gap:8px;">
        @if($subscription->isActive() || $subscription->isTrialing())
            <form method="POST" action="{{ route('billing.admin.subscriptions.cancel', $subscription) }}">
                @csrf @method('PATCH')
                <button class="btn btn-danger btn-sm" onclick="return confirm('Cancel this subscription?')">
                    Cancel Subscription
                </button>
            </form>
        @elseif($subscription->onGracePeriod())
            <form method="POST" action="{{ route('billing.admin.subscriptions.resume', $subscription) }}">
                @csrf @method('PATCH')
                <button class="btn btn-primary btn-sm">Resume</button>
            </form>
        @endif
        <a href="{{ route('billing.admin.subscriptions.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

<div class="grid-2" style="margin-bottom:16px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Subscription Info</span></div>
        <div class="card-body" style="padding:0;">
            @php
                $rows = [
                    'Customer'       => $subscription->billable->name ?? '—',
                    'Email'          => $subscription->billable->email ?? '—',
                    'Plan'           => $subscription->plan->name ?? '—',
                    'Price'          => $subscription->plan->formatted_price ?? '—',
                    'Status'         => ucfirst($subscription->status),
                    'Period Start'   => $subscription->current_period_start?->format('d M Y') ?? '—',
                    'Period End'     => $subscription->current_period_end?->format('d M Y') ?? '—',
                    'Trial Ends'     => $subscription->trial_ends_at?->format('d M Y') ?? '—',
                    'Cancelled At'   => $subscription->cancelled_at?->format('d M Y H:i') ?? '—',
                    'Retry Count'    => $subscription->retry_count . ' / ' . config('billing.dunning.max_retries', 3),
                    'Last Failed At' => $subscription->last_failed_at?->format('d M Y H:i') ?? '—',
                ];
            @endphp
            @foreach($rows as $key => $val)
                <div class="detail-row" style="padding:11px 20px;">
                    <span class="detail-key">{{ $key }}</span>
                    <span class="detail-val">{{ $val }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Renewal countdown --}}
    <div class="card" style="height:fit-content;">
        <div class="card-header"><span class="card-title">Renewal Status</span></div>
        <div class="card-body" style="text-align:center;padding:32px 20px;">
            @if($subscription->isActive())
                @php $days = $subscription->daysUntilRenewal(); @endphp
                <div style="font-size:48px;font-weight:700;font-family:var(--font-mono);color:{{ $days <= 3 ? 'var(--warning)' : 'var(--accent)' }};">
                    {{ max($days, 0) }}
                </div>
                <div style="color:var(--text-muted);font-size:14px;margin-top:4px;">days until renewal</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:8px;">
                    {{ $subscription->current_period_end?->format('l, d M Y') }}
                </div>
            @elseif($subscription->isCancelled())
                <div style="font-size:36px;">⛔</div>
                <div style="color:var(--danger);font-weight:600;margin-top:8px;">Cancelled</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                    {{ $subscription->cancelled_at?->format('d M Y') }}
                </div>
            @else
                <div style="font-size:36px;">⚠️</div>
                <div style="color:var(--warning);font-weight:600;margin-top:8px;">{{ ucfirst($subscription->status) }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Transaction History --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><span class="card-title">Transaction History</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Driver</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscription->transactions as $tx)
                    <tr>
                        <td><span class="mono" style="color:var(--accent);">{{ $tx->reference_no }}</span></td>
                        <td class="mono" style="font-weight:700;">{{ $tx->formatted_amount }}</td>
                        <td><span class="badge badge-{{ $tx->status_badge }}">{{ $tx->status }}</span></td>
                        <td><span class="tag">{{ strtoupper($tx->driver) }}</span></td>
                        <td style="font-size:12px;color:var(--text-muted);">{{ $tx->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('billing.admin.transactions.show', $tx) }}" class="btn btn-ghost btn-sm">→</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty" style="padding:24px;">No transactions yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection