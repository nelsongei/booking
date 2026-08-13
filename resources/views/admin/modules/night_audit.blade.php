@extends('layouts.app')

@section('title', 'Night Audit Wizard')
@section('page-title', 'End-of-Day Night Audit')
@section('breadcrumb', 'Operations › Night Audit')

@section('content')

<div class="page-header">
    <div>
        <h1>Night Audit Orchestrator</h1>
        <p>Step-based, idempotent end-of-day business date roll & automated room charge posting for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Current Business Date Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <span class="text-uppercase text-muted fw-bold small">Current Business Date</span>
                    <h2 class="display-6 fw-bold text-primary mt-2 mb-1">{{ $businessDate }}</h2>
                    <p class="text-muted small mb-0">System Time: {{ now()->format('Y-m-d H:i:s T') }}</p>
                </div>
                <div class="mt-3">
                    @if(($validation['can_proceed'] ?? false))
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Ready For Audit</span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-exclamation-triangle me-1"></i> Action Required</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-Audit Check Status -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Pre-Audit Verification Checklist</h6>
                <span class="small text-muted">Must resolve prior to date roll</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded text-center {{ ($validation['pending_arrivals'] ?? 0) > 0 ? 'bg-light-warning border-warning' : 'bg-light' }}">
                            <div class="fs-4 fw-bold {{ ($validation['pending_arrivals'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $validation['pending_arrivals'] ?? 0 }}
                            </div>
                            <div class="small text-muted fw-medium mt-1">Pending Arrivals</div>
                            @if(($validation['pending_arrivals'] ?? 0) > 0)
                                <div class="badge bg-warning text-dark mt-2">Needs Check-In / No-Show</div>
                            @else
                                <div class="badge bg-success mt-2">Cleared <i class="bi bi-check"></i></div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded text-center {{ ($validation['pending_departures'] ?? 0) > 0 ? 'bg-light-warning border-warning' : 'bg-light' }}">
                            <div class="fs-4 fw-bold {{ ($validation['pending_departures'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $validation['pending_departures'] ?? 0 }}
                            </div>
                            <div class="small text-muted fw-medium mt-1">Pending Departures</div>
                            @if(($validation['pending_departures'] ?? 0) > 0)
                                <div class="badge bg-warning text-dark mt-2">Needs Check-Out</div>
                            @else
                                <div class="badge bg-success mt-2">Cleared <i class="bi bi-check"></i></div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded text-center {{ ($validation['open_cashier_shifts'] ?? 0) > 0 ? 'bg-light-warning border-warning' : 'bg-light' }}">
                            <div class="fs-4 fw-bold {{ ($validation['open_cashier_shifts'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $validation['open_cashier_shifts'] ?? 0 }}
                            </div>
                            <div class="small text-muted fw-medium mt-1">Open Cashier Shifts</div>
                            @if(($validation['open_cashier_shifts'] ?? 0) > 0)
                                <div class="badge bg-warning text-dark mt-2">Close Shifts First</div>
                            @else
                                <div class="badge bg-success mt-2">Cleared <i class="bi bi-check"></i></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Stepper Panel -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-moon-stars me-2 text-primary"></i>Audit Runbook Execution Stepper</h6>
        <div>
            @if($activeAudit && $activeAudit->status === 'failed')
                <form method="POST" action="{{ route('admin.night-audit.reset', $activeAudit) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger me-2"><i class="bi bi-arrow-counterclockwise"></i> Reset Failed Steps</button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.night-audit.run') }}" class="d-inline" id="runAuditForm">
                @csrf
                <button type="submit" class="btn btn-primary px-4" id="btnRunAudit" {{ ($activeAudit?->status === 'completed') ? 'disabled' : '' }}>
                    <i class="bi bi-play-circle-fill me-2"></i> Run Night Audit
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        @php
            $steps = $activeAudit?->steps ?: [
                'validate'          => ['status' => 'pending', 'message' => 'Validating operations'],
                'post_room_charges' => ['status' => 'pending', 'message' => 'Posting daily room rates'],
                'update_no_shows'   => ['status' => 'pending', 'message' => 'Processing no-shows'],
                'roll_date'         => ['status' => 'pending', 'message' => 'Advancing business date'],
                'generate_report'   => ['status' => 'pending', 'message' => 'Generating end-of-day summary'],
            ];
        @endphp

        <div class="list-group list-group-flush">
            <!-- Step 1 -->
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon {{ ($steps['validate']['status'] ?? 'pending') === 'done' ? 'green' : 'blue' }}">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Step 1: Operational Pre-Check</h6>
                        <span class="small text-muted">{{ $steps['validate']['message'] ?? 'Verify arrivals, departures, and cashier shifts' }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge-status {{ ($steps['validate']['status'] ?? 'pending') === 'done' ? 'active' : 'pending' }}">
                        {{ strtoupper($steps['validate']['status'] ?? 'PENDING') }}
                    </span>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon {{ ($steps['post_room_charges']['status'] ?? 'pending') === 'done' ? 'green' : 'blue' }}">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Step 2: Automated Room Charge Posting</h6>
                        <span class="small text-muted">{{ $steps['post_room_charges']['message'] ?? 'Post daily room charges to all in-house guest folios' }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge-status {{ ($steps['post_room_charges']['status'] ?? 'pending') === 'done' ? 'active' : 'pending' }}">
                        {{ strtoupper($steps['post_room_charges']['status'] ?? 'PENDING') }}
                    </span>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon {{ ($steps['update_no_shows']['status'] ?? 'pending') === 'done' ? 'green' : 'blue' }}">
                        <i class="bi bi-person-x"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Step 3: Unhandled Arrivals & No-Show Update</h6>
                        <span class="small text-muted">{{ $steps['update_no_shows']['message'] ?? 'Auto-transition unhandled arrivals to no-show' }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge-status {{ ($steps['update_no_shows']['status'] ?? 'pending') === 'done' ? 'active' : 'pending' }}">
                        {{ strtoupper($steps['update_no_shows']['status'] ?? 'PENDING') }}
                    </span>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon {{ ($steps['roll_date']['status'] ?? 'pending') === 'done' ? 'green' : 'blue' }}">
                        <i class="bi bi-calendar2-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Step 4: Business Date Advance</h6>
                        <span class="small text-muted">{{ $steps['roll_date']['message'] ?? 'Roll business date to next date in sequence' }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge-status {{ ($steps['roll_date']['status'] ?? 'pending') === 'done' ? 'active' : 'pending' }}">
                        {{ strtoupper($steps['roll_date']['status'] ?? 'PENDING') }}
                    </span>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon {{ ($steps['generate_report']['status'] ?? 'pending') === 'done' ? 'green' : 'blue' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Step 5: End-of-Day Managerial Report</h6>
                        <span class="small text-muted">{{ $steps['generate_report']['message'] ?? 'Compile daily revenue, occupancy & stay counts' }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge-status {{ ($steps['generate_report']['status'] ?? 'pending') === 'done' ? 'active' : 'pending' }}">
                        {{ strtoupper($steps['generate_report']['status'] ?? 'PENDING') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Audits Roster -->
<div class="card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Audit History Log</h6>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Business Date</th>
                    <th>Status</th>
                    <th>Started By</th>
                    <th>Completed At</th>
                    <th>Occupancy %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAudits as $adt)
                    <tr>
                        <td class="fw-bold"><code>{{ $adt->business_date->format('Y-m-d') }}</code></td>
                        <td><span class="badge-status {{ $adt->status }}">{{ ucfirst($adt->status) }}</span></td>
                        <td>{{ $adt->startedBy?->name ?: 'System' }}</td>
                        <td>{{ $adt->completed_at ? $adt->completed_at->format('Y-m-d H:i') : '—' }}</td>
                        <td>
                            @if(isset($adt->report_data['occupancy_pct']))
                                <span class="badge bg-info text-dark fw-bold">{{ $adt->report_data['occupancy_pct'] }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No prior night audit logs recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
