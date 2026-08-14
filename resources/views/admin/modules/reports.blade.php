@extends('layouts.app')

@section('title', 'Reports & Executive Analytics')
@section('page-title', 'Executive PMS Analytics & Managerial Reports')
@section('breadcrumb', 'Reports › Executive Analytics')

@section('content')

<!-- Style Adjustments -->
<style>
    .report-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    }
    .chart-container-box {
        position: relative;
        width: 100%;
        min-height: 280px;
    }
    .kpi-icon-badge {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
</style>

<!-- Top Header Bar -->
<div class="page-header flex-wrap gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-bar-chart-line text-primary me-2"></i>Executive Reports & Analytics</h1>
        <p class="text-secondary small mb-0">Financial performance, RevPAR, ADR, occupancy trends & channel distribution for <strong>{{ $property?->name ?: 'All Properties' }}</strong></p>
    </div>

    <!-- Date Range & Export Actions -->
    @if(($activeTab ?? 'analytics') === 'analytics')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="analytics">
            <input type="date" name="start_date" class="form-control form-control-sm rounded-pill px-3 shadow-xs" value="{{ $startDate }}" required>
            <span class="text-muted small">to</span>
            <input type="date" name="end_date" class="form-control form-control-sm rounded-pill px-3 shadow-xs" value="{{ $endDate }}" required>
            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </form>

        <div class="btn-group">
            <a href="{{ route('admin.reports.export.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-success rounded-start-pill px-3">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV Export
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
        <a class="nav-link rounded-pill px-4 fw-bold {{ ($activeTab ?? 'analytics') === 'analytics' ? 'active bg-primary text-dark shadow-xs' : 'text-secondary' }}" 
           href="{{ route('admin.reports.index', ['tab' => 'analytics', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
            <i class="bi bi-graph-up me-2"></i>Executive Analytics
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 fw-bold {{ ($activeTab ?? 'analytics') === 'audit' ? 'active bg-dark text-white shadow-xs' : 'text-secondary' }}" 
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

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <!-- Gross Revenue -->
        <div class="col-6 col-md-3">
            <div class="card report-card bg-white p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Gross Revenue</div>
                        <div class="fs-4 fw-bold text-dark mt-1">
                            ${{ number_format(($metrics['total_gross_revenue_minor'] ?? 0) / 100, 2) }}
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-success-subtle text-success">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="text-success fw-bold"><i class="bi bi-arrow-up-right me-1"></i>{{ $metrics['reservations_count'] ?? 0 }} Bookings</span> &bull; {{ $metrics['currency'] ?? 'USD' }}
                </div>
            </div>
        </div>

        <!-- Occupancy % -->
        <div class="col-6 col-md-3">
            <div class="card report-card bg-white p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Occupancy Rate</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $metrics['occupancy_pct'] ?? 0 }}%</div>
                    </div>
                    <div class="kpi-icon-badge bg-primary-subtle text-primary">
                        <i class="bi bi-pie-chart-fill fs-4"></i>
                    </div>
                </div>
                <div class="progress mt-2.5" style="height: 6px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $metrics['occupancy_pct'] ?? 0) }}%"></div>
                </div>
            </div>
        </div>

        <!-- ADR -->
        <div class="col-6 col-md-3">
            <div class="card report-card bg-white p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Average Daily Rate</div>
                        <div class="fs-4 fw-bold text-dark mt-1">
                            ${{ number_format(($metrics['adr_minor'] ?? 0) / 100, 2) }}
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="fw-semibold">ADR per occupied room night</span>
                </div>
            </div>
        </div>

        <!-- RevPAR -->
        <div class="col-6 col-md-3">
            <div class="card report-card bg-white p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">RevPAR Yield</div>
                        <div class="fs-4 fw-bold text-dark mt-1">
                            ${{ number_format(($metrics['revpar_minor'] ?? 0) / 100, 2) }}
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-info-subtle text-info-emphasis">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="fw-semibold">Revenue per available inventory room</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Charts Grid (4 Interactive Graphs) -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Revenue ($) & Occupancy (%) Dual-Axis Line Area Chart -->
        <div class="col-12 col-lg-8">
            <div class="card report-card bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Daily Gross Revenue ($) & Occupancy Trend</h6>
                    <span class="badge bg-light text-dark border fw-normal">{{ $startDate }} &rarr; {{ $endDate }}</span>
                </div>
                <div class="card-body">
                    <div class="chart-container-box">
                        <canvas id="revenueTrendChart" height="280"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Booking Channel Distribution Doughnut Chart -->
        <div class="col-12 col-lg-4">
            <div class="card report-card bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart me-2 text-primary"></i>Booking Sources & Channels</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-container-box d-flex align-items-center justify-content-center">
                        <canvas id="channelDoughnutChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 3: ADR vs RevPAR Benchmark Comparison Bar Chart -->
        <div class="col-12 col-lg-6">
            <div class="card report-card bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-steps me-2 text-warning"></i>ADR vs RevPAR Daily Benchmark ($)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container-box">
                        <canvas id="adrRevparChart" height="240"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 4: Room Category Revenue Distribution -->
        <div class="col-12 col-lg-6">
            <div class="card report-card bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-door-open me-2 text-info"></i>Room Type Performance & Bookings Count</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container-box">
                        <canvas id="roomTypePerformanceChart" height="240"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="row g-4">
        <!-- Channel Performance Breakdown -->
        <div class="col-12 col-lg-6">
            <div class="card report-card bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-diagram-3 me-2 text-primary"></i>Channel Revenue & Booking Volume</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Channel Name</th>
                                <th>Bookings</th>
                                <th class="text-end pe-3">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $chn)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">{{ $chn['name'] }}</td>
                                    <td><span class="badge bg-secondary rounded-pill px-2.5">{{ $chn['count'] }} Bookings</span></td>
                                    <td class="text-end pe-3 fw-bold text-success">
                                        ${{ number_format($chn['revenue_minor'] / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No booking source data recorded for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Capacity & Financial Summary -->
        <div class="col-12 col-lg-6">
            <div class="card report-card bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator me-2 text-primary"></i>Capacity & Financial Audit Summary</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush rounded-bottom-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Total Room Inventory</span>
                            <strong class="text-primary">{{ $metrics['total_rooms'] ?? 0 }} Physical Rooms</strong>
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
                            <strong>${{ number_format(($metrics['total_room_revenue_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <span>Taxes & Fees Collected</span>
                            <strong>${{ number_format((($metrics['total_tax_minor'] ?? 0) + ($metrics['total_fee_minor'] ?? 0)) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 bg-light">
                            <span class="fw-bold text-dark">Gross Total Revenue</span>
                            <strong class="text-success fs-5">${{ number_format(($metrics['total_gross_revenue_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@else
    <!-- ==================== TAB 2: AUDIT LOGS & ALERTS ==================== -->
    <div class="card report-card bg-white overflow-hidden">
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
<!-- Chart.js 4.4 CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timeSeriesData = @json($timeSeries);
    const channelData    = @json($channels);
    const roomTypeData   = @json($metrics['room_type_performance'] ?? []);

    // 1. Dual-Axis Revenue ($) & Occupancy (%) Area Chart
    if (timeSeriesData && timeSeriesData.labels && timeSeriesData.labels.length > 0) {
        const ctxRev = document.getElementById('revenueTrendChart').getContext('2d');
        
        // Gradient fill for Revenue
        const revGradient = ctxRev.createLinearGradient(0, 0, 0, 300);
        revGradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
        revGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: timeSeriesData.labels,
                datasets: [
                    {
                        label: 'Gross Revenue ($)',
                        data: timeSeriesData.revenue,
                        borderColor: '#10b981',
                        backgroundColor: revGradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'yRevenue',
                    },
                    {
                        label: 'Occupancy Rate (%)',
                        data: timeSeriesData.occupancy,
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        tension: 0.4,
                        fill: false,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 3,
                        yAxisID: 'yOccupancy',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        padding: 12,
                        backgroundColor: '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.03)' } },
                    yRevenue: {
                        type: 'linear', display: true, position: 'left',
                        title: { display: true, text: 'Revenue ($)', color: '#10b981', font: { weight: 'bold' } },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { callback: v => '$' + v }
                    },
                    yOccupancy: {
                        type: 'linear', display: true, position: 'right',
                        title: { display: true, text: 'Occupancy %', color: '#3b82f6', font: { weight: 'bold' } },
                        min: 0, max: 100,
                        grid: { drawOnChartArea: false },
                        ticks: { callback: v => v + '%' }
                    }
                }
            }
        });
    }

    // 2. Booking Source Doughnut Chart
    if (channelData && channelData.length > 0) {
        const ctxDoughnut = document.getElementById('channelDoughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: channelData.map(c => c.name),
                datasets: [{
                    data: channelData.map(c => c.revenue_minor / 100),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.label + ': $' + ctx.raw.toFixed(2);
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 3. ADR vs RevPAR Benchmark Comparison Bar Chart
    if (timeSeriesData && timeSeriesData.labels && timeSeriesData.labels.length > 0) {
        const ctxBar = document.getElementById('adrRevparChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: timeSeriesData.labels,
                datasets: [
                    {
                        label: 'ADR ($)',
                        data: timeSeriesData.adr,
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderRadius: 6,
                    },
                    {
                        label: 'RevPAR ($)',
                        data: timeSeriesData.revpar,
                        backgroundColor: 'rgba(20, 184, 166, 0.85)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => '$' + v }
                    }
                }
            }
        });
    }

    // 4. Room Type Performance Horizontal Bar Chart
    if (roomTypeData && roomTypeData.length > 0) {
        const ctxRoomType = document.getElementById('roomTypePerformanceChart').getContext('2d');
        new Chart(ctxRoomType, {
            type: 'bar',
            data: {
                labels: roomTypeData.map(r => r.room_type_name),
                datasets: [{
                    label: 'Total Revenue ($)',
                    data: roomTypeData.map(r => r.total_revenue_minor / 100),
                    backgroundColor: ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                    borderRadius: 8,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => '$' + v }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endif
@endpush
