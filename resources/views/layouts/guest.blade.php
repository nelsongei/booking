<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Luxury Reservations') — {{ $property->name ?? 'Enterprise Hotel Suite' }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 & Google Luxury Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <!-- Flatpickr Range Calendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* ===== EXPEDIA/BOOKING STYLE DUAL-MONTH RANGE CALENDAR ===== */
        .flatpickr-calendar.multiMonth {
            width: auto !important;
            max-width: 680px;
            border-radius: 20px !important;
            box-shadow: 0 16px 45px rgba(15, 23, 42, 0.18) !important;
            border: 1px solid #e2e8f0 !important;
            padding: 16px !important;
            background: #ffffff !important;
            z-index: 9999 !important;
        }

        .flatpickr-months {
            padding: 0 8px 10px;
        }

        .flatpickr-months .flatpickr-month {
            height: 40px !important;
            color: #0f172a !important;
            font-weight: 700 !important;
        }

        .flatpickr-current-month {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
        }

        span.flatpickr-weekday {
            color: #64748b !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
        }

        .flatpickr-day {
            border-radius: 50% !important;
            color: #0f172a !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            height: 38px !important;
            line-height: 38px !important;
            margin: 2px 0 !important;
        }

        .flatpickr-day.selected, 
        .flatpickr-day.startRange, 
        .flatpickr-day.endRange {
            background: #1a73e8 !important;
            border-color: #1a73e8 !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            border-radius: 50% !important;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.4) !important;
        }

        .flatpickr-day.inRange {
            background: #e8f0fe !important;
            border-color: #e8f0fe !important;
            color: #1967d2 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .flatpickr-day.flatpickr-disabled, 
        .flatpickr-day.flatpickr-disabled:hover {
            color: #cbd5e1 !important;
            background: transparent !important;
            cursor: not-allowed !important;
        }
        :root {
            --primary-dark: {{ isset($property) ? $property->getDarkColor() : '#0f172a' }};
            --primary-obsidian: {{ isset($property) ? $property->getDarkColor() : '#0b0f19' }};
            --brand-gold: {{ isset($property) ? $property->getPrimaryColor() : '#c5a059' }};
            --brand-gold-light: rgba(197, 160, 89, 0.12);
            --brand-gold-hover: {{ isset($property) ? $property->getPrimaryColor() : '#b38e46' }};
            --brand-accent: {{ isset($property) ? $property->getPrimaryColor() : '#c5a059' }};
            --brand-accent-hover: {{ isset($property) ? $property->getAccentColor() : '#b8de28' }};
            --bg-canvas: #f8fafc;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --card-border-hover: #cbd5e1;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 12px;
            --radius-pill: 50rem;
            --shadow-sm: 0 4px 14px rgba(15, 23, 42, 0.03);
            --shadow-md: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            --shadow-lg: 0 20px 40px -10px rgba(15, 23, 42, 0.12), 0 10px 20px -8px rgba(15, 23, 42, 0.06);
            --shadow-glow: 0 0 30px rgba(197, 160, 89, 0.25);
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
            line-height: 1.6;
        }

        .font-serif {
            font-family: 'Playfair Display', Georgia, serif !important;
        }

        /* ===== ENTERPRISE NAVBAR ===== */
        .navbar-guest {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
            padding: 16px 0;
            transition: all 0.3s ease;
        }

        .brand-logo-box {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1e293b 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: var(--brand-gold); font-size: 20px;
            margin-right: 14px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.15);
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover .brand-logo-box {
            transform: scale(1.05);
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.02em;
        }

        .brand-badge {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--brand-gold);
            background: var(--brand-gold-light);
            padding: 2px 8px;
            border-radius: 6px;
            margin-left: 8px;
        }

        .nav-pill-btn {
            background: #ffffff;
            color: var(--primary-dark);
            border: 1px solid var(--card-border);
            border-radius: var(--radius-pill);
            padding: 8px 20px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-pill-btn:hover {
            background: var(--primary-dark);
            color: #ffffff;
            border-color: var(--primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        /* ===== STEP WIZARD PROGRESS BAR ===== */
        .wizard-container {
            margin-bottom: 32px;
        }

        .step-progress-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            border-radius: var(--radius-pill);
            padding: 10px 24px;
            border: 1px solid var(--card-border);
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .step-item {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-muted);
            position: relative;
            z-index: 2;
            padding: 6px 12px;
            border-radius: var(--radius-pill);
            transition: all 0.25s ease;
        }

        .step-item.active {
            color: var(--primary-dark);
            background: rgba(204, 242, 53, 0.25);
            border: 1px solid rgba(204, 242, 53, 0.6);
        }

        .step-item.completed {
            color: #059669;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f1f5f9;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 10px;
            font-weight: 800;
            transition: all 0.25s ease;
        }

        .step-item.active .step-number {
            background: var(--brand-accent);
            color: var(--primary-dark);
            box-shadow: 0 0 12px rgba(204, 242, 53, 0.6);
            transform: scale(1.08);
        }

        .step-item.completed .step-number {
            background: #d1fae5;
            color: #059669;
        }

        /* ===== CARDS & CONTAINERS ===== */
        .card-custom {
            background: var(--card-bg);
            border-radius: var(--radius-xl);
            border: 1px solid var(--card-border);
            box-shadow: var(--shadow-md);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
            overflow: hidden;
        }

        .card-custom:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--card-border-hover);
        }

        /* ===== LUXURY HERO BANNER ===== */
        .hero-banner-premium {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.88) 0%, rgba(11, 15, 25, 0.82) 100%),
                        url('/images/hero.png') center/cover no-repeat;
            color: #ffffff;
            border-radius: var(--radius-xl);
            padding: 56px 48px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hero-banner-premium::after {
            content: '';
            position: absolute;
            bottom: -80px; right: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(204, 242, 53, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-badge-star {
            background: linear-gradient(135deg, var(--brand-accent) 0%, #a3e635 100%);
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            padding: 6px 16px;
            border-radius: var(--radius-pill);
            box-shadow: 0 4px 14px rgba(204, 242, 53, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ===== BUTTONS ===== */
        .btn-brand {
            background: linear-gradient(135deg, var(--brand-accent) 0%, #b8de28 100%);
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: -0.01em;
            border-radius: var(--radius-pill);
            padding: 13px 32px;
            border: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 6px 20px rgba(204, 242, 53, 0.38);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-brand:hover {
            background: linear-gradient(135deg, #d8fa45 0%, #aee019 100%);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(204, 242, 53, 0.55);
        }

        .btn-brand:active {
            transform: translateY(0);
        }

        .btn-outline-custom {
            background: transparent;
            color: var(--primary-dark);
            border: 1.5px solid var(--primary-dark);
            font-weight: 700;
            border-radius: var(--radius-pill);
            padding: 10px 24px;
            transition: all 0.2s ease;
        }

        .btn-outline-custom:hover {
            background: var(--primary-dark);
            color: #ffffff;
        }

        /* ===== PRICING TAG & BADGES ===== */
        .price-tag {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.03em;
        }

        .timer-badge {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 8px 20px;
            border-radius: var(--radius-pill);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            animation: pulseTimer 2s infinite ease-in-out;
        }

        @keyframes pulseTimer {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
            50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }

        /* ===== FORM INPUTS ===== */
        .form-control, .form-select {
            border-radius: var(--radius-md);
            border: 1.5px solid var(--card-border);
            padding: 12px 16px;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--primary-dark);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
            outline: none;
        }

        .input-group-text {
            border-radius: var(--radius-md);
            border: 1.5px solid var(--card-border);
            background: #f8fafc;
            color: var(--text-muted);
            padding: 0 16px;
        }

        /* ===== FEATURE AMENITY BADGES ===== */
        .amenity-chip {
            background: #f1f5f9;
            color: var(--primary-dark);
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-pill);
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease;
        }

        .amenity-chip:hover {
            background: #e2e8f0;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero-banner-premium {
                padding: 32px 24px;
            }
            .step-progress-bar {
                overflow-x: auto;
                padding: 10px 14px;
                gap: 8px;
            }
            .step-text {
                display: none;
            }
            .step-item.active .step-text {
                display: inline;
            }
        }
    </style>

    @yield('styles')
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-guest sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('booking.index', ['slug' => $property->slug ?? 'tembo-hotel']) }}">
                <div class="brand-logo-box p-1 me-3 d-flex align-items-center justify-content-center" style="background: transparent; border: none; box-shadow: none; width: auto; height: 44px;">
                    @if(isset($property))
                        <img src="{{ $property->getLogoUrl('dark') }}" alt="{{ $property->name }}" style="max-height: 48px; width: auto; object-fit: contain;">
                    @else
                        <i class="fa-solid fa-crown text-warning fs-3"></i>
                    @endif
                </div>
                <div>
                    <div class="d-flex align-items-center">
                        <span class="brand-title">{{ $property->name ?? 'Tembo Hotel' }}</span>
                        <span class="brand-badge">5-Star Luxury</span>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Official Direct Booking Engine</small>
                </div>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                @if(isset($property))
                <a href="{{ $property->getOfficialWebsite() }}" target="_blank" class="d-none d-lg-inline-flex align-items-center gap-2 text-decoration-none small fw-semibold px-3 py-2 rounded-pill border" style="background: rgba(255,255,255,0.7); color: var(--primary-dark);">
                    <i class="fa-solid fa-globe text-primary"></i> {{ parse_url($property->getOfficialWebsite(), PHP_URL_HOST) }}
                </a>
                @endif
                <span class="d-none d-md-inline-flex align-items-center gap-2 text-muted small fw-semibold">
                    <i class="fa-solid fa-shield-halved text-success"></i> Best Price Guarantee
                </span>
                <a href="{{ route('booking.portal.lookup') }}" class="nav-pill-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> My Reservation
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="py-4">
        <div class="container">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm border-0" role="alert" style="background: #fee2e2; color: #b91c1c; border-left: 5px solid #dc2626 !important;">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i> <strong>Please correct the following errors:</strong>
                    </div>
                    <ul class="mb-0 ps-4 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm border-0" role="alert" style="background: #fee2e2; color: #b91c1c; border-left: 5px solid #dc2626 !important;">
                    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm border-0" role="alert" style="background: #d1fae5; color: #065f46; border-left: 5px solid #10b981 !important;">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Premium Enterprise Footer -->
    <footer class="text-light py-5 mt-5" style="background: {{ isset($property) ? $property->getPrimaryColor() : 'var(--primary-obsidian)' }} !important; border-top: 1px solid rgba(255,255,255,0.15);">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center mb-3 gap-3">
                        @if(isset($property))
                            <img src="{{ $property->getLogoUrl('light') }}" alt="{{ $property->name }}" style="max-height: 54px; width: auto; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));">
                        @else
                            <div class="brand-logo-box">
                                <i class="fa-solid fa-crown text-warning"></i>
                            </div>
                        @endif
                        <span class="fs-4 fw-extrabold text-white">{{ $property->name ?? 'Tembo Hotel' }}</span>
                    </div>
                    <p class="text-white-50 small pe-lg-4">
                        Experience unparalleled hospitality and world-class luxury direct reservations with instant room hold confirmation, best-rate guarantee, and 24/7 guest concierge support.
                    </p>
                    <div class="d-flex gap-3 text-white-50">
                        <span class="badge bg-secondary bg-opacity-25 text-light px-3 py-2 rounded-pill"><i class="fa-solid fa-lock text-success me-1"></i> 256-bit SSL Encrypted</span>
                        <span class="badge bg-secondary bg-opacity-25 text-light px-3 py-2 rounded-pill"><i class="fa-solid fa-bolt text-warning me-1"></i> Instant Confirmation</span>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <h6 class="fw-bold text-white text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.1em;">Guest Support</h6>
                    <ul class="list-unstyled text-white-50 small d-flex flex-column gap-2 mb-0">
                        <li><i class="fa-solid fa-phone text-warning me-2"></i> Concierge: {{ $property->phone ?? '+255 24 223 3005' }}</li>
                        <li><i class="fa-solid fa-envelope text-warning me-2"></i> {{ $property->email ?? 'reservations@tembohotel.com' }}</li>
                        <li><i class="fa-solid fa-clock text-warning me-2"></i> 24/7 Reception Desk</li>
                        <li><i class="fa-solid fa-map-pin text-warning me-2"></i> {{ $property->city ?? 'Zanzibar' }}, {{ $property->country ?? 'TZ' }}</li>
                        @if(isset($property) && $property->website)
                        <li><i class="fa-solid fa-globe text-warning me-2"></i> <a href="{{ $property->website }}" target="_blank" class="text-white-50 text-decoration-none">{{ parse_url($property->website, PHP_URL_HOST) }}</a></li>
                        @endif
                    </ul>
                </div>

                <div class="col-lg-4 col-sm-6">
                    <h6 class="fw-bold text-white text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.1em;">Direct Booking Privilege</h6>
                    <p class="text-white-50 small mb-3">
                        When you book directly through our official booking engine, you get priority room upgrades (subject to availability), complimentary high-speed Wi-Fi, and flexible check-in options.
                    </p>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-award text-warning fs-3"></i>
                        <span class="text-white fw-bold small">Certified Official Booking Portal</span>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-secondary opacity-25">

            <div class="d-flex flex-wrap justify-content-between align-items-center text-white-50 small">
                <p class="mb-0">&copy; {{ date('Y') }} {{ $property->name ?? 'Tembo Hotel' }}. All rights reserved.</p>
                <p class="mb-0">Powered by <strong>Enterprise Multi-Property PMS Suite</strong></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle & Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>

