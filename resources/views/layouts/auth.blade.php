<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — {{ config('app.name', 'Hotel Booking Platform') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --body-bg: #f3f6f3;
            --card-bg: #ffffff;
            --accent-lime: #d6f843;
            --accent-lime-hover: #ccf235;
            --text-primary: #161c17;
            --text-secondary: #6e7870;
            --border-color: #e5ebe5;
            --radius-pill: 50rem;
            --radius-card: 24px;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .auth-split-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* Left Hero Showcase Panel */
        .auth-hero-section {
            flex: 1.2;
            background-image: linear-gradient(135deg, rgba(22, 28, 23, 0.7) 0%, rgba(22, 28, 23, 0.92) 100%), url('{{ asset("images/login_hero_bg.png") }}');
            background-size: cover;
            background-position: center;
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 1px solid var(--border-color);
        }

        .auth-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 8px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: fit-content;
        }

        .brand-icon-lime {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--accent-lime);
            color: #151b16;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(214, 248, 67, 0.4);
        }

        .brand-title-text {
            font-weight: 800;
            font-size: 1.15rem;
            color: #ffffff;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .brand-sub-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .hero-title-headline {
            font-size: 2.85rem;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .hero-subtitle-desc {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 0;
            font-weight: 400;
        }

        .auth-hero-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Right Form Section */
        .auth-form-section {
            flex: 1;
            background: var(--body-bg);
            padding: 60px 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .auth-form-card {
            background: #ffffff;
            border-radius: var(--radius-card);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 44px;
        }

        .form-title-main {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 6px;
            letter-spacing: -0.4px;
        }

        .form-subtitle-main {
            color: var(--text-secondary);
            font-size: 0.88rem;
            margin-bottom: 32px;
            font-weight: 500;
        }

        .form-label-dashboard {
            color: var(--text-primary);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .input-group-dashboard {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-dashboard i.input-icon {
            position: absolute;
            left: 16px;
            color: var(--text-secondary);
            font-size: 1rem;
            pointer-events: none;
            z-index: 5;
        }

        .form-control-dashboard {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e0e6e0;
            border-radius: 12px;
            color: var(--text-primary) !important;
            padding: 12px 16px 12px 46px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control-dashboard:focus {
            background: #ffffff;
            border-color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(214, 248, 67, 0.4);
            outline: none;
        }

        .btn-lime-primary {
            width: 100%;
            background: var(--accent-lime);
            border: none;
            border-radius: var(--radius-pill);
            color: #151b16;
            font-weight: 800;
            font-size: 0.95rem;
            padding: 13px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(214, 248, 67, 0.35);
            transition: all 0.2s ease;
        }

        .btn-lime-primary:hover {
            background: var(--accent-lime-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(214, 248, 67, 0.5);
            color: #151b16;
        }

        /* Demo Role Pills */
        .demo-roles-card {
            background: #fafcfb;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            margin-top: 24px;
        }

        .demo-roles-title {
            color: var(--text-secondary);
            font-size: 0.72rem;
            font-weight: 700;
            text-uppercase: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-role-btn {
            background: #ffffff;
            border: 1px solid #e0e6e0;
            color: var(--text-primary);
            font-size: 0.78rem;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        .demo-role-btn:hover {
            border-color: #151b16;
            background: #f3f6f3;
        }

        @media (max-width: 991.98px) {
            .auth-hero-section { display: none; }
            .auth-form-section { padding: 40px 20px; min-height: 100vh; }
            .auth-form-card { padding: 28px 20px; }
        }
    </style>
</head>
<body>

    <div class="auth-split-wrapper">
        <!-- Left Hero Showcase Panel -->
        <div class="auth-hero-section">
            <div>
                <div class="auth-brand-badge">
                    <div class="brand-icon-lime">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <div>
                        <div class="brand-title-text">{{ config('app.name', 'Hotel Booking Platform') }}</div>
                        <div class="brand-sub-text">PMS & Booking Engine</div>
                    </div>
                </div>
            </div>

            <div>
                <h1 class="hero-title-headline">Elevate Hospitality Operations to 5-Star Perfection.</h1>
                <p class="hero-subtitle-desc">
                    Enterprise multi-property management suite featuring real-time room status tracking, automated dynamic rate management, row-locked inventory controls, and instant guest portal check-ins.
                </p>
            </div>

            <div class="auth-hero-footer">
                <span><i class="bi bi-circle-fill text-success me-2" style="font-size: 0.65rem;"></i> All Systems Operational</span>
                <span>v2.5.0 Enterprise</span>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-form-section">
            <div class="auth-form-card">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
