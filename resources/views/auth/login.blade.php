@extends('layouts.auth')

@section('title', 'Sign In — Management Portal')

@section('content')
<div>
    <div class="mb-4">
        <h1 class="form-title-main">Welcome back</h1>
        <p class="form-subtitle-main">Sign in to your hotel management workspace</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4 d-flex align-items-center rounded-3 p-3" style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;">
        <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
        <div class="small fw-bold">{{ $errors->first() }}</div>
    </div>
    @endif

    @if(session('status'))
    <div class="alert alert-success mb-4 d-flex align-items-center rounded-3 p-3" style="background: #dcfce7; border: 1px solid #86efac; color: #166534;">
        <i class="bi bi-check-circle-fill me-3 fs-5"></i>
        <div class="small fw-bold">{{ session('status') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('auth.login.post') }}" id="loginForm">
        @csrf

        <!-- Email Field -->
        <div class="mb-3">
            <label class="form-label-dashboard" for="email">Work Email Address</label>
            <div class="input-group-dashboard">
                <i class="bi bi-envelope input-icon"></i>
                <input type="email" class="form-control-dashboard @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       placeholder="name@hotelgroup.com" autofocus required>
            </div>
            @error('email')
            <div class="invalid-feedback d-block mt-1 small text-danger fw-bold">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label-dashboard mb-0" for="password">Security Password</label>
                <a href="#" onclick="alert('Please contact your System Administrator to reset your staff credentials.'); return false;" class="text-decoration-none small fw-bold text-dark">Forgot password?</a>
            </div>
            <div class="input-group-dashboard">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" class="form-control-dashboard @error('password') is-invalid @enderror"
                       id="password" name="password"
                       placeholder="••••••••••••" required>
                <button type="button" class="btn btn-link position-absolute end-0 text-muted pe-3 text-decoration-none" id="togglePassword" title="Toggle password visibility" style="z-index: 5;">
                    <i class="bi bi-eye" id="passwordEyeIcon"></i>
                </button>
            </div>
            @error('password')
            <div class="invalid-feedback d-block mt-1 small text-danger fw-bold">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me Checkbox -->
        <div class="mb-4">
            <div class="form-check d-flex align-items-center gap-2 mb-0">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" checked style="cursor: pointer;">
                <label class="form-check-label text-secondary small mb-0 fw-semibold" for="remember" style="cursor: pointer; user-select: none;">
                    Keep me signed in on this device
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-lime-primary" id="loginBtn">
            <span id="loginText" class="d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-in-right fs-5"></i> Sign In to Portal
            </span>
            <span id="loginSpinner" class="d-none">
                <i class="bi bi-arrow-repeat spin me-2"></i> Authenticating...
            </span>
        </button>
    </form>

    <!-- Quick Staff Role Switcher -->
    <div class="demo-roles-card">
        <div class="demo-roles-title">
            <span><i class="bi bi-key-fill me-1 text-warning"></i> Quick Demo Access</span>
            <span class="text-muted fw-normal" style="font-size: 0.68rem;">Click to Autofill</span>
        </div>
        <div class="d-flex flex-column gap-2">
            <button type="button" class="demo-role-btn justify-content-between" onclick="fillDemo('admin@platform.com', 'password')">
                <span><i class="bi bi-shield-lock-fill text-warning me-1"></i> Platform Admin</span>
                <span class="text-secondary small fw-normal">admin@platform.com</span>
            </button>
            <button type="button" class="demo-role-btn justify-content-between" onclick="fillDemo('manager@tembohotel.com', 'password')">
                <span><i class="bi bi-briefcase-fill text-info me-1"></i> Org Manager</span>
                <span class="text-secondary small fw-normal">manager@tembohotel.com</span>
            </button>
            <button type="button" class="demo-role-btn justify-content-between" onclick="fillDemo('reception@tembohotel.com', 'password')">
                <span><i class="bi bi-person-workspace text-success me-1"></i> Front Desk</span>
                <span class="text-secondary small fw-normal">reception@tembohotel.com</span>
            </button>
        </div>
    </div>

    <div class="text-center text-muted small mt-4">
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
            icon.className = 'bi bi-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'bi bi-eye';
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
        
        emailInput.style.borderColor = '#151b16';
        passwordInput.style.borderColor = '#151b16';
        
        setTimeout(() => {
            emailInput.style.borderColor = '';
            passwordInput.style.borderColor = '';
        }, 1000);
    }
</script>
@endpush
