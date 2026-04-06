@extends('billing::admin.layouts.app')

@section('title', 'Transactions')
@section('breadcrumb')<span class="breadcrumb-current">Transactions</span>@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Transactions</h1>
            <p class="page-subtitle">All payment records across drivers</p>
        </div>
    </div>

    {{-- Summary strip --}}
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-label">Total Volume</div>
            <div class="stat-value" style="font-size:20px;">{{ config('billing.currency_symbol') }} {{ number_format($summary['total'], 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Completed</div>
            <div class="stat-value accent" style="font-size:20px;">{{ config('billing.currency_symbol') }} {{ number_format($summary['completed'], 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value" style="font-size:20px;color:var(--warning);">{{ $summary['pending'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Failed</div>
            <div class="stat-value" style="font-size:20px;color:var(--danger);">{{ $summary['failed'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filters">
        <input type="text" name="search" class="form-control" placeholder="Search ref, client, account…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">All Statuses</option>
            @foreach(['pending','completed','failed','refunded','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="account_type" class="form-control">
            <option value="">All Account Types</option>
            @foreach(['mobile','bank','card'] as $t)
                <option value="{{ $t }}" {{ request('account_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="transaction_type" class="form-control">
            <option value="">Income & Expense</option>
            <option value="income" {{ request('transaction_type') === 'income' ? 'selected' : '' }}>Income</option>
            <option value="expense" {{ request('transaction_type') === 'expense' ? 'selected' : '' }}>Expense</option>
        </select>
        <select name="driver" class="form-control">
            <option value="">All Drivers</option>
            <option value="mpesa" {{ request('driver') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
            <option value="stripe" {{ request('driver') === 'stripe' ? 'selected' : '' }}>Stripe</option>
        </select>
        <button class="btn btn-primary" type="submit">Filter</button>
        <a href="{{ route('billing.admin.transactions.index') }}" class="btn btn-outline">Clear</a>
    </form>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ref No</th>
                        <th>Invoice No</th>
                        <th>Client No</th>
                        <th>Account Number</th>
                        <th>Acct Type</th>
                        <th>Txn Type</th>
                        <th>Amount</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td><span class="mono" style="color:var(--accent);">{{ $tx->reference_no }}</span></td>
                            <td><span class="mono">{{ $tx->invoice_number ?? '—' }}</span></td>
                            <td><span class="mono">{{ $tx->client_no ?? '—' }}</span></td>
                            <td><span class="mono">{{ $tx->account_number }}</span></td>
                            <td><span class="tag">{{ $tx->account_type }}</span></td>
                            <td>
                                <span class="badge {{ $tx->transaction_type === 'income' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $tx->transaction_type }}
                                </span>
                            </td>
                            <td class="mono" style="font-weight:700;">{{ $tx->formatted_amount }}</td>
                            <td><span class="tag">{{ strtoupper($tx->driver) }}</span></td>
                            <td><span class="badge badge-{{ $tx->status_badge }}">{{ $tx->status }}</span></td>
                            <td style="color:var(--text-muted);font-size:12px;white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('billing.admin.transactions.show', $tx) }}" class="btn btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty">
                                    <div class="empty-icon">🔍</div>
                                    <div class="empty-title">No transactions found</div>
                                    <p>Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div style="padding:16px 20px;border-top:1px solid var(--border);">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection