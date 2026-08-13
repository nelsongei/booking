@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page-title', 'Managerial Reports & Group Analytics')
@section('breadcrumb', 'Reports › Executive Analytics')

@section('content')

<!-- Header & Filter Bar -->
<div class="page-header flex-wrap gap-3">
    <div>
        <h1>Executive Analytics Dashboard</h1>
        <p>Real-time RevPAR, ADR, Occupancy performance & channel distribution for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>

    <!-- Date Range & Export Actions -->
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="d-flex align-items-center gap-2">
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" required>
            <span class="text-muted">to</span>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" required>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </form>

        <div class="btn-group">
            <a href="{{ route('admin.reports.export.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
            </a>
            <a href="{{ route('admin.reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF Report
            </a>
        </div>
    </div>
</div>

<!-- Date Preset Buttons -->
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.reports.index', ['start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light border">
        Last 7 Days
    </a>
    <a href="{{ route('admin.reports.index', ['start_date' => now()->subDays(29)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light border">
        Last 30 Days
    </a>
    <a href="{{ route('admin.reports.index', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light border">
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
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>Daily Occupancy % & RevPAR Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="occupancyTrendChart" height="260"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Source Distribution Doughnut Chart -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Booking Channels</h6>
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
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-primary"></i>Channel Revenue & Booking Volume</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
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
                                <td><span class="badge bg-secondary">{{ $chn['count'] }}</span></td>
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
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Capacity & Inventory Summary</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Total Room Inventory</span>
                        <strong class="text-primary">{{ $metrics['total_rooms'] ?? 0 }} Rooms</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Available Room Nights</span>
                        <strong>{{ $metrics['total_available_room_nights'] ?? 0 }} Nights</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Occupied Room Nights</span>
                        <strong class="text-success">{{ $metrics['occupied_room_nights'] ?? 0 }} Nights</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Net Room Charges</span>
                        <strong>{{ number_format(($metrics['total_room_revenue_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Taxes & Fees Collected</span>
                        <strong>{{ number_format((($metrics['total_tax_minor'] ?? 0) + ($metrics['total_fee_minor'] ?? 0)) / 100, 2) }} {{ $metrics['currency'] ?? 'USD' }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
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
@endpush
