@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Operational Overview for ' . (isset($property) && $property ? $property->name : 'All Properties'))

@section('content')

@if(!isset($property) || !$property)
<div class="alert alert-warning mb-4 rounded-4 shadow-sm border-0" style="background: #fef3c7; color: #92400e;">
    <i class="bi bi-info-circle me-2"></i>
    No property selected. <a href="{{ route('admin.properties.index') }}" class="alert-link">Configure a property</a> to see operational data.
</div>
@endif

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <!-- Arrivals Today -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card-luxury">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper stat-icon-emerald">
                    <i class="fa-solid fa-calendar-check text-white"></i>
                </div>
                <span class="stat-badge-pill stat-badge-emerald">
                    <i class="fa-solid fa-circle font-xs me-1"></i> Live
                </span>
            </div>
            <div>
                <div class="stat-card-title">Arrivals Today</div>
                <div class="stat-card-value">{{ $stats['arrivals_today'] ?? 0 }}</div>
            </div>
            <div class="stat-mini-bar">
                <div class="stat-mini-bar-fill" style="width: {{ min(100, (($stats['arrivals_today'] ?? 0) / max(1, $stats['rooms_total'] ?? 10)) * 100) }}%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
            </div>
        </div>
    </div>

    <!-- Departures Today -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card-luxury">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper stat-icon-sapphire">
                    <i class="fa-solid fa-plane-departure text-white"></i>
                </div>
                <span class="stat-badge-pill stat-badge-sapphire">
                    <i class="fa-solid fa-circle font-xs me-1"></i> Live
                </span>
            </div>
            <div>
                <div class="stat-card-title">Departures Today</div>
                <div class="stat-card-value">{{ $stats['departures_today'] ?? 0 }}</div>
            </div>
            <div class="stat-mini-bar">
                <div class="stat-mini-bar-fill" style="width: {{ min(100, (($stats['departures_today'] ?? 0) / max(1, $stats['rooms_total'] ?? 10)) * 100) }}%; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
            </div>
        </div>
    </div>

    <!-- In-House Guests -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card-luxury">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper stat-icon-indigo">
                    <i class="fa-solid fa-bed text-white"></i>
                </div>
                <span class="stat-badge-pill stat-badge-indigo">
                    <i class="fa-solid fa-circle font-xs me-1"></i> Active
                </span>
            </div>
            <div>
                <div class="stat-card-title">In-House Guests</div>
                <div class="stat-card-value">{{ $stats['in_house'] ?? 0 }}</div>
            </div>
            <div class="stat-mini-bar">
                <div class="stat-mini-bar-fill" style="width: {{ min(100, (($stats['in_house'] ?? 0) / max(1, $stats['rooms_total'] ?? 10)) * 100) }}%; background: linear-gradient(90deg, #6366f1, #818cf8);"></div>
            </div>
        </div>
    </div>

    <!-- Active Bookings -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card-luxury">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper stat-icon-amber">
                    <i class="fa-solid fa-bookmark text-white"></i>
                </div>
                <span class="stat-badge-pill stat-badge-amber">
                    <i class="fa-solid fa-circle font-xs me-1"></i> Confirmed
                </span>
            </div>
            <div>
                <div class="stat-card-title">Active Bookings</div>
                <div class="stat-card-value">{{ $stats['reservations_total'] ?? 0 }}</div>
            </div>
            <div class="stat-mini-bar">
                <div class="stat-mini-bar-fill" style="width: {{ min(100, (($stats['reservations_total'] ?? 0) / max(1, 15)) * 100) }}%; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
            </div>
        </div>
    </div>

    <!-- Occupancy Rate -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card-luxury">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper stat-icon-teal">
                    <i class="fa-solid fa-chart-pie text-white"></i>
                </div>
                <span class="stat-badge-pill stat-badge-teal">
                    <i class="fa-solid fa-circle font-xs me-1"></i> Realtime
                </span>
            </div>
            <div>
                <div class="stat-card-title">Occupancy Rate</div>
                <div class="stat-card-value">{{ $stats['occupancy_today'] ?? 0 }}%</div>
            </div>
            <div class="stat-mini-bar">
                <div class="stat-mini-bar-fill" style="width: {{ min(100, $stats['occupancy_today'] ?? 0) }}%; background: linear-gradient(90deg, #14b8a6, #2dd4bf);"></div>
            </div>
        </div>
    </div>

    <!-- Revenue (MTD) -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card-luxury">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper stat-icon-obsidian">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <span class="stat-badge-pill stat-badge-gold">
                    <i class="fa-solid fa-crown font-xs me-1"></i> MTD
                </span>
            </div>
            <div>
                @php
                    $revenue = ($stats['revenue_mtd'] ?? 0) / 100;
                    $currency = isset($property) && $property ? $property->currency : 'USD';
                @endphp
                <div class="stat-card-title">Revenue (MTD)</div>
                <div class="stat-card-value revenue-val" title="{{ $currency }} {{ number_format($revenue, 2) }}">
                    {{ $currency }} {{ number_format($revenue, 0) }}
                </div>
            </div>
            <div class="stat-mini-bar">
                <div class="stat-mini-bar-fill" style="width: 85%; background: linear-gradient(90deg, #c5a059, #dfb76c, #9a7b38);"></div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary KPI Cards Row: RevPAR, ADR, Active Holds, Unpaid Balance -->
