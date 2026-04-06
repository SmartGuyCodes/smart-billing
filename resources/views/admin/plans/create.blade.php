@extends('billing::admin.layouts.app')

@section('title', 'Create Plan')

@section('breadcrumb')
    <a href="{{ route('billing.admin.plans.index') }}">Plans</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">Create</span>
@endsection

@push('styles')
<style>
    .create-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }
    .price-wrap { position:relative; display:flex; align-items:center; }
    .price-prefix {
        position:absolute; left:12px; font-family:var(--font-mono);
        font-size:13px; color:var(--text-muted); pointer-events:none; z-index:1;
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
    .interval-pill label:hover { color:var(--text); border-color:var(--border-2); }

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
        content:''; position:absolute; left:3px; top:3px;
        width:16px; height:16px; background:var(--text-muted);
        border-radius:50%; transition:transform .2s, background .2s;
    }
    .toggle-switch input:checked + .toggle-track { background:var(--accent); }
    .toggle-switch input:checked + .toggle-track::after { transform:translateX(18px); background:#000; }

    /* ── Preview ── */
    .preview-card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); overflow:hidden; position:sticky; top:80px;
    }
    .preview-popular-pill {
        display:inline-flex; align-items:center; gap:4px; padding:3px 10px;
        background:var(--accent); color:#000; border-radius:20px; font-size:10px;
        font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-bottom:10px;
    }
    .preview-name  { font-size:20px; font-weight:700; color:var(--text); line-height:1.2; }
    .preview-desc  { font-size:12px; color:var(--text-muted); margin-top:4px; min-height:16px; }
    .preview-price-row { display:flex; align-items:baseline; gap:4px; }
    .preview-sym   { font-size:15px; font-weight:600; color:var(--text-muted); font-family:var(--font-mono); }
    .preview-amt   { font-size:36px; font-weight:700; color:var(--accent); font-family:var(--font-mono); line-height:1; }
    .preview-per   { font-size:13px; color:var(--text-muted); }
    .preview-trial { margin-top:6px; font-size:12px; color:var(--info); }
    .preview-feat-item {
        display:flex; align-items:flex-start; gap:8px; padding:5px 0;
        font-size:13px; color:var(--text-muted); border-bottom:1px dashed var(--border);
    }
    .preview-feat-item:last-child { border-bottom:none; }
    .preview-feat-item::before { content:'✓'; color:var(--accent); font-size:11px; font-weight:700; margin-top:2px; flex-shrink:0; }

    @media (max-width:960px) {
        .create-layout { grid-template-columns:1fr; }
        .preview-card  { position:static; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Plan</h1>
        <p class="page-subtitle">Define a new subscription tier — preview updates live as you type</p>
    </div>
    <a href="{{ route('billing.admin.plans.index') }}" class="btn btn-outline">← Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif

<form method="POST" action="{{ route('billing.admin.plans.store') }}" id="planForm">
@csrf

<div class="create-layout">

{{-- ═══════════════ LEFT COLUMN ═══════════════ --}}
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Identity --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Identity</span></div>
        <div class="card-body">
            <div class="grid-2">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Plan Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" id="planName" class="form-control"
                        value="{{ old('name') }}" placeholder="e.g. Starter" autocomplete="off" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">
                        Slug <span style="color:var(--danger)">*</span>
                        <span style="font-size:10px;font-weight:400;color:var(--text-dim);margin-left:4px;">auto-filled</span>
                    </label>
                    <input type="text" name="slug" id="planSlug" class="form-control"
                        value="{{ old('slug') }}" placeholder="e.g. starter"
                        pattern="[a-z0-9\-]+" title="Lowercase letters, numbers, hyphens only" required>
                    <p class="form-hint">Permanent after creation.</p>
                </div>
            </div>
            <div class="form-group" style="margin-top:16px;margin-bottom:0;">
                <label class="form-label">Description</label>
                <textarea name="description" id="planDesc" class="form-control" rows="2"
                    placeholder="Brief summary shown to customers on pricing pages">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Pricing</span></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Amount ({{ config('billing.currency', 'KES') }}) <span style="color:var(--danger)">*</span></label>
                <div class="price-wrap">
                    <span class="price-prefix">{{ config('billing.currency_symbol', 'KSh') }}</span>
                    <input type="number" name="price" id="planPrice" class="form-control"
                        value="{{ old('price') }}" min="0" step="1" placeholder="999" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Billing Interval <span style="color:var(--danger)">*</span></label>
                <div class="interval-group">
                    @foreach(['daily'=>'📅','weekly'=>'🗓','monthly'=>'📆','yearly'=>'🗃'] as $val => $icon)
                        <div class="interval-pill">
                            <input type="radio" name="interval" id="int_{{ $val }}" value="{{ $val }}"
                                {{ old('interval','monthly') === $val ? 'checked' : '' }}>
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
                        value="{{ old('trial_days', 0) }}" min="0" max="365">
                    <p class="form-hint">Set to 0 to skip trial.</p>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control"
                        value="{{ old('sort_order', 0) }}" min="0">
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
                @foreach(old('features', ['','','']) as $feat)
                    <div class="feature-row">
                        <input type="text" name="features[]" class="form-control feature-input"
                            value="{{ $feat }}" placeholder="e.g. Up to 5 users">
                        <button type="button" class="btn-remove">×</button>
                    </div>
                @endforeach
            </div>
            <p class="form-hint">Each line renders as a ✓ bullet on your pricing page.</p>
        </div>
    </div>

    {{-- Flags --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Visibility</span></div>
        <div class="card-body" style="padding:0 20px;">
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Active</div>
                    <div class="toggle-hint">Inactive plans are hidden from customers</div>
                </div>
                <label class="toggle-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="tActive"
                        {{ old('is_active', '1') ? 'checked' : '' }}>
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Mark as Popular</div>
                    <div class="toggle-hint">Adds a highlight badge on pricing pages</div>
                </div>
                <label class="toggle-switch">
                    <input type="hidden" name="is_popular" value="0">
                    <input type="checkbox" name="is_popular" value="1" id="tPopular"
                        {{ old('is_popular') ? 'checked' : '' }}>
                    <span class="toggle-track"></span>
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;padding-bottom:40px;">
        <button type="submit" class="btn btn-primary" style="padding:10px 28px;font-size:14px;">
            Create Plan
        </button>
        <a href="{{ route('billing.admin.plans.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</div>

{{-- ═══════════════ RIGHT COLUMN — LIVE PREVIEW ═══════════════ --}}
<div>
    <div class="preview-card">
        <div style="padding:20px 20px 16px;border-bottom:1px solid var(--border);">
            <div id="pPopular" class="preview-popular-pill" style="display:none;">⭐ Most Popular</div>
            <div class="preview-name" id="pName">Plan Name</div>
            <div class="preview-desc" id="pDesc">Description will appear here</div>
        </div>

        <div style="padding:20px;border-bottom:1px solid var(--border);">
            <div class="preview-price-row">
                <span class="preview-sym">{{ config('billing.currency_symbol','KSh') }}</span>
                <span class="preview-amt" id="pPrice">0</span>
                <span class="preview-per">/<span id="pInterval">month</span></span>
            </div>
            <div class="preview-trial" id="pTrial" style="display:none;"></div>
        </div>

        <div style="padding:16px 20px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:10px;">
                What's included
            </div>
            <div id="pFeatures">
                <div class="preview-feat-item" style="color:var(--text-dim);font-style:italic;">Add features above…</div>
            </div>
        </div>

        <div style="padding:12px 20px;border-top:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
            <span class="tag" id="pSlug">slug</span>
            <span class="tag" id="pStatus" style="color:var(--accent);">active</span>
        </div>
    </div>

    {{-- JSON snapshot --}}
    <div style="margin-top:12px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
        <div style="padding:8px 14px;border-bottom:1px solid var(--border);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);">
            API Payload Preview
        </div>
        <pre id="pJson" style="padding:14px;font-size:11px;font-family:var(--font-mono);color:var(--text-muted);line-height:1.7;overflow-x:auto;margin:0;"></pre>
    </div>
</div>

</div>{{-- /create-layout --}}
</form>
@endsection

@push('scripts')
<script>
(function(){
    const ITV = { daily:'day', weekly:'week', monthly:'month', yearly:'year' };

    const $ = id => document.getElementById(id);
    const nameEl=$('planName'), slugEl=$('planSlug'), descEl=$('planDesc'),
          priceEl=$('planPrice'), trialEl=$('planTrial'),
          activeEl=$('tActive'), popularEl=$('tPopular');

    // Auto-slug
    nameEl.addEventListener('input', function(){
        if(!slugEl.dataset.manual){
            slugEl.value = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        }
        sync();
    });
    slugEl.addEventListener('input', function(){ this.dataset.manual='1'; sync(); });

    // Wire inputs
    [descEl,priceEl,trialEl,activeEl,popularEl].forEach(el=>{
        el.addEventListener('input', sync);
        el.addEventListener('change', sync);
    });
    document.querySelectorAll('[name=interval]').forEach(el=>el.addEventListener('change',sync));

    // Feature list
    function bindRemove(btn){
        btn.addEventListener('click', function(){
            const list=$('featureList');
            if(list.children.length > 1) this.closest('.feature-row').remove();
            else this.closest('.feature-row').querySelector('input').value='';
            sync();
        });
    }
    document.querySelectorAll('.btn-remove').forEach(bindRemove);
    document.querySelectorAll('.feature-input').forEach(el=>el.addEventListener('input',sync));

    $('addFeatureBtn').addEventListener('click', function(){
        const row=document.createElement('div');
        row.className='feature-row';
        row.innerHTML=`<input type="text" name="features[]" class="form-control feature-input" placeholder="e.g. Priority support">
                       <button type="button" class="btn-remove">×</button>`;
        $('featureList').appendChild(row);
        row.querySelector('input').addEventListener('input',sync);
        bindRemove(row.querySelector('.btn-remove'));
        row.querySelector('input').focus();
    });

    function esc(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function sync(){
        const name    = nameEl.value.trim()||'Plan Name';
        const slug    = slugEl.value.trim()||'plan-slug';
        const desc    = descEl.value.trim()||'Description will appear here';
        const price   = parseFloat(priceEl.value)||0;
        const intv    = (document.querySelector('[name=interval]:checked')||{}).value||'monthly';
        const trial   = parseInt(trialEl.value)||0;
        const feats   = Array.from(document.querySelectorAll('.feature-input'))
                          .map(e=>e.value.trim()).filter(Boolean);
        const active  = activeEl.checked;
        const popular = popularEl.checked;

        $('pName').textContent = name;
        $('pDesc').textContent = desc;
        $('pPrice').textContent = price.toLocaleString();
        $('pInterval').textContent = ITV[intv]||intv;
        $('pSlug').textContent = slug;
        $('pStatus').textContent = active?'active':'inactive';
        $('pStatus').style.color = active?'var(--accent)':'var(--text-dim)';
        $('pPopular').style.display = popular?'inline-flex':'none';

        const trialEl2=$('pTrial');
        if(trial>0){ trialEl2.style.display='block'; trialEl2.textContent=`🎁 ${trial}-day free trial`; }
        else trialEl2.style.display='none';

        $('pFeatures').innerHTML = feats.length
            ? feats.map(f=>`<div class="preview-feat-item">${esc(f)}</div>`).join('')
            : `<div class="preview-feat-item" style="color:var(--text-dim);font-style:italic;">Add features above…</div>`;

        $('pJson').textContent = JSON.stringify({
            name, slug, price,
            currency:'{{ config("billing.currency","KES") }}',
            interval:intv, trial_days:trial,
            features:feats, is_active:active, is_popular:popular
        }, null, 2);
    }

    sync();
})();
</script>
@endpush