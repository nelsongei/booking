@extends('layouts.auth')

@section('title', 'Sign In — Management Portal')

@section('content')
<div>
    <div class="mb-4">
        <h1 class="form-header-title">Welcome back</h1>
        <p class="form-header-sub">Sign in to your hotel management workspace</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4 d-flex align-items-center rounded-3 p-3" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
        <i class="fa-solid fa-circle-exclamation me-3 fs-5"></i>
        <div class="small fw-medium">{{ $errors->first() }}</div>
    </div>
    @endif

    @if(session('status'))
    <div class="alert alert-success mb-4 d-flex align-items-center rounded-3 p-3" style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #86efac;">
        <i class="fa-solid fa-circle-check me-3 fs-5"></i>
        <div class="small fw-medium">{{ session('status') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('auth.login.post') }}" id="loginForm">
        @csrf

        <!-- Email Field -->
        <div class="mb-4">
            <label class="form-label" for="email">Work Email Address</label>
            <div class="input-group-custom">
                <i class="fa-solid fa-envelope input-icon"></i>
                <input type="email" class="form-control-luxury @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       placeholder="name@hotelgroup.com" autofocus required>
            </div>
            @error('email')
            <div class="invalid-feedback d-block mt-1 small" style="color: #fca5a5;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="password">Security Password</label>
                <a href="#" onclick="alert('Please contact your System Administrator to reset your staff credentials.'); return false;" class="forgot-link">Forgot password?</a>
            </div>
            <div class="input-group-custom">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" class="form-control-luxury @error('password') is-invalid @enderror"
                       id="password" name="password"
                       placeholder="••••••••••••" required>
                <button type="button" class="password-toggle-btn" id="togglePassword" title="Toggle password visibility">
                    <i class="fa-solid fa-eye" id="passwordEyeIcon"></i>
                </button>
            </div>
            @error('password')
            <div class="invalid-feedback d-block mt-1 small" style="color: #fca5a5;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me Checkbox -->
        <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="form-check d-flex align-items-center gap-2 mb-0">
                <input class="form-check-input form-check-input-gold" type="checkbox" id="remember" name="remember" checked>
                <label class="form-check-label-custom mb-0" for="remember">
                    Keep me signed in on this device
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-luxury-primary" id="loginBtn">
            <span id="loginText" class="d-flex align-items-center justify-content-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In to Portal
            </span>
            <span id="loginSpinner" class="d-none">
                <i class="fa-solid fa-circle-notch fa-spin me-2"></i> Authenticating Credentials...
            </span>
        </button>
    </form>

    <!-- Quick Demo Logins Helper Card -->
    <div class="demo-credentials-box">
        <div class="demo-title">
            <span><i class="fa-solid fa-key me-1 text-warning"></i> Quick Demo Access</span>
            <span class="text-muted fw-normal" style="font-size: 0.7rem;">Click to Autofill</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="demo-pill" onclick="fillDemo('admin@platform.com', 'password')">
                <i class="fa-solid fa-user-shield text-warning"></i> Platform Admin
            </button>
            <button type="button" class="demo-pill" onclick="fillDemo('manager@tembohotel.com', 'password')">
                <i class="fa-solid fa-user-tie text-info"></i> Org Manager
            </button>
            <button type="button" class="demo-pill" onclick="fillDemo('reception@tembohotel.com', 'password')">
                <i class="fa-solid fa-concierge-bell text-success"></i> Front Desk
            </button>
        </div>
    </div>

    <div class="auth-footer-text">
        &copy; {{ date('Y') }} {{ config('app.name', 'Hotel Booking Platform') }}. All rights reserved.
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Password Visibility Toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('passwordEyeIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    });

    // Loading Spinner on Submit
    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('loginText').classList.add('d-none');
        document.getElementById('loginSpinner').classList.remove('d-none');
        document.getElementById('loginBtn').disabled = true;
    });

    // Quick Fill Demo Credentials Function
    function fillDemo(email, password) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        emailInput.value = email;
        passwordInput.value = password;
        
        // Add subtle flash effect
        emailInput.style.borderColor = '#c5a059';
        passwordInput.style.borderColor = '#c5a059';
        
        setTimeout(() => {
            emailInput.style.borderColor = '';
            passwordInput.style.borderColor = '';
        }, 1000);
    }
</script>
@endpush