<div class="row g-4 mb-4">
    <!-- RevPAR -->
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 shadow-sm border-0 rounded-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">RevPAR (Rev / Room)</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold">KPI</span>
            </div>
            <div class="fs-3 fw-extrabold text-dark">{{ $currency }} {{ number_format($stats['revpar'] ?? 0, 2) }}</div>
            <small class="text-muted"><i class="fa-solid fa-hotel text-warning me-1"></i> Average revenue per available room night</small>
        </div>
    </div>

    <!-- ADR -->
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 shadow-sm border-0 rounded-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">ADR (Avg Daily Rate)</span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold">Yield</span>
            </div>
            <div class="fs-3 fw-extrabold text-dark">{{ $currency }} {{ number_format($stats['adr'] ?? 0, 2) }}</div>
            <small class="text-muted"><i class="fa-solid fa-chart-line text-success me-1"></i> Average price realized per active booking</small>
        </div>
    </div>

    <!-- Active 15-Min Holds -->
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 shadow-sm border-0 rounded-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Active Room Holds</span>
                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-bold">15-Min Lock</span>
            </div>
            <div class="fs-3 fw-extrabold text-warning">{{ $stats['active_holds'] ?? 0 }} <span class="fs-6 text-muted font-sans">Active Locks</span></div>
            <small class="text-muted"><i class="fa-solid fa-bolt text-warning me-1"></i> Live inventory holds in booking engine</small>
        </div>
    </div>

    <!-- Unpaid Balance Due -->
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 shadow-sm border-0 rounded-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Unpaid Folio Balance</span>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold">Receivables</span>
            </div>
            <div class="fs-3 fw-extrabold text-dark">{{ $currency }} {{ number_format(($stats['unpaid_balance'] ?? 0) / 100, 2) }}</div>
            <small class="text-muted"><i class="fa-solid fa-clock-history text-danger me-1"></i> Balance due at front desk check-in</small>
        </div>
    </div>
</div>

