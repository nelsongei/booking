@extends('layouts.app')

@section('title', 'Payment Integration Settings — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>Payment Gateway & API Integration Settings</h1>
        <p>Configure live API credentials for Safaricom M-Pesa Daraja and Stripe Payments</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.integrations.settings.update') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Safaricom M-Pesa Daraja API Settings Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-phone me-2"></i>Safaricom M-Pesa Daraja API
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">Live STK Push</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">M-Pesa Environment Mode</label>
                        <select name="mpesa_env" class="form-select">
                            <option value="sandbox" {{ ($config['mpesa_env'] ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing Simulator)</option>
                            <option value="production" {{ ($config['mpesa_env'] ?? '') === 'production' ? 'selected' : '' }}>Production (Live Safaricom Daraja)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Consumer Key</label>
                        <input type="text" name="mpesa_consumer_key" class="form-control" value="{{ $config['mpesa_consumer_key'] }}" placeholder="e.g. 8kX9A2b3c4d5e6f7g8h9i0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Consumer Secret</label>
                        <input type="password" name="mpesa_consumer_secret" class="form-control" value="{{ $config['mpesa_consumer_secret'] }}" placeholder="••••••••••••••••••••••••">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Shortcode (Paybill / Till Number)</label>
                        <input type="text" name="mpesa_shortcode" class="form-control" value="{{ $config['mpesa_shortcode'] }}" placeholder="e.g. 174379 or 600100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lipa Na M-Pesa Passkey</label>
                        <input type="password" name="mpesa_passkey" class="form-control" value="{{ $config['mpesa_passkey'] }}" placeholder="bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stripe Payments Settings Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-credit-card me-2"></i>Stripe Payments & Terminal
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">Card & PaymentIntents</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" name="stripe_key" class="form-control" value="{{ $config['stripe_key'] }}" placeholder="pk_live_51... or pk_test_...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="password" name="stripe_secret" class="form-control" value="{{ $config['stripe_secret'] }}" placeholder="sk_live_51... or sk_test_...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Webhook Signing Secret</label>
                        <input type="password" name="stripe_webhook_secret" class="form-control" value="{{ $config['stripe_webhook_secret'] }}" placeholder="whsec_...">
                    </div>

                    <div class="p-3 bg-light rounded-3 mt-4 border">
                        <div class="fw-bold small text-dark"><i class="bi bi-shield-check text-success me-1"></i>Security & Redaction</div>
                        <div class="text-muted small">API secret keys are stored securely and automatically redacted in application error logs.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button Row -->
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">
                <i class="bi bi-check-lg me-1"></i>Save Integration Settings
            </button>
        </div>
    </div>
</form>
@endsection
