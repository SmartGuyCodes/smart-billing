@extends('billing::admin.layouts.app')

@section('title', 'Invoices')
@section('breadcrumb')<span class="breadcrumb-current">Invoices</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Invoices</h1>
        <p class="page-subtitle">All generated billing invoices</p>
    </div>
</div>

{{-- Summary --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-label">Paid Revenue</div>
        <div class="stat-value accent" style="font-size:20px;">
            {{ config('billing.currency_symbol') }} {{ number_format($stats['paid_total'], 0) }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value" style="font-size:20px;color:var(--warning);">
            {{ config('billing.currency_symbol') }} {{ number_format($stats['unpaid_total'], 0) }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Overdue</div>
        <div class="stat-value" style="font-size:20px;color:var(--danger);">{{ $stats['overdue'] }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filters">
    <input type="text" name="search" class="form-control" placeholder="Search invoice number…" value="{{ request('search') }}">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['draft','unpaid','paid','void','overdue'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary" type="submit">Filter</button>
    <a href="{{ route('billing.admin.invoices.index') }}" class="btn btn-outline">Clear</a>
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Paid At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><span class="mono" style="color:var(--accent);">{{ $invoice->invoice_number }}</span></td>
                        <td>
                            <div style="font-weight:500;">{{ $invoice->billable->name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $invoice->billable->email ?? '' }}</div>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">{{ $invoice->subscription->plan->name ?? '—' }}</td>
                        <td class="mono" style="font-weight:700;">{{ $invoice->formatted_total }}</td>
                        <td>
                            @php
                                $badge = match($invoice->status) {
                                    'paid'  => 'success',
                                    'unpaid'=> 'warning',
                                    'overdue'=> 'danger',
                                    'void'  => 'secondary',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge badge-{{ $badge }}">{{ $invoice->status }}</span>
                        </td>
                        <td style="font-size:12px;color:{{ $invoice->isOverdue() ? 'var(--danger)' : 'var(--text-muted)' }};">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            {{ $invoice->paid_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                <a href="{{ route('billing.admin.invoices.show', $invoice) }}" class="btn btn-ghost btn-sm">View</a>
                                <a href="{{ route('billing.admin.invoices.pdf', $invoice) }}" class="btn btn-ghost btn-sm" target="_blank">PDF</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">🧾</div>
                                <div class="empty-title">No invoices yet</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--border);">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection