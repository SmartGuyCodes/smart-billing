@extends('billing::admin.layouts.app')

@section('title', 'Add Plan')

@section('breadcrumb')
    <a href="{{ route('billing.admin.plans.index') }}">Plans</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">Add</span>
@endsection

@push('styles')
<style>
    /* ── Centered narrow layout ── */
    .add-wrap {
        max-width:780px;
        margin:0 auto;
    }

    /* ── Step indicator ── */
    .steps {
        display:flex;
        align-items:center;
        gap:0;
        margin-bottom:28px;
    }

    .step {
        display:flex;
        align-items:center;
        gap:10px;
        flex:1;
    }

    .step-num {
        width:28px; height:28px;
        border-radius:50%;
        background:var(--surface-2);
        border:1px solid var(--border-2);
        display:flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; color:var(--text-muted);
        flex-shrink:0;
        transition:all .2s;
    }

    .step.active .step-num {
        background:var(--accent);
        border-color:var(--accent);
        color:#000;
    }

    .step.done .step-num {
        background:var(--accent-glow);
        border-color:var(--accent);
        color:var(--accent);
    }

    .step-label {
        font-size:12px; font-weight:500;
        color:var(--text-muted);
    }

    .step.active .step-label { color:var(--text); }
    .step.done .step-label   { color:var(--accent); }

    .step-line {
        height:1px;
        flex:1;
        background:var(--border);
        margin:0 12px;
    }

    /* ── Template cards ── */
    .template-grid {
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:12px;
        margin-bottom:4px;
    }

    .template-card {
        background:var(--surface-2);
        border:2px solid var(--border);
        border-radius:var(--radius);
        padding:16px;
        cursor:pointer;
        transition:all .15s;
        position:relative;
    }

    .template-card:hover {
        border-color:var(--border-2);
        background:var(--surface);
    }

    .template-card.selected {
        border-color:var(--accent);
        background:var(--accent-glow);
    }

    .template-card.selected::after {
        content:'✓';
        position:absolute; top:10px; right:12px;
        width:20px; height:20px;
        background:var(--accent); color:#000;
        border-radius:50%; font-size:11px; font-weight:700;
        display:flex; align-items:center; justify-content:center;
    }

    .template-icon { font-size:24px; margin-bottom:8px; }
    .template-name { font-size:14px; font-weight:700; color:var(--text); margin-bottom:2px; }
    .template-price { font-size:13px; color:var(--accent); font-family:var(--font-mono); }
    .template-desc  { font-size:11px; color:var(--text-muted); margin-top:4px; }

    /* ── Step panels ── */
    .step-panel { display:none; }
    .step-panel.active { display:block; }

    /* ── Feature chips ── */
    .feat-chips {
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-bottom:12px;
    }

    .feat-chip {
        display:inline-flex; align-items:center; gap:6px;
        padding:5px 10px;
        background:var(--surface-2);
        border:1px solid var(--border-2);
        border-radius:20px;
        font-size:12px; color:var(--text-muted);
        cursor:pointer;
        transition:all .15s;
    }

    .feat-chip.picked {
        background:var(--accent-glow);
        border-color:var(--accent);
        color:var(--accent);
    }

    .feat-chip .chip-check { font-size:10px; }

    /* ── Inline feature adder ── */
    .feat-adder {
        display:flex; gap:8px; align-items:center; margin-top:8px;
    }
    .feat-adder input { flex:1; }

    /* ── Price grid ── */
    .price-grid {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:12px;
    }

    .interval-pill input[type=radio] { display:none; }
    .interval-pill label {
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        padding:10px 6px; background:var(--surface-2); border:1px solid var(--border-2);
        border-radius:var(--radius-sm); cursor:pointer; font-size:12px; font-weight:500;
        color:var(--text-muted); transition:all .15s; gap:3px; user-select:none;
    }
    .interval-pill label .icon { font-size:17px; }
    .interval-pill input:checked + label { background:var(--accent-glow); border-color:var(--accent); color:var(--accent); }

    .interval-group { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }

    .price-preview {
        background:var(--surface-2);
        border:1px solid var(--border);
        border-radius:var(--radius-sm);
        padding:16px;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:4px;
    }

    /* ── Review panel ── */
    .review-row {
        display:flex; justify-content:space-between; align-items:flex-start;
        padding:12px 0; border-bottom:1px solid var(--border); font-size:13px;
    }
    .review-row:last-child { border-bottom:none; }
    .review-key { color:var(--text-muted); }
    .review-val { font-weight:500; color:var(--text); text-align:right; max-width:60%; }

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

    @media (max-width:680px) {
        .template-grid { grid-template-columns:1fr 1fr; }
        .price-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
<div class="add-wrap">

    <div class="page-header">
        <div>
            <h1 class="page-title">Add Plan</h1>
            <p class="page-subtitle">Use a template or start from scratch — takes about 60 seconds</p>
        </div>
        <a href="{{ route('billing.admin.plans.index') }}" class="btn btn-outline">← Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- ── Steps indicator ── --}}
    <div class="steps">
        <div class="step active" id="stepInd1">
            <div class="step-num">1</div>
            <div class="step-label">Template</div>
        </div>
        <div class="step-line"></div>
        <div class="step" id="stepInd2">
            <div class="step-num">2</div>
            <div class="step-label">Pricing</div>
        </div>
        <div class="step-line"></div>
        <div class="step" id="stepInd3">
            <div class="step-num">3</div>
            <div class="step-label">Features</div>
        </div>
        <div class="step-line"></div>
        <div class="step" id="stepInd4">
            <div class="step-num">4</div>
            <div class="step-label">Review</div>
        </div>
    </div>

    <form method="POST" action="{{ route('billing.admin.plans.store') }}" id="addPlanForm">
    @csrf

    {{-- ══════════════════════════════════════════════
         STEP 1 — Choose a template
    ══════════════════════════════════════════════ --}}
    <div class="step-panel active card" id="panel1">
        <div class="card-header" style="justify-content:flex-start;gap:12px;">
            <span class="card-title">Step 1 — Choose a starting point</span>
        </div>
        <div class="card-body">

            <div class="template-grid" id="templateGrid">
                @php
                    $templates = [
                        'starter'    => ['icon'=>'🌱','name'=>'Starter',    'price'=>999,  'interval'=>'monthly','trial'=>14, 'desc'=>'Great for individuals and small teams.',
                                         'features'=>['Up to 3 users','5 GB storage','Email support','Basic analytics']],
                        'pro'        => ['icon'=>'🚀','name'=>'Pro',         'price'=>2999, 'interval'=>'monthly','trial'=>14, 'desc'=>'For growing teams that need more.',
                                         'features'=>['Up to 20 users','50 GB storage','Priority support','Advanced analytics','API access']],
                        'enterprise' => ['icon'=>'🏢','name'=>'Enterprise',  'price'=>9999, 'interval'=>'monthly','trial'=>0,  'desc'=>'Unlimited scale with dedicated support.',
                                         'features'=>['Unlimited users','Unlimited storage','Dedicated account manager','99.9% SLA','Custom integrations','SSO / SAML']],
                        'basic_yearly'=>['icon'=>'📅','name'=>'Basic Yearly', 'price'=>7999,'interval'=>'yearly', 'trial'=>0,  'desc'=>'Save 33% by paying annually.',
                                         'features'=>['All Starter features','Annual billing discount','Priority onboarding']],
                        'free'       => ['icon'=>'🎁','name'=>'Free',         'price'=>0,   'interval'=>'monthly','trial'=>0,  'desc'=>'Let users start without a card.',
                                         'features'=>['1 user','1 GB storage','Community support']],
                        'custom'     => ['icon'=>'✏️', 'name'=>'Custom',      'price'=>null,'interval'=>'monthly','trial'=>0,  'desc'=>'Build from a blank slate.',
                                         'features'=>[]],
                    ];
                @endphp

                @foreach($templates as $key => $tpl)
                    <div class="template-card" data-template="{{ $key }}"
                         data-name="{{ $tpl['name'] }}"
                         data-price="{{ $tpl['price'] ?? '' }}"
                         data-interval="{{ $tpl['interval'] }}"
                         data-trial="{{ $tpl['trial'] }}"
                         data-features="{{ json_encode($tpl['features']) }}">
                        <div class="template-icon">{{ $tpl['icon'] }}</div>
                        <div class="template-name">{{ $tpl['name'] }}</div>
                        @if($tpl['price'] !== null)
                            <div class="template-price">
                                {{ config('billing.currency_symbol','KSh') }} {{ number_format($tpl['price']) }}
                                /{{ $tpl['interval'] === 'yearly' ? 'yr' : 'mo' }}
                            </div>
                        @else
                            <div class="template-price" style="color:var(--text-muted);">Custom pricing</div>
                        @endif
                        <div class="template-desc">{{ $tpl['desc'] }}</div>
                    </div>
                @endforeach
            </div>

        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;">
            <button type="button" class="btn btn-primary" id="nextToStep2" disabled>
                Next: Set Pricing →
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         STEP 2 — Name & Pricing
    ══════════════════════════════════════════════ --}}
    <div class="step-panel card" id="panel2">
        <div class="card-header"><span class="card-title">Step 2 — Name &amp; Pricing</span></div>
        <div class="card-body">

            <div class="grid-2" style="margin-bottom:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Plan Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" id="planName" class="form-control"
                        value="{{ old('name') }}" placeholder="e.g. Starter" autocomplete="off" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Slug <span style="font-size:10px;font-weight:400;color:var(--text-dim);">auto-filled</span></label>
                    <input type="text" name="slug" id="planSlug" class="form-control"
                        value="{{ old('slug') }}" placeholder="e.g. starter"
                        pattern="[a-z0-9\-]+" required>
                    <p class="form-hint">Permanent after creation.</p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="planDesc" class="form-control" rows="2"
                    placeholder="Brief summary shown to customers">{{ old('description') }}</textarea>
            </div>

            <div class="price-grid">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Price ({{ config('billing.currency','KES') }}) <span style="color:var(--danger)">*</span></label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <span style="position:absolute;left:12px;font-family:var(--font-mono);font-size:13px;color:var(--text-muted);pointer-events:none;">
                            {{ config('billing.currency_symbol','KSh') }}
                        </span>
                        <input type="number" name="price" id="planPrice" class="form-control"
                            style="padding-left:48px;"
                            value="{{ old('price') }}" min="0" step="1" placeholder="999" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Trial Days</label>
                    <input type="number" name="trial_days" id="planTrial" class="form-control"
                        value="{{ old('trial_days', 0) }}" min="0" max="365">
                    <p class="form-hint">0 = no trial.</p>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px;margin-bottom:0;">
                <label class="form-label">Billing Interval <span style="color:var(--danger)">*</span></label>
                <div class="interval-group">
                    @foreach(['daily'=>'📅','weekly'=>'🗓','monthly'=>'📆','yearly'=>'🗃'] as $val => $icon)
                        <div class="interval-pill">
                            <input type="radio" name="interval" id="int_{{ $val }}" value="{{ $val }}"
                                {{ old('interval','monthly') === $val ? 'checked' : '' }}>
                            <label for="int_{{ $val }}">
                                <span class="icon">{{ $icon }}</span>{{ ucfirst($val) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:space-between;">
            <button type="button" class="btn btn-ghost" id="backToStep1">← Back</button>
            <button type="button" class="btn btn-primary" id="nextToStep3">Next: Features →</button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         STEP 3 — Features
    ══════════════════════════════════════════════ --}}
    <div class="step-panel card" id="panel3">
        <div class="card-header"><span class="card-title">Step 3 — Features</span></div>
        <div class="card-body">

            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
                Click quick-add chips or type your own below. These appear as ✓ bullets on your pricing page.
            </p>

            {{-- Quick-add chips --}}
            <div class="feat-chips" id="featChips">
                @php $quickFeats = [
                    '1 user','3 users','5 users','10 users','20 users','Unlimited users',
                    '1 GB storage','5 GB storage','10 GB storage','50 GB storage','Unlimited storage',
                    'Email support','Priority support','Phone support','24/7 support','Dedicated account manager',
                    'API access','Webhooks','SSO / SAML','Custom integrations','Advanced analytics','Export to CSV',
                    '99.9% SLA','Custom domain','White-label','Audit logs',
                ] @endphp
                @foreach($quickFeats as $qf)
                    <div class="feat-chip" data-feat="{{ $qf }}">
                        <span class="chip-check">○</span> {{ $qf }}
                    </div>
                @endforeach
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0;"></div>

            {{-- Custom feature input --}}
            <div class="feat-adder">
                <input type="text" id="customFeatInput" class="form-control" placeholder="Type a custom feature and press Enter…">
                <button type="button" class="btn btn-outline" id="addCustomFeat">+ Add</button>
            </div>

            {{-- Selected features (rendered as real inputs) --}}
            <div id="selectedFeats" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;"></div>

            {{-- Hidden features array --}}
            <div id="hiddenFeats"></div>

        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:space-between;">
            <button type="button" class="btn btn-ghost" id="backToStep2">← Back</button>
            <button type="button" class="btn btn-primary" id="nextToStep4">Next: Review →</button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         STEP 4 — Review & Submit
    ══════════════════════════════════════════════ --}}
    <div class="step-panel card" id="panel4">
        <div class="card-header">
            <span class="card-title">Step 4 — Review &amp; Create</span>
        </div>
        <div class="card-body">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

                {{-- Left review --}}
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:8px;">
                        Plan Details
                    </div>
                    <div class="review-row">
                        <span class="review-key">Name</span>
                        <span class="review-val" id="revName">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-key">Slug</span>
                        <span class="review-val mono" id="revSlug">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-key">Price</span>
                        <span class="review-val" id="revPrice">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-key">Interval</span>
                        <span class="review-val" id="revInterval">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-key">Trial</span>
                        <span class="review-val" id="revTrial">—</span>
                    </div>
                </div>

                {{-- Right: features --}}
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:8px;">
                        Features
                    </div>
                    <div id="revFeatures" style="display:flex;flex-direction:column;gap:4px;"></div>
                </div>
            </div>

            {{-- Flags --}}
            <div style="display:flex;gap:16px;padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:20px;">
                <div style="flex:1;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:13px;font-weight:500;">Active</div>
                        <div style="font-size:11px;color:var(--text-muted);">Visible to new customers</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="tActive" checked>
                        <span class="toggle-track"></span>
                    </label>
                </div>
                <div style="width:1px;background:var(--border);"></div>
                <div style="flex:1;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:13px;font-weight:500;">Popular</div>
                        <div style="font-size:11px;color:var(--text-muted);">Adds a highlight badge</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="is_popular" value="0">
                        <input type="checkbox" name="is_popular" value="1" id="tPopular">
                        <span class="toggle-track"></span>
                    </label>
                </div>
            </div>

            <input type="hidden" name="sort_order" value="0">

        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:space-between;align-items:center;">
            <button type="button" class="btn btn-ghost" id="backToStep3">← Back</button>
            <div style="display:flex;gap:8px;align-items:center;">
                <span style="font-size:12px;color:var(--text-muted);">Ready to go live</span>
                <button type="submit" class="btn btn-primary" style="padding:10px 28px;font-size:14px;">
                    🚀 Create Plan
                </button>
            </div>
        </div>
    </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const $ = id => document.getElementById(id);

    // ── State ──
    let currentStep = 1;
    let pickedFeats = [];

    // ── Step navigation ──
    function showStep(n){
        for(let i=1;i<=4;i++){
            $(`panel${i}`)?.classList.toggle('active', i===n);
            const ind = $(`stepInd${i}`);
            if(ind){
                ind.classList.toggle('active', i===n);
                ind.classList.toggle('done', i<n);
            }
        }
        currentStep = n;
        if(n===4) populateReview();
    }

    // ── Template selection ──
    document.querySelectorAll('.template-card').forEach(card => {
        card.addEventListener('click', function(){
            document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('selected'));
            this.classList.add('selected');
            $('nextToStep2').disabled = false;

            // Pre-fill from template data
            const name = this.dataset.name;
            const price = this.dataset.price;
            const intv  = this.dataset.interval;
            const trial = this.dataset.trial;
            const feats = JSON.parse(this.dataset.features || '[]');

            $('planName').value = name;
            $('planSlug').value = name.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
            delete $('planSlug').dataset.manual;
            if(price !== '') $('planPrice').value = price;
            $('planTrial').value = trial;

            const intEl = document.querySelector(`[name=interval][value="${intv}"]`);
            if(intEl) intEl.checked = true;

            // Set features
            pickedFeats = [...feats];
            renderChips();
            renderSelectedFeats();
        });
    });

    $('nextToStep2').addEventListener('click', () => showStep(2));
    $('backToStep1').addEventListener('click', () => showStep(1));
    $('nextToStep3').addEventListener('click', () => {
        if(!$('planName').value.trim()){ alert('Plan name is required.'); return; }
        if(!$('planPrice').value){ alert('Price is required.'); return; }
        showStep(3);
    });
    $('backToStep2').addEventListener('click', () => showStep(2));
    $('nextToStep4').addEventListener('click', () => showStep(4));
    $('backToStep3').addEventListener('click', () => showStep(3));

    // ── Auto-slug ──
    $('planName').addEventListener('input', function(){
        if(!$('planSlug').dataset.manual){
            $('planSlug').value = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        }
    });
    $('planSlug').addEventListener('input', function(){ this.dataset.manual='1'; });

    // ── Features: chip toggle ──
    function renderChips(){
        document.querySelectorAll('.feat-chip').forEach(chip => {
            const f = chip.dataset.feat;
            const picked = pickedFeats.includes(f);
            chip.classList.toggle('picked', picked);
            chip.querySelector('.chip-check').textContent = picked ? '✓' : '○';
        });
    }

    document.querySelectorAll('.feat-chip').forEach(chip => {
        chip.addEventListener('click', function(){
            const f = this.dataset.feat;
            const idx = pickedFeats.indexOf(f);
            if(idx === -1) pickedFeats.push(f);
            else pickedFeats.splice(idx, 1);
            renderChips();
            renderSelectedFeats();
        });
    });

    // ── Features: custom add ──
    function addCustomFeat(val){
        val = val.trim();
        if(!val || pickedFeats.includes(val)) return;
        pickedFeats.push(val);
        renderChips();
        renderSelectedFeats();
        $('customFeatInput').value = '';
    }

    $('addCustomFeat').addEventListener('click', () => addCustomFeat($('customFeatInput').value));
    $('customFeatInput').addEventListener('keydown', e => {
        if(e.key==='Enter'){ e.preventDefault(); addCustomFeat($('customFeatInput').value); }
    });

    // ── Render selected features list ──
    function renderSelectedFeats(){
        const container = $('selectedFeats');
        const hidden    = $('hiddenFeats');

        container.innerHTML = '';
        hidden.innerHTML = '';

        if(pickedFeats.length === 0){
            container.innerHTML = `<div style="font-size:12px;color:var(--text-dim);padding:8px 0;">No features selected yet.</div>`;
            return;
        }

        pickedFeats.forEach((f, i) => {
            // Hidden input for form submit
            const inp = document.createElement('input');
            inp.type='hidden'; inp.name='features[]'; inp.value=f;
            hidden.appendChild(inp);

            // Visible row
            const row = document.createElement('div');
            row.style.cssText='display:flex;align-items:center;gap:8px;';
            row.innerHTML = `
                <span style="color:var(--accent);font-size:11px;font-weight:700;width:14px;">✓</span>
                <span style="flex:1;font-size:13px;color:var(--text);">${esc(f)}</span>
                <button type="button" data-idx="${i}" style="
                    background:transparent;border:none;cursor:pointer;
                    color:var(--text-dim);font-size:15px;padding:2px 6px;
                    border-radius:4px;transition:color .15s;
                " onmouseenter="this.style.color='var(--danger)'" onmouseleave="this.style.color='var(--text-dim)'">×</button>`;
            row.querySelector('button').addEventListener('click', function(){
                pickedFeats.splice(parseInt(this.dataset.idx), 1);
                renderChips();
                renderSelectedFeats();
            });
            container.appendChild(row);
        });
    }

    // ── Populate review step ──
    function populateReview(){
        const sym = '{{ config("billing.currency_symbol","KSh") }}';
        const ITV = { daily:'day', weekly:'week', monthly:'month', yearly:'year' };
        const intv = (document.querySelector('[name=interval]:checked')||{}).value || 'monthly';
        const trial = parseInt($('planTrial').value)||0;

        $('revName').textContent     = $('planName').value || '—';
        $('revSlug').textContent     = $('planSlug').value || '—';
        $('revPrice').textContent    = `${sym} ${parseInt($('planPrice').value||0).toLocaleString()} / ${ITV[intv]||intv}`;
        $('revInterval').textContent = ucfirst(intv);
        $('revTrial').textContent    = trial > 0 ? `${trial} days free` : 'None';

        const rf = $('revFeatures');
        rf.innerHTML = pickedFeats.length
            ? pickedFeats.map(f=>`<div style="font-size:13px;display:flex;gap:6px;align-items:flex-start;padding:2px 0;">
                    <span style="color:var(--accent);font-size:11px;margin-top:2px;">✓</span>
                    <span style="color:var(--text-muted);">${esc(f)}</span></div>`).join('')
            : `<span style="color:var(--text-dim);font-size:12px;font-style:italic;">No features added</span>`;
    }

    function esc(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function ucfirst(s){ return s.charAt(0).toUpperCase()+s.slice(1); }

    // ── Init ──
    renderChips();
    renderSelectedFeats();
})();
</script>
@endpush