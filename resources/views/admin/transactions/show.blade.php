@extends('billing::admin.layouts.app')

@section('title', 'Transaction ' . $transaction->reference_no)
@section('breadcrumb')
    <a href="{{ route('billing.admin.transactions.index') }}">Transactions</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">{{ $transaction->reference_no }}</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $transaction->reference_no }}</h1>
            <p class="page-subtitle">
                <span class="badge badge-{{ $transaction->status_badge }}">{{ $transaction->status }}</span>
                &nbsp;
                <span class="tag">{{ strtoupper($transaction->driver) }}</span>
            </p>
        </div>
        <div style="display:flex;gap:8px;">
            @if($transaction->isPending())
                <form method="POST" action="{{ route('billing.admin.transactions.verify', $transaction) }}">
                    @csrf
                    <button class="btn btn-outline">↻ Verify Status</button>
                </form>
            @endif
            @if($transaction->isCompleted())
                <button class="btn btn-outline btn-sm" onclick="document.getElementById('refundModal').style.display='flex'">
                    ↩ Refund
                </button>
            @endif
            <a href="{{ route('billing.admin.transactions.index') }}" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    <div class="grid-2">
        {{-- Transaction Core Fields --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Transaction Details</span></div>
            <div class="card-body" style="padding:0;">
                @php
                    $rows = [
                        'Reference No'       => $transaction->reference_no,
                        'Invoice Number'     => $transaction->invoice_number ?? '—',
                        'Client No'          => $transaction->client_no ?? '—',
                        'Account Number'     => $transaction->account_number,
                        'Account Type'       => ucfirst($transaction->account_type),
                        'Transaction Type'   => ucfirst($transaction->transaction_type),
                        'Amount'             => $transaction->formatted_amount,
                        'Currency'           => $transaction->currency,
                        'Status'             => ucfirst($transaction->status),
                        'Driver'             => strtoupper($transaction->driver),
                        'Gateway Ref'        => $transaction->gateway_ref ?? '—',
                        'Checkout Request'   => $transaction->checkout_request_id ?? '—',
                        'Description'        => $transaction->description ?? '—',
                        'Created At'         => $transaction->created_at->format('d M Y, H:i:s'),
                        'Paid At'            => $transaction->paid_at?->format('d M Y, H:i:s') ?? '—',
                    ];
                @endphp
                @foreach($rows as $key => $val)
                    <div class="detail-row" style="padding:12px 20px;">
                        <span class="detail-key">{{ $key }}</span>
                        <span class="detail-val mono">{{ $val }}</span>
                    </div>
                @endforeach

                @if($transaction->failure_reason)
                    <div class="detail-row" style="padding:12px 20px;">
                        <span class="detail-key">Failure Reason</span>
                        <span class="detail-val" style="color:var(--danger);">{{ $transaction->failure_reason }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div>
            {{-- Subscription --}}
            @if($transaction->subscription)
                <div class="card" style="margin-bottom:16px;">
                    <div class="card-header">
                        <span class="card-title">Subscription</span>
                        <a href="{{ route('billing.admin.subscriptions.show', $transaction->subscription) }}" class="btn btn-ghost btn-sm">View →</a>
                    </div>
                    <div class="card-body">
                        <div style="font-weight:600;">{{ $transaction->subscription->plan->name ?? 'Unknown Plan' }}</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                            Status: <span class="badge badge-{{ $transaction->subscription->status === 'active' ? 'success' : 'secondary' }}">{{ $transaction->subscription->status }}</span>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                            Renews: {{ $transaction->subscription->current_period_end?->format('d M Y') ?? '—' }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Raw Gateway Response --}}
            <div class="card">
                <div class="card-header"><span class="card-title">Raw Gateway Response</span></div>
                <div class="card-body">
                    <pre style="
                        background:var(--bg);
                        border:1px solid var(--border);
                        border-radius:var(--radius-sm);
                        padding:14px;
                        font-size:11px;
                        font-family:var(--font-mono);
                        color:var(--text-muted);
                        overflow-x:auto;
                        max-height:300px;
                    ">{{ json_encode($transaction->meta, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    <div id="refundModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999;align-items:center;justify-content:center;">
        <div class="card" style="width:420px;">
            <div class="card-header">
                <span class="card-title">Issue Refund</span>
                <button class="btn btn-ghost btn-sm" onclick="document.getElementById('refundModal').style.display='none'">✕</button>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('billing.admin.transactions.refund', $transaction) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Refund Amount ({{ $transaction->currency }})</label>
                        <input type="number" name="amount" class="form-control"
                            value="{{ $transaction->amount }}" min="0.01" max="{{ $transaction->amount }}" step="0.01">
                        <p class="form-hint">Max: {{ $transaction->formatted_amount }}</p>
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('refundModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection