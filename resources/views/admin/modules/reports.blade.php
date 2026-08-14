@extends('layouts.app')

@section('title', 'Reports & Audit Logs')
@section('page-title', 'Managerial Reports & Audit Logs')
@section('breadcrumb', 'Reports › Audit Logs & Executive Analytics')

@section('content')

<!-- Top Header Bar -->
<div class="page-header flex-wrap gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-bar-chart-line text-primary me-2"></i>Reports & Audit Logs</h1>
        <p class="text-secondary small mb-0">Executive financial analytics, channel distribution & system activity trail for <strong>{{ $property?->name ?: 'All Properties' }}</strong></p>
    </div>

    <!-- Date Range & Export Actions (For Analytics Tab) -->
    @if(($activeTab ?? 'analytics') === 'analytics')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="analytics">
            <input type="date" name="start_date" class="form-control form-control-sm rounded-pill px-3" value="{{ $startDate }}" required>
            <span class="text-muted small">to</span>
            <input type="date" name="end_date" class="form-control form-control-sm rounded-pill px-3" value="{{ $endDate }}" required>
            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </form>

        <div class="btn-group">
            <a href="{{ route('admin.reports.export.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-success rounded-start-pill px-3">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <a href="{{ route('admin.reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-danger rounded-end-pill px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF Report
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4 bg-white p-1.5 rounded-pill shadow-xs border d-inline-flex">
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 fw-bold {{ ($activeTab ?? 'analytics') === 'analytics' ? 'active bg-primary text-dark' : 'text-secondary' }}" 
           href="{{ route('admin.reports.index', ['tab' => 'analytics', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
            <i class="bi bi-graph-up me-2"></i>Executive Analytics
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 fw-bold {{ ($activeTab ?? 'analytics') === 'audit' ? 'active bg-dark text-white' : 'text-secondary' }}" 
           href="{{ route('admin.reports.index', ['tab' => 'audit']) }}">
            <i class="bi bi-shield-check me-2"></i>Audit Logs & Alerts
        </a>
    </li>
</ul>

@if(($activeTab ?? 'analytics') === 'analytics')
    <!-- ==================== TAB 1: EXECUTIVE ANALYTICS ==================== -->

    <!-- Date Preset Buttons -->
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('admin.reports.index', ['tab' => 'analytics', 'start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()]) }}" 
           class="btn btn-sm btn-light border rounded-pill px-3">
            Last 7 Days
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'analytics', 'start_date' => now()->subDays(29)->toDateString(), 'end_date' => now()->toDateString()]) }}" 
           class="btn btn-sm btn-light border rounded-pill px-3">
            Last 30 Days
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'analytics', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" 
           class="btn btn-sm btn-light border rounded-pill px-3">
            This Month
        </a>
    </div>

    <!-- Key Performance Indicators Row -->
    <div class="row g-3 mb-4">
        <!-- Gross Revenue -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
                <div>
                    <div class="stat-number">
                        {{ number_format(($metrics['total_gross_revenue_minor'] ?? 0) / 100, 2) }}
                    </div>
                    <div class="stat-label">Gross Revenue ({{ $metrics['currency'] ?? 'USD' }})</div>
                </div>
            </div>
        </div>

        <!-- Occupancy % -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-pie-chart-fill"></i></div>
                <div>
                    <div class="stat-number">{{ $metrics['occupancy_pct'] ?? 0 }}%</div>
                    <div class="stat-label">Occupancy Rate</div>
                </div>
            </div>
        </div>

        <!-- ADR -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="stat-number">
                        {{ number_format(($metrics['adr_minor'] ?? 0) / 100, 2) }}
                    </div>
                    <div class="stat-label">Average Daily Rate (ADR)</div>
                </div>
            </div>
        </div>

        <!-- RevPAR -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon teal"><i class="bi bi-building"></i></div>
                <div>
                    <div class="stat-number">
                        {{ number_format(($metrics['revpar_minor'] ?? 0) / 100, 2) }}
                    </div>
                    <div class="stat-label">RevPAR</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Time Series Line Chart: Occupancy & RevPAR -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up me-2 text-primary"></i>Daily Occupancy % & RevPAR Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="occupancyTrendChart" height="260"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Source Distribution Doughnut Chart -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-diagram-3 me-2 text-primary"></i>Booking Channels</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="channelDoughnutChart" height="240"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Section: Channel Details & Operational Breakdown -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-table me-2 text-primary"></i>Channel Revenue & Booking Volume</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Channel Name</th>
                                <th>Reservations</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $chn)
                                <tr>
                                    <td class="fw-bold">{{ $chn['name'] }}</td>
                                    <td><span class="badge bg-secondary rounded-pill px-2.5">{{ $chn['count'] }}</span></td>
                                    <td class="fw-semibold text-success">
                                        {{ number_format($chn['revenue_minor'] / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No booking source data recorded for period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Capacity & Inventory Summary</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush rounded-bottom-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Total Room Inventory</span>
                            <strong class="text-primary">{{ $metrics['total_rooms'] ?? 0 }} Rooms</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Available Room Nights</span>
                            <strong>{{ $metrics['total_available_room_nights'] ?? 0 }} Nights</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Occupied Room Nights</span>
                            <strong class="text-success">{{ $metrics['occupied_room_nights'] ?? 0 }} Nights</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Net Room Charges</span>
                            <strong>{{ number_format(($metrics['total_room_revenue_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Taxes & Fees Collected</span>
                            <strong>{{ number_format((($metrics['total_tax_minor'] ?? 0) + ($metrics['total_fee_minor'] ?? 0)) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@else
    <!-- ==================== TAB 2: AUDIT LOGS & ALERTS ==================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock me-2 text-primary"></i>System Audit Trail & Operational Events</h6>
                <p class="text-muted small mb-0">Immutable audit log of all reservations, check-ins, rate changes, logins, and administrative actions.</p>
            </div>
            <span class="badge bg-secondary-subtle text-dark border rounded-pill px-3 py-1">
                {{ $auditLogs->total() }} Total Events
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light text-uppercase text-secondary small">
                    <tr>
                        <th class="ps-4">Event Action</th>
                        <th>Actor</th>
                        <th>Target Resource</th>
                        <th>Source IP</th>
                        <th>Timestamp</th>
                        <th class="text-end pe-4">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        @php
                            $badgeClass = match(true) {
                                str_contains($log->action, 'created')   => 'bg-success-subtle text-success border border-success-subtle',
                                str_contains($log->action, 'updated')   => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                str_contains($log->action, 'status')    => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                str_contains($log->action, 'login')     => 'bg-primary-subtle text-primary border border-primary-subtle',
                                str_contains($log->action, 'logout')    => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                default                                 => 'bg-light text-dark border'
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold {{ $badgeClass }}">
                                    {{ ucwords(str_replace(['.', '_'], ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $log->actor?->name ?: 'System Automations' }}</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">{{ $log->actor?->email ?: $log->actor_type }}</div>
                            </td>
                            <td>
                                @if($log->target_type)
                                    <span class="fw-semibold text-dark">{{ $log->target_type }}</span>
                                    @if($log->target_id)
                                        <code class="small ms-1">#{{ Str::limit($log->target_id, 10) }}</code>
                                    @endif
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <code class="small bg-light px-2 py-1 rounded">{{ $log->source_ip ?: '127.0.0.1' }}</code>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->created_at ? $log->created_at->format('M d, Y H:i:s') : 'N/A' }}</div>
                                <div class="text-muted small" style="font-size: 0.7rem;">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</div>
                            </td>
                            <td class="text-end pe-4">
                                @if(!empty($log->metadata) || !empty($log->before) || !empty($log->after))
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#logDetails-{{ $log->id }}">
                                        <i class="bi bi-code-slash me-1"></i> View JSON
                                    </button>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($log->metadata) || !empty($log->before) || !empty($log->after))
                            <tr class="collapse bg-light" id="logDetails-{{ $log->id }}">
                                <td colspan="6" class="p-3 ps-4 pe-4">
                                    <div class="bg-dark text-white p-3 rounded-3 font-monospace small" style="font-size: 0.78rem;">
                                        @if(!empty($log->metadata))
                                            <div class="text-warning fw-bold mb-1">// Metadata</div>
                                            <pre class="text-white mb-2" style="white-space: pre-wrap;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                        @if(!empty($log->before))
                                            <div class="text-danger fw-bold mb-1">// Before State</div>
                                            <pre class="text-white mb-2" style="white-space: pre-wrap;">{{ json_encode($log->before, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                        @if(!empty($log->after))
                                            <div class="text-success fw-bold mb-1">// After State</div>
                                            <pre class="text-white mb-0" style="white-space: pre-wrap;">{{ json_encode($log->after, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                <i class="bi bi-shield-slash fs-1 d-block mb-2 text-secondary"></i>
                                No audit log records recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} of {{ $auditLogs->total() }} entries
                </div>
                <div>
                    {{ $auditLogs->links() }}
                </div>
            </div>
        @endif
    </div>
@endif

@endsection

@push('styles')
@if(($activeTab ?? 'analytics') === 'analytics')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Line Chart: Occupancy % & RevPAR Trends
    const timeSeriesData = @json($timeSeries);
    if (timeSeriesData && timeSeriesData.labels) {
        const ctxLine = document.getElementById('occupancyTrendChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: timeSeriesData.labels,
                datasets: [
                    {
                        label: 'Occupancy %',
                        data: timeSeriesData.occupancy,
                        borderColor: '#3b6ff0',
                        backgroundColor: 'rgba(59,111,240,0.1)',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'yOcc',
                    },
                    {
                        label: 'RevPAR ({{ $metrics['currency'] ?? 'USD' }})',
                        data: timeSeriesData.revpar,
                        borderColor: '#10b981',
                        borderDash: [5, 5],
                        tension: 0.3,
                        yAxisID: 'yRev',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    yOcc: {
                        type: 'linear', display: true, position: 'left',
                        title: { display: true, text: 'Occupancy %' },
                        min: 0, max: 100
                    },
                    yRev: {
                        type: 'linear', display: true, position: 'right',
                        title: { display: true, text: 'RevPAR' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // 2. Doughnut Chart: Booking Channel Distribution
    const channelData = @json($channels);
    if (channelData && channelData.length > 0) {
        const ctxDoughnut = document.getElementById('channelDoughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: channelData.map(c => c.name),
                datasets: [{
                    data: channelData.map(c => c.count),
                    backgroundColor: ['#3b6ff0', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
@endif
@endpush
