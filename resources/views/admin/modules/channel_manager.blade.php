@extends('layouts.app')

@section('title', 'Channel Manager')
@section('page-title', 'OTA Channel Manager & Integrations')
@section('breadcrumb', 'Integrations › Channel Manager')

@section('content')

<div class="page-header flex-wrap gap-3">
    <div>
        <h1>Channel Manager Dashboard</h1>
        <p>Inbound/outbound channel manager sync with Booking.com, Airbnb, Expedia & Agoda for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>

    <div>
        <form method="POST" action="{{ route('admin.channel-manager.sync') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-arrow-repeat me-1"></i> Sync Inventory & Rates
            </button>
        </form>
    </div>
</div>

<!-- Channel Connection Status Cards -->
<div class="row g-4 mb-4">
    @foreach($supportedProviders as $key => $provider)
        @php
            $conn = $connections->get($key);
            $isActive = $conn && $conn->status === 'active';
        @endphp
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-top border-3 {{ $isActive ? 'border-success' : 'border-secondary' }}">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon {{ $isActive ? 'green' : 'blue' }}">
                                <i class="bi {{ $provider['icon'] }}"></i>
                            </div>
                            <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                                {{ $isActive ? 'Active' : 'Disconnected' }}
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $provider['name'] }}</h5>
                        <p class="text-muted small mb-3">{{ $provider['category'] }} Channel Integration</p>
                        
                        @if($conn && $conn->last_sync_at)
                            <div class="small text-muted">
                                <i class="bi bi-clock me-1"></i>Last Synced: {{ $conn->last_sync_at->diffForHumans() }}
                            </div>
                        @else
                            <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Never synced</div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-sm {{ $isActive ? 'btn-outline-secondary' : 'btn-outline-primary' }} w-100"
                                data-bs-toggle="modal" data-bs-target="#configureModal_{{ $key }}">
                            <i class="bi bi-gear me-1"></i> {{ $isActive ? 'Configure Connection' : 'Connect Channel' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configure Modal for Provider -->
        <div class="modal fade" id="configureModal_{{ $key }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.channel-manager.connection.update') }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="provider" value="{{ $key }}">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi {{ $provider['icon'] }} me-2 text-primary"></i>Configure {{ $provider['name'] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Connection Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $isActive ? 'selected' : '' }}>Active (Enabled)</option>
                                <option value="inactive" {{ !$isActive ? 'selected' : '' }}>Inactive (Disabled)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Key / Access Token</label>
                            <input type="password" name="api_key" class="form-control" placeholder="Enter provider credentials API key..." value="{{ $conn?->credentials_encrypted['api_key'] ?? '' }}">
                            <div class="form-text">Encrypted before persistence in database.</div>
                        </div>
                        <div class="p-3 bg-light rounded border">
                            <div class="small fw-bold mb-1"><i class="bi bi-link-45deg me-1"></i>Inbound Webhook Endpoint URL</div>
                            <code class="small text-break">{{ url('/api/v1/webhooks/' . $key) }}</code>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

<!-- Integration Architecture Summary -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Channel Integration Pipeline Architecture</h6>
    </div>
    <div class="card-body">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-3 border rounded">
                    <i class="bi bi-building fs-2 text-primary"></i>
                    <h6 class="fw-bold mt-2 mb-1">1. Inventory Engine</h6>
                    <p class="small text-muted mb-0">Pushes room availability & rates to channel endpoints.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded">
                    <i class="bi bi-arrow-down-up fs-2 text-success"></i>
                    <h6 class="fw-bold mt-2 mb-1">2. Inbound Webhooks</h6>
                    <p class="small text-muted mb-0">Receives real-time reservations via <code>/api/v1/webhooks/{provider}</code>.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded">
                    <i class="bi bi-shield-check fs-2 text-info"></i>
                    <h6 class="fw-bold mt-2 mb-1">3. Deduplication</h6>
                    <p class="small text-muted mb-0">Prevents duplicate bookings using <code>provider_event_id</code>.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded">
                    <i class="bi bi-bug fs-2 text-danger"></i>
                    <h6 class="fw-bold mt-2 mb-1">4. Dead-Letter Fallback</h6>
                    <p class="small text-muted mb-0">Routes failed payloads to dead-letter queue for replay.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
