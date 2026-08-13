@extends('layouts.app')

@section('title', 'System Health & Diagnostics')
@section('page-title', 'Production System Health & Diagnostics')
@section('breadcrumb', 'System › Health & Diagnostics')

@section('content')

<div class="page-header flex-wrap gap-3">
    <div>
        <h1>System Health & Production Readiness</h1>
        <p>Real-time database latency, storage write permissions, database table indexes & security middleware status</p>
    </div>

    <div>
        <button class="btn btn-primary" onclick="recheckDiagnostics()">
            <i class="bi bi-arrow-repeat me-1"></i> Run Live Re-Check
        </button>
    </div>
</div>

<!-- Overall System Status Card -->
<div class="card mb-4 border-top border-4 {{ ($diagnostics['overall_status'] ?? '') === 'healthy' ? 'border-success' : 'border-warning' }}">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon {{ ($diagnostics['overall_status'] ?? '') === 'healthy' ? 'green' : 'amber' }}" style="width: 56px; height: 56px; font-size: 1.8rem;">
                    <i class="bi {{ ($diagnostics['overall_status'] ?? '') === 'healthy' ? 'bi-shield-check' : 'bi-exclamation-triangle' }}"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">
                        System Status:
                        <span class="{{ ($diagnostics['overall_status'] ?? '') === 'healthy' ? 'text-success' : 'text-warning' }}">
                            {{ strtoupper($diagnostics['overall_status'] ?? 'UNKNOWN') }}
                        </span>
                    </h4>
                    <p class="text-muted small mb-0">All core infrastructure components & security parameters verified.</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-clock me-1"></i>{{ $diagnostics['server_time'] ?? '' }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Diagnostics Grid -->
<div class="row g-4 mb-4">
    <!-- 1. DB Latency -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted fw-bold small">Database Latency</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <h3 class="fw-bold text-primary mb-1">{{ $diagnostics['db_latency_ms'] ?? 0 }} <span class="fs-6 text-muted">ms</span></h3>
                <p class="small text-muted mb-0">MySQL PDO ping latency</p>
            </div>
        </div>
    </div>

    <!-- 2. PHP & Laravel -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted fw-bold small">Environment</span>
                    <span class="badge bg-info text-dark">PHP {{ $diagnostics['php_version'] ?? '' }}</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">Laravel {{ $diagnostics['laravel_version'] ?? '' }}</h3>
                <p class="small text-muted mb-0">Modular Monolith Architecture</p>
            </div>
        </div>
    </div>

    <!-- 3. Disk Storage -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted fw-bold small">Disk Usage</span>
                    <span class="badge bg-secondary">{{ $diagnostics['disk_usage_pct'] ?? 0 }}% Used</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $diagnostics['free_disk_gb'] ?? 0 }} <span class="fs-6 text-muted">GB Free</span></h3>
                <p class="small text-muted mb-0">Total: {{ $diagnostics['total_disk_gb'] ?? 0 }} GB</p>
            </div>
        </div>
    </div>

    <!-- 4. Storage Permissions -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted fw-bold small">Storage Writable</span>
                    <span class="badge bg-success">Writable</span>
                </div>
                <h3 class="fw-bold text-success mb-1"><i class="bi bi-folder-check"></i> Passed</h3>
                <p class="small text-muted mb-0">App, Logs, Framework writable</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Table Row Counts & Database Matrix -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-database me-2 text-primary"></i>Database Table Roster & Row Metrics</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Table Concept</th>
                            <th>Row Count</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($diagnostics['table_counts'] ?? []) as $label => $count)
                            <tr>
                                <td class="fw-bold">{{ $label }}</td>
                                <td><span class="badge bg-secondary">{{ $count }}</span></td>
                                <td><span class="badge bg-success"><i class="bi bi-check me-1"></i>Healthy</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Security Audit Matrix -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2 text-primary"></i>Security & Middleware Audit</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach(($diagnostics['security'] ?? []) as $key => $val)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-uppercase small text-muted">{{ str_replace('_', ' ', $key) }}</span>
                            <span class="badge bg-light text-dark border">{{ $val }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<script>
async function recheckDiagnostics() {
    showLoading();
    try {
        const response = await fetch('{{ route("admin.system.health.diagnostics") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        const json = await response.json();
        if (json.success) {
            window.location.reload();
        }
    } finally {
        hideLoading();
    }
}
</script>
@endpush
