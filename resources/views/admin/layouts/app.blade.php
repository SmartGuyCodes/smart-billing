<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Billing') — {{ config('billing.invoice.company_name', config('app.name')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:        #0d0f14;
            --surface:   #151820;
            --surface-2: #1c2030;
            --border:    #252a3a;
            --border-2:  #2e3448;
            --text:      #e2e8f0;
            --text-muted:#7c8599;
            --text-dim:  #4a5168;
            --accent:    #00c896;
            --accent-dim:#003d2e;
            --accent-glow: rgba(0,200,150,0.12);
            --danger:    #ef4444;
            --danger-dim:#3b0f0f;
            --warning:   #f59e0b;
            --warning-dim:#3b2700;
            --info:      #3b82f6;
            --info-dim:  #0f1f3b;
            --radius:    10px;
            --radius-sm: 6px;
            --sidebar-w: 240px;
            --font: 'DM Sans', sans-serif;
            --font-mono: 'DM Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }

        .sidebar-logo-text {
            font-weight: 700;
            font-size: 15px;
            color: var(--text);
        }

        .sidebar-logo-sub {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 400;
            display: block;
            line-height: 1;
        }

        nav { padding: 12px 0; flex: 1; }

        .nav-section {
            padding: 6px 16px 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-dim);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 13.5px;
            transition: all 0.15s;
            border-radius: 0;
            position: relative;
        }

        .nav-item:hover { color: var(--text); background: var(--surface-2); }

        .nav-item.active {
            color: var(--accent);
            background: var(--accent-glow);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 4px; bottom: 4px;
            width: 3px;
            background: var(--accent);
            border-radius: 0 2px 2px 0;
        }

        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-dim);
        }

        /* ── Main Content ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .breadcrumb a { color: var(--text-muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--accent); }
        .breadcrumb-sep { color: var(--text-dim); }
        .breadcrumb-current { color: var(--text); font-weight: 500; }

        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .page-content { padding: 28px; flex: 1; }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
        }

        .page-title { font-size: 22px; font-weight: 700; color: var(--text); }
        .page-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title { font-weight: 600; font-size: 14px; color: var(--text); }
        .card-body  { padding: 20px; }

        /* ── Stat Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
            font-family: var(--font-mono);
        }

        .stat-value.accent { color: var(--accent); }
        .stat-change {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            text-align: left;
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-dim);
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }

        tbody td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: var(--surface-2); }

        .mono { font-family: var(--font-mono); font-size: 12px; }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .badge-success  { background: rgba(0,200,150,.12); color: var(--accent); }
        .badge-danger   { background: var(--danger-dim); color: var(--danger); }
        .badge-warning  { background: var(--warning-dim); color: var(--warning); }
        .badge-info     { background: var(--info-dim); color: var(--info); }
        .badge-secondary{ background: var(--surface-2); color: var(--text-muted); }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-family: var(--font);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-primary  { background: var(--accent); color: #000; border-color: var(--accent); }
        .btn-primary:hover { background: #00e6ac; }
        .btn-outline  { background: transparent; color: var(--text); border-color: var(--border-2); }
        .btn-outline:hover { border-color: var(--accent); color: var(--accent); }
        .btn-danger   { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-ghost { background: transparent; color: var(--text-muted); border-color: transparent; }
        .btn-ghost:hover { color: var(--accent); background: var(--accent-glow); }

        /* ── Form Controls ── */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: var(--text); }
        .form-hint  { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        .form-control {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: var(--radius-sm);
            padding: 9px 12px;
            font-family: var(--font);
            font-size: 13px;
            color: var(--text);
            outline: none;
            transition: border-color 0.15s;
        }

        .form-control:focus { border-color: var(--accent); }
        .form-control::placeholder { color: var(--text-dim); }

        select.form-control { cursor: pointer; }

        /* ── Alert ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success { background: var(--accent-glow); border-color: rgba(0,200,150,.3); color: var(--accent); }
        .alert-danger  { background: var(--danger-dim); border-color: rgba(239,68,68,.3); color: var(--danger); }
        .alert-info    { background: var(--info-dim); border-color: rgba(59,130,246,.3); color: var(--info); }
        .alert-warning { background: var(--warning-dim); border-color: rgba(245,158,11,.3); color: var(--warning); }

        /* ── Pagination ── */
        .pagination { display: flex; gap: 4px; align-items: center; margin-top: 20px; }
        .pagination a, .pagination span {
            padding: 6px 11px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            border: 1px solid var(--border);
            background: var(--surface);
        }
        .pagination .active span, .pagination a:hover {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }

        /* ── Filters bar ── */
        .filters {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filters .form-control { width: auto; min-width: 140px; }

        /* ── Grid ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

        /* ── Pill tag ── */
        .tag {
            display: inline-block;
            padding: 2px 8px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 11px;
            font-family: var(--font-mono);
            color: var(--text-muted);
        }

        /* ── Empty state ── */
        .empty {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-muted);
        }
        .empty-icon { font-size: 36px; margin-bottom: 12px; }
        .empty-title { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 4px; }

        /* ── Detail rows ── */
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
        .detail-row  { padding: 12px 0; border-bottom: 1px solid var(--border); display: flex; gap: 12px; }
        .detail-key  { color: var(--text-muted); font-size: 12px; min-width: 160px; }
        .detail-val  { color: var(--text); font-size: 13px; font-weight: 500; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }
    </style>
    @stack('styles')
</head>
<body>

<!-- ── Sidebar ── -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">💳</div>
        <div>
            <span class="sidebar-logo-text">Smart Billing</span>
            <span class="sidebar-logo-sub">{{ config('billing.invoice.company_name', config('app.name')) }}</span>
        </div>
    </div>

    <nav>
        <div class="nav-section">Overview</div>
        <a href="{{ route('billing.admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('billing.admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10a8 8 0 1116 0A8 8 0 012 10zm8-5a1 1 0 00-1 1v4a1 1 0 00.293.707l2.5 2.5a1 1 0 101.414-1.414L11 10.586V6a1 1 0 00-1-1z"/></svg>
            Dashboard
        </a>

        <div class="nav-section">Billing</div>
        <a href="{{ route('billing.admin.transactions.index') }}"
           class="nav-item {{ request()->routeIs('billing.admin.transactions.*') ? 'active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/></svg>
            Transactions
            @php $pending = \SmartGuyCodes\Billing\Models\BillingTransaction::pending()->count() @endphp
            @if($pending > 0)
                <span class="badge badge-warning" style="margin-left:auto;font-size:10px;">{{ $pending }}</span>
            @endif
        </a>
        <a href="{{ route('billing.admin.subscriptions.index') }}"
           class="nav-item {{ request()->routeIs('billing.admin.subscriptions.*') ? 'active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
            Subscriptions
        </a>
        <a href="{{ route('billing.admin.invoices.index') }}"
           class="nav-item {{ request()->routeIs('billing.admin.invoices.*') ? 'active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
            Invoices
        </a>
        <a href="{{ route('billing.admin.plans.index') }}"
           class="nav-item {{ request()->routeIs('billing.admin.plans.*') ? 'active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/></svg>
            Plans
        </a>

        <div class="nav-section">Config</div>
        <a href="{{ route('billing.admin.settings.index') }}"
            class="nav-item {{ request()->routeIs('billing.admin.settings.*') ? 'active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
            Settings
        </a>
    </nav>

    <div class="sidebar-footer">
        Smart Billing v1.0 &middot; {{ strtoupper(config('billing.default_driver', 'mpesa')) }}
    </div>
</aside>

<!-- ── Main ── -->
<main class="main">
    <header class="topbar">
        <div class="breadcrumb">
            <a href="{{ route('billing.admin.dashboard') }}">Billing</a>
            @hasSection('breadcrumb')
                <span class="breadcrumb-sep">/</span>
                @yield('breadcrumb')
            @endif
        </div>
        <div class="topbar-right">
            <span style="font-size:12px;color:var(--text-muted);">
                {{ config('billing.currency_symbol') }} {{ config('billing.currency') }}
                &middot;
                {{ now()->format('d M Y') }}
            </span>
        </div>
    </header>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">✗ {{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">ℹ {{ session('info') }}</div>
        @endif

        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>