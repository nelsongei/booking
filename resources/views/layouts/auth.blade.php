<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — {{ config('app.name', 'Hotel Booking Platform') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 & Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-obsidian: #0b1120;
            --secondary-obsidian: #0f172a;
            --accent-gold: #c5a059;
            --accent-gold-hover: #dfb76c;
            --accent-gold-light: rgba(197, 160, 89, 0.15);
            --border-glass: rgba(255, 255, 255, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background-color: var(--primary-obsidian);
            background-image: 
                radial-gradient(at 0% 0%, rgba(197, 160, 89, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.95) 0px, transparent 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            margin: 0;
            position: relative;
            overflow-x: hidden;
            color: #f8fafc;
        }

        .font-serif {
            font-family: 'Playfair Display', serif;
        }

        /* Subtle Ambient Glow Circles */
        .ambient-glow-1 {
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(197, 160, 89, 0.15) 0%, transparent 70%);
            top: -150px; left: -150px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(30, 58, 138, 0.25) 0%, transparent 70%);
            bottom: -200px; right: -150px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* Container Card */
        .auth-container {
            width: 100%;
            max-width: 1040px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-glass);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        /* Left Hero Panel */
        .auth-hero-panel {
            background-image: linear-gradient(180deg, rgba(11, 17, 32, 0.45) 0%, rgba(11, 17, 32, 0.94) 100%), url('{{ asset("images/login_hero_bg.png") }}');
            background-size: cover;
            background-position: center;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            min-height: 560px;
            border-right: 1px solid var(--border-glass);
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(197, 160, 89, 0.3);
            padding: 8px 18px;
            border-radius: 50px;
            backdrop-filter: blur(12px);
            width: fit-content;
        }

        .brand-icon-box {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #c5a059, #9a7b38);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #0b1120; font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(197, 160, 89, 0.4);
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: #ffffff;
            letter-spacing: 0.02em;
        }

        .hero-headline {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.25;
            color: #ffffff;
            margin-bottom: 16px;
        }

        .hero-description {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Right Form Panel */
        .auth-form-panel {
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .form-header-sub {
            color: #94a3b8;
            font-size: 0.88rem;
            margin-bottom: 32px;
        }

        /* Form Controls */
        .form-label {
            color: #cbd5e1;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-custom .input-icon {
            position: absolute;
            left: 16px;
            color: var(--accent-gold);
            font-size: 1rem;
            z-index: 5;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control-luxury {
            width: 100%;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            color: #ffffff !important;
            padding: 13px 16px 13px 44px;
            font-size: 0.92rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.25s ease;
        }

        .form-control-luxury:focus {
            background: rgba(30, 41, 59, 0.85);
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.18);
            outline: none;
        }

        .form-control-luxury::placeholder {
            color: #64748b;
        }

        /* Chrome/Safari Autocomplete dark fix */
        .form-control-luxury:-webkit-autofill,
        .form-control-luxury:-webkit-autofill:hover, 
        .form-control-luxury:-webkit-autofill:focus {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0px 1000px #1e293b inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .password-toggle-btn {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 8px;
            font-size: 0.95rem;
            z-index: 5;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: var(--accent-gold);
        }

        /* Checkbox & Links */
        .form-check-input-gold {
            width: 18px; height: 18px;
            background-color: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            cursor: pointer;
        }

        .form-check-input-gold:checked {
            background-color: var(--accent-gold);
            border-color: var(--accent-gold);
        }

        .form-check-label-custom {
            color: #94a3b8;
            font-size: 0.83rem;
            cursor: pointer;
            user-select: none;
        }

        .forgot-link {
            color: var(--accent-gold);
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .forgot-link:hover {
            color: var(--accent-gold-hover);
            text-decoration: underline;
        }

        /* Luxury Button */
        .btn-luxury-primary {
            width: 100%;
            background: linear-gradient(135deg, #c5a059 0%, #d4af37 50%, #9a7b38 100%);
            border: none;
            border-radius: 14px;
            color: #0b1120;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 14px;
            letter-spacing: 0.03em;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(197, 160, 89, 0.3);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-luxury-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(197, 160, 89, 0.45);
            color: #0b1120;
        }

        .btn-luxury-primary:active {
            transform: translateY(0);
        }

        /* Quick Demo Credentials Box */
        .demo-credentials-box {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 16px;
            margin-top: 28px;
        }

        .demo-title {
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 700;
            text-uppercase: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .demo-pill:hover {
            background: var(--accent-gold-light);
            border-color: var(--accent-gold);
            color: var(--accent-gold-hover);
        }

        .auth-footer-text {
            color: #64748b;
            font-size: 0.78rem;
            text-align: center;
            margin-top: 24px;
        }

        @media (max-width: 991.98px) {
            .auth-hero-panel {
                display: none;
            }
            .auth-form-panel {
                padding: 40px 28px;
            }
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="auth-container">
        <div class="row g-0">
            <!-- Left Hero Showcase Panel -->
            <div class="col-lg-6 auth-hero-panel">
                <div>
                    <div class="brand-badge mb-4">
                        <div class="brand-icon-box">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <span class="brand-name">{{ config('app.name', 'Grand Horizon Resort') }}</span>
                    </div>

                    <div class="mt-4">
                        <h2 class="hero-headline font-serif">Elevate Hospitality Operations to 5-Star Perfection.</h2>
                        <p class="hero-description">
                            Enterprise multi-property management suite featuring real-time room status tracking, automated dynamic rate management, row-locked inventory controls, and instant guest portal check-ins.
                        </p>
                    </div>
                </div>

                <div>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="feature-pill"><i class="fa-solid fa-shield-halved text-warning"></i> 256-Bit SSL Encrypted</span>
                        <span class="feature-pill"><i class="fa-solid fa-layer-group text-info"></i> Multi-Property Ready</span>
                        <span class="feature-pill"><i class="fa-solid fa-bolt text-success"></i> Realtime Inventory Lock</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top border-white border-opacity-10 text-white-50 small">
                        <span><i class="fa-solid fa-circle text-success me-1 font-xs"></i> All Systems Operational</span>
                        <span>v2.5.0 Enterprise</span>
                    </div>
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="col-lg-6 auth-form-panel">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
