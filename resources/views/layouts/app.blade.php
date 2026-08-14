<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hotel PMS') — {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Property Management System')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 & Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 72px;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e7ebe7;
            --sidebar-text: #5c665e;
            --sidebar-text-active: #151b16;
            --sidebar-accent: #d6f843;
            --sidebar-hover: #f3f6f3;
            --sidebar-active-bg: #d6f843;
            --topbar-bg: #ffffff;
            --body-bg: #f3f6f3;
            --card-bg: #ffffff;
            --text-primary: #161c17;
            --text-secondary: #6e7870;
            --border-color: #e5ebe5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --radius: 20px;
            --radius-sm: 12px;
            --radius-pill: 50rem;
            --shadow: 0 4px 20px rgba(0,0,0,0.02);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.05);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background: var(--body-bg);
            color: var(--text-primary);
            margin: 0;
            font-size: 0.875rem;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform .3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            min-height: var(--topbar-height);
            flex-shrink: 0;
        }

        .sidebar-brand-icon {
            width: 38px; height: 38px;
            background: var(--sidebar-accent);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #151b16; font-size: 20px; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(214, 248, 67, 0.4);
        }

        .sidebar-brand-name {
            color: var(--text-primary); font-weight: 800; font-size: 1.15rem;
            line-height: 1.2; letter-spacing: -0.3px;
        }

        .sidebar-brand-sub {
            color: var(--text-secondary); font-size: 0.68rem;
            text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
        }

        .sidebar-section {
            padding: 18px 24px 6px;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-secondary);
            font-weight: 700;
        }

        .sidebar-nav { padding: 8px 14px; flex: 1; }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: var(--radius-pill);
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all .2s ease;
            margin-bottom: 3px;
            position: relative;
        }

        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        .sidebar-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-text-active);
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(214, 248, 67, 0.35);
        }

        .sidebar-link i {
            font-size: 1.1rem;
            width: 22px; text-align: center; flex-shrink: 0;
        }

        .sidebar-badge {
            margin-left: auto;
            background: #ff5a5a;
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 99px;
        }

        .sidebar-footer {
            padding: 14px;
            border-top: 1px solid var(--border-color);
        }

        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: var(--radius-sm);
            color: var(--text-primary);
            text-decoration: none;
            transition: background .2s;
        }

        .sidebar-user:hover { background: var(--sidebar-hover); }

        .sidebar-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--sidebar-accent);
            display: flex; align-items: center; justify-content: center;
            color: #151b16; font-weight: 800; font-size: 0.85rem;
            flex-shrink: 0;
        }

        .sidebar-user-name {
            color: var(--text-primary); font-weight: 700; font-size: 0.82rem;
            line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .sidebar-user-role {
            color: var(--text-secondary); font-size: 0.7rem; font-weight: 500;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            position: fixed;
            top: 0; left: var(--sidebar-width); right: 0;
            height: var(--topbar-height);
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center;
            padding: 0 32px;
            gap: 20px;
            z-index: 999;
            box-shadow: var(--shadow);
        }

        .topbar-title {
            font-size: 1.15rem; font-weight: 800; color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        .topbar-breadcrumb {
            font-size: 0.75rem; color: var(--text-secondary);
            margin-top: 1px; font-weight: 500;
        }

        .topbar-search {
            position: relative;
            max-width: 380px;
            width: 100%;
        }

        .topbar-search input {
            background: #f3f6f3;
            border: 1px solid #e0e6e0;
            border-radius: var(--radius-pill);
            padding: 8px 16px 8px 40px;
            font-size: 0.85rem;
            width: 100%;
            transition: all .2s;
        }

        .topbar-search input:focus {
            outline: none;
            background: #ffffff;
            border-color: #151b16;
            box-shadow: 0 0 0 3px rgba(214, 248, 67, 0.4);
        }

        .topbar-search i {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .property-switcher {
            display: flex; align-items: center; gap: 8px;
            background: #f3f6f3; border: 1px solid #e0e6e0;
            border-radius: var(--radius-pill); padding: 6px 14px;
            cursor: pointer; transition: all .2s;
            font-size: 0.82rem; font-weight: 600;
        }

        .property-switcher:hover { border-color: #151b16; background: #ffffff; }

        .property-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #10b981; flex-shrink: 0;
        }

        .topbar-icon-btn {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #e0e6e0;
            background: #ffffff; color: var(--text-primary);
            cursor: pointer; transition: all .2s; text-decoration: none;
            font-size: 1.05rem;
            position: relative;
        }

        .topbar-icon-btn:hover { background: #f3f6f3; border-color: #151b16; }

        .topbar-badge {
            position: absolute; top: -2px; right: -2px;
            width: 16px; height: 16px; border-radius: 50%;
            background: #ff5a5a; color: white;
            font-size: 0.6rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 32px;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* ===== CARDS ===== */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
            border-radius: var(--radius) var(--radius) 0 0;
            display: flex; align-items: center; gap: 10px;
        }

        .card-header h5, .card-header h6 {
            margin: 0; font-weight: 800; letter-spacing: -0.2px;
        }

        .card-body { padding: 24px; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            padding: 22px;
            display: flex; align-items: flex-start; gap: 16px;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }

        /* ===== LUXURY EXECUTIVE STAT CARDS ===== */
        .stat-card-luxury {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 22px;
            padding: 22px 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05), 0 2px 6px rgba(15, 23, 42, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .stat-card-luxury:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 24px 45px -10px rgba(15, 23, 42, 0.14), 0 8px 20px -4px rgba(15, 23, 42, 0.08);
            border-color: rgba(197, 160, 89, 0.5);
        }

        .stat-card-luxury::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #c5a059, #dfb76c, #9a7b38);
            opacity: 0.8;
        }

        .stat-card-luxury::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(197, 160, 89, 0.8), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card-luxury:hover::after {
            opacity: 1;
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stat-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #ffffff !important;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .stat-icon-wrapper i,
        .stat-icon-wrapper svg {
            color: #ffffff !important;
            font-size: 1.3rem !important;
            display: inline-block;
        }

        .stat-card-luxury:hover .stat-icon-wrapper {
            transform: scale(1.12) rotate(-4deg);
        }

        /* Icon Gradients with Glow */
        .stat-icon-emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 10px 22px rgba(16, 185, 129, 0.4);
        }

        .stat-icon-sapphire {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 10px 22px rgba(59, 130, 246, 0.4);
        }

        .stat-icon-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            box-shadow: 0 10px 22px rgba(99, 102, 241, 0.4);
        }

        .stat-icon-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 10px 22px rgba(245, 158, 11, 0.4);
        }

        .stat-icon-teal {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            box-shadow: 0 10px 22px rgba(20, 184, 166, 0.4);
        }

        .stat-icon-obsidian {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.4);
        }

        .stat-icon-obsidian i {
            color: #c5a059 !important;
        }

        .stat-badge-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.02em;
        }

        .stat-badge-emerald { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .stat-badge-sapphire { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
        .stat-badge-indigo   { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
        .stat-badge-amber    { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .stat-badge-teal     { background: rgba(20, 184, 166, 0.12); color: #0f766e; }
        .stat-badge-gold     { background: rgba(197, 160, 89, 0.15); color: #9a7b38; }

        .stat-card-title {
            font-size: 0.76rem;
            font-weight: 700;
            text-uppercase: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 6px;
        }

        .stat-card-value {
            font-size: 1.95rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .stat-card-value.revenue-val {
            font-size: 1.35rem;
            letter-spacing: -0.02em;
        }

        .stat-mini-bar {
            height: 5px;
            width: 100%;
            background: #f1f5f9;
            border-radius: 99px;
            margin-top: 16px;
            overflow: hidden;
        }

        .stat-mini-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.6s ease;
        }

        .stat-icon {
            width: 48px; height: 48px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; flex-shrink: 0;
        }

        .stat-icon.lime   { background: #eefb98; color: #151b16; }
        .stat-icon.blue   { background: #e0edff; color: #2563eb; }
        .stat-icon.green  { background: #dcfce7; color: #16a34a; }
        .stat-icon.amber  { background: #fef3c7; color: #d97706; }
        .stat-icon.red    { background: #fee2e2; color: #dc2626; }
        .stat-icon.purple { background: #f3e8ff; color: #9333ea; }
        .stat-icon.teal   { background: #ccfbf1; color: #0d9488; }

        .stat-number {
            font-size: 1.85rem; font-weight: 800; color: var(--text-primary);
            line-height: 1; letter-spacing: -0.5px;
        }

        .stat-label {
            font-size: 0.78rem; color: var(--text-secondary);
            font-weight: 600; margin-top: 6px;
        }

        .stat-change {
            font-size: 0.72rem; font-weight: 700; margin-top: 6px;
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: var(--radius-pill);
        }

        .stat-change.up   { background: #dcfce7; color: #15803d; }
        .stat-change.down { background: #fee2e2; color: #b91c1c; }

        /* ===== TABLES ===== */
        .table { margin-bottom: 0; }
        .table th {
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--text-secondary); border-bottom: 1px solid var(--border-color);
            padding: 12px 18px; background: #fafcfb;
        }

        .table td {
            padding: 14px 18px; vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.85rem; font-weight: 500;
        }

        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover td { background: #f7faf7; }

        /* ===== BADGES ===== */
        .badge-status {
            padding: 5px 14px; border-radius: var(--radius-pill);
            font-size: 0.72rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 4px;
        }

        .badge-status.active, .badge-status.confirmed, .badge-status.checked_in {
            background: #d6f843; color: #151b16;
        }

        .badge-status.inactive, .badge-status.cancelled {
            background: #ffdada; color: #991b1b;
        }

        .badge-status.pending, .badge-status.pending_payment {
            background: #fef3c7; color: #92400e;
        }

        .badge-status.setup, .badge-status.inquiry, .badge-status.held {
            background: #e0ebd8; color: #2d382e;
        }

        /* ===== BUTTONS ===== */
        .btn-primary {
            background: var(--sidebar-accent);
            color: #151b16; border: none;
            border-radius: var(--radius-pill);
            font-weight: 700; font-size: 0.85rem;
            padding: 9px 20px;
            transition: all .2s;
            box-shadow: 0 4px 12px rgba(214, 248, 67, 0.35);
        }

        .btn-primary:hover { background: #ccf235; color: #151b16; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(214, 248, 67, 0.5); }

        .btn-outline-primary {
            border-radius: var(--radius-pill); font-weight: 700; font-size: 0.85rem;
            border: 1px solid #151b16; color: #151b16; background: transparent;
            padding: 8px 18px;
        }

        .btn-outline-primary:hover { background: #151b16; color: #ffffff; }

        .btn-secondary {
            background: #e5ebe5; color: #151b16; border: none;
            border-radius: var(--radius-pill); font-weight: 700; font-size: 0.85rem;
            padding: 9px 18px;
        }

        .btn-secondary:hover { background: #d8e0d8; color: #151b16; }

        .btn-sm { padding: 6px 14px; font-size: 0.78rem; border-radius: var(--radius-pill); }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            border-radius: var(--radius-sm); border: 1px solid var(--border-color);
            font-size: 0.875rem; padding: 9px 14px;
            transition: all .2s; background: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            border-color: #151b16;
            box-shadow: 0 0 0 3px rgba(214, 248, 67, 0.4);
        }

        .form-label { font-weight: 700; font-size: 0.8rem; color: var(--text-primary); margin-bottom: 6px; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-size: 1.6rem; font-weight: 800; margin: 0;
            color: var(--text-primary); letter-spacing: -0.4px;
        }

        .page-header p {
            font-size: 0.85rem; color: var(--text-secondary);
            margin: 4px 0 0; font-weight: 500;
        }

        /* ===== LOADING OVERLAY ===== */
        .spinner-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(21, 27, 22, 0.5); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999; opacity: 0; pointer-events: none; transition: opacity .2s;
        }

        .spinner-overlay.active { opacity: 1; pointer-events: all; }

        /* ===== UTILITIES ===== */
        .fade-in { animation: fadeIn .3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .topbar { left: 0; padding: 0 16px; }
            .main-content { margin-left: 0; padding: 20px 16px; }
            .topbar-search { display: none; }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-grid-fill"></i>
        </div>
        <div>
            <div class="sidebar-brand-name">{{ config('app.name') }}</div>
            <div class="sidebar-brand-sub">PMS & Booking</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>

        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-section">Reservations</div>

        <a href="{{ route('admin.reservations.index') }}" class="sidebar-link {{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i>
            <span>Reservations</span>
        </a>

        <a href="{{ route('admin.tape-chart.index') }}" class="sidebar-link {{ request()->routeIs('admin.tape-chart*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i>
            <span>Tape Chart</span>
        </a>

        <div class="sidebar-section">Front Desk</div>

        <a href="{{ route('admin.front-desk.arrivals') }}" class="sidebar-link {{ request()->routeIs('admin.front-desk.arrivals') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Arrivals</span>
        </a>

        <a href="{{ route('admin.front-desk.departures') }}" class="sidebar-link {{ request()->routeIs('admin.front-desk.departures') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-right"></i>
            <span>Departures</span>
        </a>

        <a href="{{ route('admin.front-desk.in-house') }}" class="sidebar-link {{ request()->routeIs('admin.front-desk.in-house') ? 'active' : '' }}">
            <i class="bi bi-house"></i>
            <span>In-House</span>
        </a>

        <div class="sidebar-section">Operations</div>

        <a href="{{ route('admin.housekeeping.index') }}" class="sidebar-link {{ request()->routeIs('admin.housekeeping*') ? 'active' : '' }}">
            <i class="bi bi-brush"></i>
            <span>Housekeeping</span>
        </a>

        <a href="{{ route('admin.folios.index') }}" class="sidebar-link {{ request()->routeIs('admin.folios*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            <span>Folios</span>
        </a>

        <a href="{{ route('admin.night-audit.index') }}" class="sidebar-link {{ request()->routeIs('admin.night-audit*') ? 'active' : '' }}">
            <i class="bi bi-moon-stars"></i>
            <span>Night Audit</span>
        </a>

        <div class="sidebar-section">Reports</div>

        <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i>
            <span>Reports</span>
        </a>

        <div class="sidebar-section">Integrations</div>

        <a href="{{ route('admin.channel-manager.index') }}" class="sidebar-link {{ request()->routeIs('admin.channel-manager*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            <span>Channel Manager</span>
        </a>

        <a href="{{ route('admin.dead-letter.index') }}" class="sidebar-link {{ request()->routeIs('admin.dead-letter*') ? 'active' : '' }}">
            <i class="bi bi-bug"></i>
            <span>Dead-Letter Queue</span>
        </a>

        <a href="{{ route('admin.system.health') }}" class="sidebar-link {{ request()->routeIs('admin.system.health*') ? 'active' : '' }}">
            <i class="bi bi-heart-pulse"></i>
            <span>System Health</span>
        </a>

        <div class="sidebar-section">Scale & Allotments</div>

        <a href="{{ route('admin.group-allotments.index') }}" class="sidebar-link {{ request()->routeIs('admin.group-allotments*') ? 'active' : '' }}">
            <i class="bi bi-briefcase"></i>
            <span>Corporate & Allotments</span>
        </a>

        <a href="{{ route('admin.loyalty.index') }}" class="sidebar-link {{ request()->routeIs('admin.loyalty*') ? 'active' : '' }}">
            <i class="bi bi-award"></i>
            <span>Loyalty Program</span>
        </a>

        <div class="sidebar-section">Inventory & Pricing</div>

        <a href="{{ route('admin.inventory.matrix') }}" class="sidebar-link {{ request()->routeIs('admin.inventory*') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>Availability Matrix</span>
        </a>

        <a href="{{ route('admin.quotes.index') }}" class="sidebar-link {{ request()->routeIs('admin.quotes*') ? 'active' : '' }}">
            <i class="bi bi-calculator"></i>
            <span>Quote Inspector</span>
        </a>

        <div class="sidebar-section">Configuration</div>

        <a href="{{ route('admin.properties.index') }}" class="sidebar-link {{ request()->routeIs('admin.properties*') ? 'active' : '' }}">
            <i class="bi bi-buildings"></i>
            <span>Properties</span>
        </a>

        <a href="{{ route('admin.room-types.index') }}" class="sidebar-link {{ request()->routeIs('admin.room-types*') ? 'active' : '' }}">
            <i class="bi bi-door-closed"></i>
            <span>Room Types</span>
        </a>

        <a href="{{ route('admin.rooms.index') }}" class="sidebar-link {{ request()->routeIs('admin.rooms*') ? 'active' : '' }}">
            <i class="bi bi-key"></i>
            <span>Physical Rooms</span>
        </a>

        <a href="{{ route('admin.rate-plans.index') }}" class="sidebar-link {{ request()->routeIs('admin.rate-plans*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i>
            <span>Rate Plans</span>
        </a>

        <a href="{{ route('admin.taxes.index') }}" class="sidebar-link {{ request()->routeIs('admin.taxes*') ? 'active' : '' }}">
            <i class="bi bi-percent"></i>
            <span>Taxes & Fees</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span>Users</span>
        </a>

        @if(auth()->user()->is_platform_admin)
        <a href="{{ route('admin.organizations.index') }}" class="sidebar-link {{ request()->routeIs('admin.organizations*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            <span>Organizations</span>
        </a>
        @endif

        <!-- Sidebar Bottom Promo Card -->
        <div class="p-3 my-3 rounded-4 border" style="background: #edf8e8; border-color: #d2ecd0 !important;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-rocket-takeoff-fill text-dark"></i>
                <div class="fw-bold text-dark small" style="line-height: 1.2;">Elevate Standards</div>
            </div>
            <p class="text-muted mb-2" style="font-size: 0.72rem; line-height: 1.3;">Enhanced Reporting, Faster Check-Ins & PMS Tools.</p>
            <button class="btn btn-sm btn-primary w-100 fw-bold" style="font-size: 0.76rem;">Update Now</button>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="#" class="sidebar-user" data-bs-toggle="dropdown">
            <div class="sidebar-avatar">{{ substr(auth()->user()->name, 0, 2) }}</div>
            <div style="min-width:0">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-role">{{ auth()->user()->roles->first()?->name ?? 'Administrator' }}</div>
            </div>
            <i class="bi bi-three-dots-vertical ms-auto text-muted"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius: 14px; border: 1px solid var(--border-color);">
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger fw-semibold">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>

<!-- Topbar -->
<header class="topbar">
    <button class="topbar-icon-btn d-md-none" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="me-auto">
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        @hasSection('breadcrumb')
        <div class="topbar-breadcrumb">@yield('breadcrumb')</div>
        @endif
    </div>

    <!-- Search Input -->
    <div class="topbar-search d-none d-lg-block">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search room, guest, book, etc...">
    </div>

    <!-- Property Switcher -->
    @php $currentProperty = app()->bound('current.property') ? app('current.property') : null; @endphp
    @if($currentProperty)
    <div class="dropdown">
        <div class="property-switcher" data-bs-toggle="dropdown" id="propertySwitcherBtn">
            <div class="property-dot"></div>
            <span class="fw-bold text-truncate" style="max-width: 140px;">{{ $currentProperty->name }}</span>
            <i class="bi bi-chevron-down ms-1" style="font-size: 0.7rem; color: var(--text-secondary)"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius: 16px; border: 1px solid var(--border-color); min-width: 220px;">
            <li><h6 class="dropdown-header">Switch Property</h6></li>
            @php
                $myProperties = auth()->user()->is_platform_admin
                    ? \App\Infrastructure\Persistence\Property::where('status', 'active')->get()
                    : \App\Infrastructure\Persistence\Property::where('organization_id', auth()->user()->organization_id)->where('status', 'active')->get();
            @endphp
            @foreach($myProperties as $prop)
            <li>
                <form method="POST" action="{{ route('admin.switch.property') }}">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $prop->id }}">
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 {{ $currentProperty->id === $prop->id ? 'active' : '' }}"
                        style="font-size: 0.85rem; padding: 8px 16px;">
                        <div style="width: 6px; height: 6px; border-radius: 50%; background: {{ $currentProperty->id === $prop->id ? '#10b981' : '#d1d5db' }};"></div>
                        {{ $prop->name }}
                        @if($currentProperty->id === $prop->id)
                            <i class="bi bi-check ms-auto text-success"></i>
                        @endif
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Notifications Dropdown Popup -->
    @php
        $latestNotifications = \App\Infrastructure\Persistence\AuditLog::with('actor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $unreadCount = max(3, $latestNotifications->count());
    @endphp

    <div class="dropdown">
        <button class="topbar-icon-btn position-relative border-0" data-bs-toggle="dropdown" id="notificationDropdownBtn" aria-expanded="false" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="topbar-badge">{{ $unreadCount }}</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end shadow-lg p-0 border-0 rounded-4 overflow-hidden mt-2" 
             aria-labelledby="notificationDropdownBtn" 
             style="width: 360px; max-width: 90vw; z-index: 1050;">
            
            <!-- Header -->
            <div class="p-3 bg-dark text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bell-fill text-warning"></i>
                    <h6 class="mb-0 fw-bold text-white fs-6">Notifications & Alerts</h6>
                </div>
                <span class="badge bg-primary rounded-pill px-2.5 py-1 small" style="font-size: 0.7rem;">
                    {{ $unreadCount }} New
                </span>
            </div>

            <!-- Notification Items List -->
            <div class="notification-list overflow-auto" style="max-height: 340px;">
                @forelse($latestNotifications as $notif)
                    @php
                        $iconClass = match(true) {
                            str_contains($notif->action, 'reservation')  => 'bi-calendar-check-fill bg-success-subtle text-success',
                            str_contains($notif->action, 'check_in') || str_contains($notif->action, 'auth.login') => 'bi-box-arrow-in-right bg-primary-subtle text-primary',
                            str_contains($notif->action, 'room')        => 'bi-key-fill bg-warning-subtle text-warning-emphasis',
                            str_contains($notif->action, 'inventory')   => 'bi-grid-3x3-gap-fill bg-info-subtle text-info-emphasis',
                            default                                     => 'bi-info-circle-fill bg-secondary-subtle text-secondary'
                        };
                        $actionTitle = ucwords(str_replace(['.', '_'], ' ', $notif->action));
                        $actorName   = $notif->actor?->name ?: 'System Admin';
                        $timeAgo     = $notif->created_at ? $notif->created_at->diffForHumans() : 'Just now';
                    @endphp
                    <a href="{{ route('admin.reports.index', ['tab' => 'audit']) }}" class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3 text-wrap">
                        <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center flex-shrink-0 {{ $iconClass }}" style="width: 38px; height: 38px;">
                            <i class="bi {{ strtok($iconClass, ' ') }} fs-6"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark text-truncate" style="font-size: 0.82rem;">{{ $actionTitle }}</span>
                                <small class="text-muted ms-1" style="font-size: 0.7rem;">{{ $timeAgo }}</small>
                            </div>
                            <p class="text-muted mb-0 small lh-sm text-truncate-2" style="font-size: 0.75rem;">
                                Action by <strong>{{ $actorName }}</strong>
                                @if($notif->target_type)
                                    &bull; {{ $notif->target_type }} #{{ Str::limit($notif->target_id, 8) }}
                                @endif
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="p-3 border-bottom d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-success-subtle text-success p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="bi bi-calendar-check-fill fs-6"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.82rem;">System Active</span>
                                <small class="text-muted" style="font-size: 0.7rem;">1m ago</small>
                            </div>
                            <p class="text-muted mb-0 small" style="font-size: 0.75rem;">PMS System operational and ready.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Footer -->
            <div class="p-2.5 bg-light text-center border-top">
                <a href="{{ route('admin.reports.index', ['tab' => 'audit']) }}" class="text-decoration-none fw-bold small text-primary">
                    View All Audit Logs & Alerts &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Settings Dropdown -->
    <div class="dropdown">
        <button class="topbar-icon-btn border-0" data-bs-toggle="dropdown" id="settingsDropdownBtn" aria-expanded="false" title="Quick Settings">
            <i class="bi bi-gear"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-2 border-0 mt-2" aria-labelledby="settingsDropdownBtn" style="min-width: 220px;">
            <li><h6 class="dropdown-header text-uppercase fw-bold text-muted small">System Configuration</h6></li>
            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.properties.index') }}"><i class="bi bi-buildings me-2 text-primary"></i>Properties</a></li>
            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.room-types.index') }}"><i class="bi bi-door-closed me-2 text-info"></i>Room Types</a></li>
            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.rate-plans.index') }}"><i class="bi bi-tags me-2 text-warning"></i>Rate Plans</a></li>
            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2 text-success"></i>Users & Roles</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.system.health') }}"><i class="bi bi-heart-pulse me-2 text-danger"></i>System Health</a></li>
        </ul>
    </div>
</header>

<!-- Loading overlay -->
<div class="spinner-overlay" id="loadingOverlay">
    <div class="text-center">
        <div class="spinner-border" style="color: var(--sidebar-accent);" role="status"></div>
        <div class="text-white mt-2 small fw-bold">Processing...</div>
    </div>
</div>

<!-- Main Content -->
<main class="main-content fade-in">
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4 rounded-4 shadow-sm border-0" role="alert" style="background: #dcfce7; color: #15803d;">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible mb-4 rounded-4 shadow-sm border-0" role="alert" style="background: #fee2e2; color: #b91c1c;">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4 rounded-4 shadow-sm border-0" role="alert" style="background: #fee2e2; color: #b91c1c;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Sidebar toggle (mobile)
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('open');
    });

    // AJAX setup — attach CSRF token to all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Helper: show/hide loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    function showLoading() { loadingOverlay.classList.add('active'); }
    function hideLoading() { loadingOverlay.classList.remove('active'); }

    // Generic AJAX helper
    async function ajaxRequest(url, method, data, {showLoader = true} = {}) {
        if (showLoader) showLoading();
        try {
            const response = await fetch(url, {
                method: method.toUpperCase(),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: data ? JSON.stringify(data) : undefined,
            });
            const json = await response.json();
            if (!response.ok) throw json;
            return json;
        } finally {
            if (showLoader) hideLoading();
        }
    }

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
</script>

@stack('scripts')
</body>
</html>