<!-- Main Operations & Revenue Chart Grid -->
<div class="row g-4 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="fw-bold mb-0">Revenue Analytics</h6>
                    <small class="text-muted">Dynamic performance overview for {{ isset($property) && $property ? $property->name : 'Property' }}</small>
                </div>
                <span class="badge bg-dark rounded-pill px-3 py-2">Past 6 Months</span>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Room Availability Widget -->
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-door-open me-2 text-dark"></i>Room Availability</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fs-6 fw-extrabold text-muted">Occupied</div>
                        <div class="fs-2 fw-bold text-dark">{{ $stats['rooms_occupied'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="fs-6 fw-extrabold text-muted">Reserved</div>
                        <div class="fs-2 fw-bold text-dark">{{ $stats['rooms_reserved'] ?? 0 }}</div>
                    </div>
                </div>

                @php
                    $totalRooms = $stats['rooms_total'] ?? 0;
                    $occPct = $totalRooms > 0 ? round((($stats['rooms_occupied'] ?? 0) / $totalRooms) * 100) : 0;
                    $resPct = $totalRooms > 0 ? round((($stats['rooms_reserved'] ?? 0) / $totalRooms) * 100) : 0;
                @endphp

                <div class="progress mb-4" style="height: 12px; border-radius: 99px; background: #e5ebe5;">
                    <div class="progress-bar" style="width: {{ $occPct }}%; background: #d6f843;"></div>
                    <div class="progress-bar" style="width: {{ $resPct }}%; background: #93c5fd;"></div>
                </div>

                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="fs-3 fw-bold text-success">{{ $stats['rooms_available'] ?? 0 }}</div>
                            <div class="small text-muted fw-semibold">Available</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="fs-3 fw-bold text-danger">{{ $stats['rooms_not_ready'] ?? 0 }}</div>
                            <div class="small text-muted fw-semibold">Not Ready</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Distribution & Channel Share Charts Grid -->
<div class="row g-4 mb-4">
    <!-- Room Category Distribution Doughnut Chart -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-pie-chart text-primary me-2"></i>Suite Category Share</h6>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">Active Bookings</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 250px;">
                <canvas id="roomTypeChart" style="max-height: 230px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Channel Breakdown Bar Chart -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-column text-success me-2"></i>Booking Channels</h6>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">Direct vs OTA</span>
            </div>
            <div class="card-body" style="min-height: 250px;">
                <canvas id="channelChart" style="max-height: 230px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions + Property Info + System Status Grid -->
<div class="row g-4 mb-4">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge-fill me-2 text-dark"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-primary text-start fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>New Reservation
                    </a>
                    <a href="{{ route('admin.front-desk.arrivals') }}" class="btn btn-outline-primary text-start fw-bold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Check In Guest
                    </a>
                    <a href="{{ route('admin.front-desk.departures') }}" class="btn btn-outline-primary text-start fw-bold">
                        <i class="bi bi-box-arrow-right me-2"></i>Check Out Guest
                    </a>
                    <a href="{{ route('admin.folios.index') }}" class="btn btn-outline-primary text-start fw-bold">
                        <i class="bi bi-cash-stack me-2"></i>Post Folio Payment
                    </a>
                    <a href="{{ route('admin.night-audit.index') }}" class="btn btn-outline-primary text-start fw-bold">
                        <i class="bi bi-moon-stars me-2"></i>Execute Night Audit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Info -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-building me-2 text-dark"></i>Property Info</h6>
            </div>
            <div class="card-body">
                @if(isset($property) && $property)
                <dl class="mb-0" style="font-size: 0.85rem;">
                    <dt class="text-secondary small">Property Name</dt>
                    <dd class="fw-bold mb-2 text-dark">{{ $property->name }}</dd>

                    <dt class="text-secondary small">Property Code</dt>
                    <dd class="fw-bold mb-2"><code>{{ $property->code }}</code></dd>

                    <dt class="text-secondary small">Operating Currency</dt>
                    <dd class="fw-bold mb-2">{{ $property->currency }}</dd>

                    <dt class="text-secondary small">Total Physical Rooms</dt>
                    <dd class="fw-bold mb-2">{{ $stats['rooms_total'] ?? 0 }} Rooms</dd>

                    <dt class="text-secondary small">Status</dt>
                    <dd class="mb-0">
                        <span class="badge-status {{ $property->status }}">{{ ucfirst($property->status) }}</span>
                    </dd>
                </dl>
                @else
                <div class="empty-state py-4 text-center">
                    <i class="bi bi-building fs-1 text-muted"></i>
                    <p class="mb-0 mt-2 text-muted fw-semibold">No property selected</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-dark"></i>System Operations</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Public Booking Engine</span>
                        <span class="badge-status {{ isset($property) && $property?->booking_engine_enabled ? 'active' : 'inactive' }}">
                            {{ isset($property) && $property?->booking_engine_enabled ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Stripe Payment Adapter</span>
                        <span class="badge-status active">Connected</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Channel Manager Sync</span>
                        <span class="badge-status active">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Night Audit Wizard</span>
                        <span class="badge-status active">Ready</span>
                    </div>

                    <hr class="my-1">

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Live System Clock</span>
                        <span class="fw-bold small text-dark" id="serverClock">{{ now()->format('H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Live Bookings Table -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-dark"></i>Recent Live Reservations</h6>
                <a href="{{ route('admin.reservations.index') }}" class="btn btn-sm btn-outline-dark rounded-pill">View All Bookings</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Confirmation #</th>
                            <th>Guest Name</th>
                            <th>Dates</th>
                            <th>Nights</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Channel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReservations as $res)
                        <tr>
                            <td><code class="fw-bold">{{ $res->confirmation_number }}</code></td>
                            <td class="fw-semibold text-dark">{{ $res->primaryGuest ? $res->primaryGuest->full_name : 'Guest' }}</td>
                            <td>{{ \Carbon\Carbon::parse($res->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($res->check_out)->format('M d, Y') }}</td>
                            <td>{{ $res->nights }}</td>
                            <td class="fw-bold text-dark">{{ $res->currency }} {{ number_format($res->total_minor / 100, 2) }}</td>
                            <td>
                                <span class="badge-status {{ $res->status }}">{{ ucfirst(str_replace('_', ' ', $res->status)) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill px-2 py-1 small">{{ $res->booking_channel }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No reservations recorded yet for this property.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Live Payments & Transactions Stream Table -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between border-bottom">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-credit-card text-success me-2"></i>Live Payment & Transactions Stream</h6>
                <a href="{{ route('admin.folios.index') }}" class="btn btn-sm btn-outline-dark rounded-pill">View All Folios</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ULID</th>
                            <th>Gateway / Provider</th>
                            <th>Guest Name</th>
                            <th>Confirmation #</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $pay)
                        <tr>
                            <td><code class="fw-bold text-dark">{{ $pay->ulid }}</code></td>
                            <td>
                                @if($pay->provider === 'stripe')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold"><i class="fa-brands fa-stripe me-1"></i> Stripe Card</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-bold"><i class="fa-solid fa-money-bill-wave me-1"></i> Front Desk Cash</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-dark">{{ $pay->first_name }} {{ $pay->last_name }}</td>
                            <td><code class="text-primary">{{ $pay->confirmation_number }}</code></td>
                            <td class="fw-extrabold text-dark">{{ $pay->currency }} {{ number_format($pay->amount_minor / 100, 2) }}</td>
                            <td>
                                <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> {{ ucfirst($pay->status) }}</span>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($pay->created_at)->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No payment transactions recorded yet for this property.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Live clock update
    function updateClock() {
        const now = new Date();
        const clock = document.getElementById('serverClock');
        if (clock) {
            clock.textContent = now.toLocaleTimeString('en-GB');
        }
    }
    setInterval(updateClock, 1000);

    // Dynamic Analytics Charts
    document.addEventListener("DOMContentLoaded", function() {
        const currency = "{{ isset($property) && $property ? $property->currency : 'USD' }}";

        // 1. Revenue Chart
        const ctxRev = document.getElementById('revenueChart');
        if (ctxRev) {
            const chartLabels = {!! json_encode($chartLabels ?? []) !!};
            const chartData   = {!! json_encode($chartRevenueData ?? []) !!};

            new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Total Revenue (' + currency + ')',
                        data: chartData,
                        borderColor: '#151b16',
                        backgroundColor: 'rgba(214, 248, 67, 0.45)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#d6f843',
                        pointBorderColor: '#151b16',
                        pointRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return currency + ' ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: '#e5ebe5' },
                            ticks: { callback: value => currency + ' ' + value.toLocaleString() }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Suite Category Share Chart (Doughnut)
        const ctxType = document.getElementById('roomTypeChart');
        if (ctxType) {
            const typeLabels = {!! json_encode($roomTypeLabels ?? []) !!};
            const typeCounts = {!! json_encode($roomTypeCounts ?? []) !!};

            new Chart(ctxType, {
                type: 'doughnut',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        data: typeCounts,
                        backgroundColor: ['#10b981', '#6366f1', '#f59e0b', '#ec4899', '#3b82f6'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // 3. Booking Channels Chart (Bar)
        const ctxChan = document.getElementById('channelChart');
        if (ctxChan) {
            const chanLabels = {!! json_encode($channelLabels ?? []) !!};
            const chanCounts = {!! json_encode($channelCounts ?? []) !!};

            new Chart(ctxChan, {
                type: 'bar',
                data: {
                    labels: chanLabels,
                    datasets: [{
                        label: 'Bookings Count',
                        data: chanCounts,
                        backgroundColor: ['#d6f843', '#3b82f6', '#8b5cf6', '#10b981'],
                        borderRadius: 8,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
