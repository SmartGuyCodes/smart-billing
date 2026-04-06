@extends('billing::admin.layouts.app')

@section('title', 'Edit Plan — ' . $plan->name)

@section('breadcrumb')
    <a href="{{ route('billing.admin.plans.index') }}">Plans</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">Edit · {{ $plan->name }}</span>
@endsection

@push('styles')
<style>
    .edit-layout {
        display:grid;
        grid-template-columns:1fr 320px;
        gap:24px;
        align-items:start;
    }
    .price-wrap { position:relative; display:flex; align-items:center; }
    .price-prefix {
        position:absolute; left:12px; font-family:var(--font-mono); font-size:13px;
        color:var(--text-muted); pointer-events:none; z-index:1;
    }
    .price-wrap .form-control { padding-left:48px; }

    .interval-group { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
    .interval-pill input[type=radio] { display:none; }
    .interval-pill label {
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        padding:10px 6px; background:var(--surface-2); border:1px solid var(--border-2);
        border-radius:var(--radius-sm); cursor:pointer; font-size:12px; font-weight:500;
        color:var(--text-muted); transition:all .15s; gap:3px; user-select:none;
    }
    .interval-pill label .icon { font-size:17px; }
    .interval-pill input:checked + label { background:var(--accent-glow); border-color:var(--accent); color:var(--accent); }

    .feature-list { display:flex; flex-direction:column; gap:8px; margin-bottom:8px; }
    .feature-row  { display:flex; align-items:center; gap:8px; }
    .feature-row input { flex:1; }
    .btn-remove {
        background:transparent; border:1px solid var(--border-2); border-radius:var(--radius-sm);
        color:var(--text-dim); width:32px; height:34px; display:flex; align-items:center;
        justify-content:center; cursor:pointer; font-size:17px; flex-shrink:0; transition:all .15s;
    }
    .btn-remove:hover { border-color:var(--danger); color:var(--danger); }

    .toggle-row {
        display:flex; align-items:center; justify-content:space-between;
        padding:13px 0; border-bottom:1px solid var(--border);
    }
    .toggle-row:last-child { border-bottom:none; }
    .toggle-label { font-size:13px; font-weight:500; color:var(--text); }
    .toggle-hint  { font-size:11px; color:var(--text-muted); margin-top:2px; }
    .toggle-switch { position:relative; width:40px; height:22px; flex-shrink:0; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-track {
        position:absolute; inset:0; background:var(--border-2); border-radius:11px;
        cursor:pointer; transition:background .2s;
    }
    .toggle-track::after {
        content:''; position:absolute; left:3px; top:3px; width:16px; height:16px;
        background:var(--text-muted); border-radius:50%; transition:transform .2s, background .2s;
    }
    .toggle-switch input:checked + .toggle-track { background:var(--accent); }
    .toggle-switch input:checked + .toggle-track::after { transform:translateX(18px); background:#000; }

    /* ── Readonly slug field ── */
    .slug-readonly {
        background:var(--bg) !important;
        border-color:var(--border) !important;
        color:var(--text-dim) !important;
        cursor:not-allowed;
        font-family:var(--font-mono);
        font-size:12px;
    }

    /* ── Change indicator dot ── */
    .changed-dot {
        display:inline-block; width:6px; height:6px;
        background:var(--warning); border-radius:50%;
        margin-left:6px; vertical-align:middle; opacity:0;
        transition:opacity .2s;
    }
    .changed-dot.show { opacity:1; }

    /* ── Sidebar panels ── */
    .side-panel {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); overflow:hidden;
    }
    .side-panel-header {
        padding:12px 16px; border-bottom:1px solid var(--border);
        font-size:12px; font-weight:600; color:var(--text-muted);
        text-transform:uppercase; letter-spacing:.06em;
    }
    .side-panel-body { padding:16px; }

    .stat-pill {
        display:flex; align-items:center; justify-content:space-between;
        padding:10px 0; border-bottom:1px solid var(--border);
        font-size:13px;
    }
    .stat-pill:last-child { border-bottom:none; }
    .stat-pill-label { color:var(--text-muted); }
    .stat-pill-val   { font-weight:600; font-family:var(--font-mono); color:var(--text); }

    /* danger zone */
    .danger-zone {
        border:1px solid var(--danger-dim); border-radius:var(--radius);
        background:rgba(239,68,68,.04); padding:16px;
    }
    .danger-zone-title { font-size:13px; font-weight:600; color:var(--danger); margin-bottom:6px; }
    .danger-zone-desc  { font-size:12px; color:var(--text-muted); margin-bottom:12px; }

    @media (max-width:960px) { .edit-layout { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <div>
            <h1 class="page-title">
                Edit Plan
                <span style="color:var(--text-muted);font-weight:400;font-size:18px;">· {{ $plan->name }}</span>
            </h1>
            <p class="page-subtitle">
                <span class="mono">{{ $plan->slug }}</span>
                &nbsp;·&nbsp;
                {{ $plan->subscriptions()->active()->count() }} active subscribers
            </p>
        </div>
    </div>
    <div style="display:flex;gap:8px;">
        <form method="POST" action="{{ route('billing.admin.plans.toggle', $plan) }}" style="display:inline;">
            @csrf @method('PATCH')
            <button class="btn {{ $plan->is_active ? 'btn-outline' : 'btn-primary' }} btn-sm">
                {{ $plan->is_active ? 'Disable Plan' : 'Enable Plan' }}
            </button>
        </form>
        <a href="{{ route('billing.admin.plans.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('billing.admin.plans.update', $plan) }}" id="planForm">
@csrf @method('PUT')

{{-- Hidden original values for diff detection --}}
<input type="hidden" id="origName"  value="{{ $plan->name }}">
<input type="hidden" id="origPrice" value="{{ $plan->price }}">
<input type="hidden" id="origDesc"  value="{{ $plan->description }}">
<input type="hidden" id="origIntv"  value="{{ $plan->interval }}">
<input type="hidden" id="origTrial" value="{{ $plan->trial_days }}">

<div class="edit-layout">

{{-- ═══════════ LEFT: FORM ═══════════ --}}
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Identity --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                Identity
                <span class="changed-dot" id="dotIdentity"></span>
            </span>
        </div>
        <div class="card-body">
            <div class="grid-2">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Plan Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" id="planName" class="form-control"
                        value="{{ old('name', $plan->name) }}" autocomplete="off" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">
                        Slug
                        <span style="font-size:10px;font-weight:400;color:var(--text-dim);margin-left:4px;">locked</span>
                    </label>
                    <input type="text" class="form-control slug-readonly"
                        value="{{ $plan->slug }}" readonly tabindex="-1">
                    <p class="form-hint">Cannot be changed after creation.</p>
                </div>
            </div>
            <div class="form-group" style="margin-top:16px;margin-bottom:0;">
                <label class="form-label">Description</label>
                <textarea name="description" id="planDesc" class="form-control" rows="2"
                    placeholder="Brief summary shown to customers">{{ old('description', $plan->description) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                Pricing
                <span class="changed-dot" id="dotPricing"></span>
            </span>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Amount ({{ config('billing.currency','KES') }}) <span style="color:var(--danger)">*</span></label>
                <div class="price-wrap">
                    <span class="price-prefix">{{ config('billing.currency_symbol','KSh') }}</span>
                    <input type="number" name="price" id="planPrice" class="form-control"
                        value="{{ old('price', $plan->price) }}" min="0" step="1" required>
                </div>
                @if($plan->subscriptions()->active()->count() > 0)
                    <p class="form-hint" style="color:var(--warning);">
                        ⚠ Price changes apply to new renewals only. Existing subscribers renew at their original rate until you notify them.
                    </p>
                @endif
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Billing Interval <span style="color:var(--danger)">*</span></label>
                <div class="interval-group">
                    @foreach(['daily'=>'📅','weekly'=>'🗓','monthly'=>'📆','yearly'=>'🗃'] as $val => $icon)
                        <div class="interval-pill">
                            <input type="radio" name="interval" id="int_{{ $val }}" value="{{ $val }}"
                                {{ old('interval', $plan->interval) === $val ? 'checked' : '' }}>
                            <label for="int_{{ $val }}">
                                <span class="icon">{{ $icon }}</span>
                                {{ ucfirst($val) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Trial & sort --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Trial &amp; Display Order</span></div>
        <div class="card-body">
            <div class="grid-2">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Trial Days</label>
                    <input type="number" name="trial_days" id="planTrial" class="form-control"
                        value="{{ old('trial_days', $plan->trial_days) }}" min="0" max="365">
                    <p class="form-hint">0 disables trial.</p>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control"
                        value="{{ old('sort_order', $plan->sort_order) }}" min="0">
                    <p class="form-hint">Lower = appears first.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Features --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Features</span>
            <button type="button" class="btn btn-outline btn-sm" id="addFeatureBtn">+ Add Feature</button>
        </div>
        <div class="card-body">
            <div class="feature-list" id="featureList">
                @php $features = old('features', $plan->features ?? ['']) @endphp
                @foreach($features as $feat)
                    <div class="feature-row">
                        <input type="text" name="features[]" class="form-control feature-input"
                            value="{{ $feat }}" placeholder="e.g. Up to 5 users">
                        <button type="button" class="btn-remove">×</button>
                    </div>
                @endforeach
            </div>
            <p class="form-hint">Each entry renders as a ✓ bullet on your pricing page.</p>
        </div>
    </div>

    {{-- Flags --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Visibility</span></div>
        <div class="card-body" style="padding:0 20px;">
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Active</div>
                    <div class="toggle-hint">Inactive plans are hidden from new sign-ups</div>
                </div>
                <label class="toggle-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="tActive"
                        {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Mark as Popular</div>
                    <div class="toggle-hint">Highlights with a "Most Popular" badge</div>
                </div>
                <label class="toggle-switch">
                    <input type="hidden" name="is_popular" value="0">
                    <input type="checkbox" name="is_popular" value="1" id="tPopular"
                        {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}>
                    <span class="toggle-track"></span>
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;padding-bottom:40px;">
        <button type="submit" class="btn btn-primary" id="saveBtn" style="padding:10px 28px;font-size:14px;">
            Save Changes
        </button>
        <a href="{{ route('billing.admin.plans.index') }}" class="btn btn-outline">Cancel</a>
        <span id="unsavedNote" style="display:none;align-self:center;font-size:12px;color:var(--warning);">
            ● Unsaved changes
        </span>
    </div>
</div>

{{-- ═══════════ RIGHT: META PANEL ═══════════ --}}
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Plan Stats --}}
    <div class="side-panel">
        <div class="side-panel-header">Plan Stats</div>
        <div class="side-panel-body" style="padding:0 16px;">
            @php
                $activeSubs  = $plan->subscriptions()->active()->count();
                $totalSubs   = $plan->subscriptions()->count();
                $trialSubs   = $plan->subscriptions()->trialing()->count();
                $revenue     = $plan->subscriptions()->active()->count() * $plan->price;
            @endphp
            <div class="stat-pill">
                <span class="stat-pill-label">Active subscribers</span>
                <span class="stat-pill-val" style="color:var(--accent);">{{ $activeSubs }}</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-label">On trial</span>
                <span class="stat-pill-val">{{ $trialSubs }}</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-label">Total all time</span>
                <span class="stat-pill-val">{{ $totalSubs }}</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-label">Est. MRR</span>
                <span class="stat-pill-val">{{ config('billing.currency_symbol') }} {{ number_format($revenue, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Plan Metadata --}}
    <div class="side-panel">
        <div class="side-panel-header">Metadata</div>
        <div class="side-panel-body" style="padding:0 16px;">
            <div class="stat-pill">
                <span class="stat-pill-label">Created</span>
                <span class="stat-pill-val" style="font-size:12px;">{{ $plan->created_at->format('d M Y') }}</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-label">Last updated</span>
                <span class="stat-pill-val" style="font-size:12px;">{{ $plan->updated_at->format('d M Y') }}</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-label">Plan ID</span>
                <span class="stat-pill-val mono" style="font-size:12px;">#{{ $plan->id }}</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-label">Currency</span>
                <span class="stat-pill-val mono">{{ $plan->currency }}</span>
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    @if($activeSubs === 0)
        <div class="danger-zone">
            <div class="danger-zone-title">Danger Zone</div>
            <div class="danger-zone-desc">This plan has no active subscribers. Deleting it is permanent and cannot be undone.</div>
            <form method="POST" action="{{ route('billing.admin.plans.destroy', $plan) }}"
                  onsubmit="return confirm('Permanently delete the {{ $plan->name }} plan?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm" style="width:100%;">Delete Plan</button>
            </form>
        </div>
    @else
        <div style="padding:14px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);font-size:12px;color:var(--text-muted);">
            🔒 Plan cannot be deleted while it has <strong style="color:var(--text);">{{ $activeSubs }}</strong> active subscriber(s). Cancel those subscriptions first.
        </div>
    @endif

</div>
</div>{{-- /edit-layout --}}
</form>
@endsection

@push('scripts')
<script>
(function(){
    const ITV = { daily:'day', weekly:'week', monthly:'month', yearly:'year' };
    const $ = id => document.getElementById(id);

    // Original values for diff
    const orig = {
        name:  $('origName').value,
        price: $('origPrice').value,
        desc:  $('origDesc').value,
        intv:  $('origIntv').value,
        trial: $('origTrial').value,
    };

    function getInterval(){
        return (document.querySelector('[name=interval]:checked')||{}).value || orig.intv;
    }

    function checkChanges(){
        const changed =
            $('planName').value !== orig.name ||
            $('planPrice').value != orig.price ||
            $('planDesc').value !== orig.desc ||
            getInterval() !== orig.intv ||
            $('planTrial').value != orig.trial;

        $('unsavedNote').style.display = changed ? 'flex' : 'none';
        $('dotIdentity').classList.toggle('show',
            $('planName').value !== orig.name || $('planDesc').value !== orig.desc);
        $('dotPricing').classList.toggle('show',
            $('planPrice').value != orig.price || getInterval() !== orig.intv);
    }

    // Wire all inputs
    [$('planName'), $('planPrice'), $('planDesc'), $('planTrial')].forEach(el => {
        el.addEventListener('input', checkChanges);
    });
    document.querySelectorAll('[name=interval]').forEach(el => el.addEventListener('change', checkChanges));
    [$('tActive'), $('tPopular')].forEach(el => el.addEventListener('change', checkChanges));

    // Feature list
    function bindRemove(btn){
        btn.addEventListener('click', function(){
            const list = $('featureList');
            if(list.children.length > 1) this.closest('.feature-row').remove();
            else this.closest('.feature-row').querySelector('input').value = '';
        });
    }
    document.querySelectorAll('.btn-remove').forEach(bindRemove);

    $('addFeatureBtn').addEventListener('click', function(){
        const row = document.createElement('div');
        row.className = 'feature-row';
        row.innerHTML = `<input type="text" name="features[]" class="form-control feature-input" placeholder="e.g. Custom integrations">
                         <button type="button" class="btn-remove">×</button>`;
        $('featureList').appendChild(row);
        bindRemove(row.querySelector('.btn-remove'));
        row.querySelector('input').focus();
    });

    // Warn on unsaved changes before navigating away
    let formDirty = false;
    document.getElementById('planForm').addEventListener('input', () => { formDirty = true; checkChanges(); });
    document.getElementById('planForm').addEventListener('change', () => { formDirty = true; checkChanges(); });
    document.getElementById('planForm').addEventListener('submit', () => { formDirty = false; });
    window.addEventListener('beforeunload', e => {
        if(formDirty){ e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
@endpush