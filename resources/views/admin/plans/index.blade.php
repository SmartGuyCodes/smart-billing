@extends('billing::admin.layouts.app')

@section('title', 'Plans')
@section('breadcrumb')<span class="breadcrumb-current">Plans</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Plans</h1>
        <p class="page-subtitle">Define subscription plans for your customers</p>
    </div>
    <a href="{{ route('billing.admin.plans.create') }}" class="btn btn-primary">+ New Plan</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Price</th>
                    <th>Interval</th>
                    <th>Trial</th>
                    <th>Active Subs</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $plan->name }}</div>
                            @if($plan->is_popular)
                                <span class="badge badge-info" style="font-size:9px;">Popular</span>
                            @endif
                        </td>
                        <td><span class="mono">{{ $plan->slug }}</span></td>
                        <td class="mono" style="font-weight:700;color:var(--accent);">{{ $plan->formatted_price }}</td>
                        <td><span class="tag">{{ $plan->interval }}</span></td>
                        <td>{{ $plan->trial_days > 0 ? $plan->trial_days . ' days' : '—' }}</td>
                        <td class="mono">{{ $plan->subscriptions_count }}</td>
                        <td>
                            <span class="badge {{ $plan->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('billing.admin.plans.edit', $plan) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('billing.admin.plans.toggle', $plan) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-ghost btn-sm">
                                        {{ $plan->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                @if($plan->subscriptions_count === 0)
                                    <form method="POST" action="{{ route('billing.admin.plans.destroy', $plan) }}"
                                            onsubmit="return confirm('Delete this plan?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm" style="color:var(--danger);">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">📋</div>
                                <div class="empty-title">No plans yet</div>
                                <p><a href="{{ route('billing.admin.plans.create') }}" style="color:var(--accent);">Create your first plan</a></p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection