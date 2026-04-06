@extends('billing::admin.layouts.app')

@section('title', 'Subscriptions')
@section('breadcrumb')<span class="breadcrumb-current">Subscriptions</span>@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Subscriptions</h1>
            <p class="page-subtitle">Customer subscription lifecycle management</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value accent" style="font-size:24px;">{{ $stats['active'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Trialing</div>
            <div class="stat-value" style="font-size:24px;color:var(--info);">{{ $stats['trialing'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Past Due</div>
            <div class="stat-value" style="font-size:24px;color:var(--warning);">{{ $stats['past_due'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cancelled</div>
            <div class="stat-value" style="font-size:24px;color:var(--text-muted);">{{ $stats['cancelled'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filters">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">All Statuses</option>
            @foreach(['trialing','active','past_due','suspended','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">Filter</button>
        <a href="{{ route('billing.admin.subscriptions.index') }}" class="btn btn-outline">Clear</a>
    </form>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Period Start</th>
                        <th>Renews / Ends</th>
                        <th>Retries</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td>
                                <div style="font-weight:500;">{{ $sub->billable->name ?? 'Unknown' }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $sub->billable->email ?? '' }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $sub->plan->name ?? '—' }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $sub->plan->formatted_price ?? '' }}</div>
                            </td>
                            <td>
                                @php
                                    $badge = match($sub->status) {
                                        'active'    => 'success',
                                        'trialing'  => 'info',
                                        'past_due'  => 'warning',
                                        'suspended' => 'danger',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ str_replace('_',' ',$sub->status) }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);">
                                {{ $sub->current_period_start?->format('d M Y') ?? '—' }}
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);">
                                {{ $sub->current_period_end?->format('d M Y') ?? '—' }}
                                @if($sub->isActive() && $sub->daysUntilRenewal() <= 3)
                                    <span class="badge badge-warning" style="font-size:9px;">Soon</span>
                                @endif
                            </td>
                            <td class="mono" style="color:{{ $sub->retry_count > 0 ? 'var(--warning)' : 'var(--text-muted)' }};">
                                {{ $sub->retry_count }} / {{ config('billing.dunning.max_retries', 3) }}
                            </td>
                            <td>
                                <a href="{{ route('billing.admin.subscriptions.show', $sub) }}" class="btn btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty">
                                    <div class="empty-icon">📋</div>
                                    <div class="empty-title">No subscriptions found</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div style="padding:16px 20px;border-top:1px solid var(--border);">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
@endsection