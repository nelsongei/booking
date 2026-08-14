@extends('layouts.app')

@section('title', 'PMS Tape Chart')
@section('page-title', 'Interactive Tape Chart & Occupancy Timeline')
@section('breadcrumb', 'Front Desk › Tape Chart')

@section('content')

<style>
    .tape-chart-container {
        font-family: inherit;
    }
    .booking-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.18) !important;
    }
    .empty-slot-td:hover {
        background-color: #e2e8f0 !important;
    }
    .empty-slot-td:hover .empty-slot-inner {
        opacity: 1 !important;
        color: #2563eb !important;
    }
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 5;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }
    .kpi-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.15s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }
</style>

<div class="tape-chart-container">

    <!-- KPI & Occupancy Banner -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card kpi-card bg-white shadow-sm p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium text-uppercase">Total Rooms</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $totalRooms }}</div>
                    </div>
                    <div class="bg-primary-subtle text-primary p-2.5 rounded-3">
                        <i class="bi bi-door-open fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>{{ $cleanCount }} Clean</span> &bull; 
                    <span class="text-warning-emphasis fw-semibold">{{ $dirtyCount }} Dirty</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card kpi-card bg-white shadow-sm p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium text-uppercase">Occupancy Today</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $occupancyRate }}%</div>
                    </div>
                    <div class="bg-success-subtle text-success p-2.5 rounded-3">
                        <i class="bi bi-pie-chart fs-4"></i>
                    </div>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $occupancyRate) }}%"></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card kpi-card bg-white shadow-sm p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium text-uppercase">Arrivals Today</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $arrivalsCount }}</div>
                    </div>
                    <div class="bg-warning-subtle text-warning-emphasis p-2.5 rounded-3">
                        <i class="bi bi-box-arrow-in-right fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <a href="{{ route('admin.front-desk.arrivals') }}" class="text-decoration-none text-warning-emphasis fw-semibold">
                        View Arrivals Roster &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card kpi-card bg-white shadow-sm p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium text-uppercase">Departures Today</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $departuresCount }}</div>
                    </div>
                    <div class="bg-info-subtle text-info-emphasis p-2.5 rounded-3">
                        <i class="bi bi-box-arrow-right fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <a href="{{ route('admin.front-desk.departures') }}" class="text-decoration-none text-info-emphasis fw-semibold">
                        View Departures &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center justify-content-between">
                
                <!-- Left: Navigation & Date Controls -->
                <div class="col-12 col-lg-7 d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->copy()->subDays($daysCount)->toDateString(), 'days' => $daysCount]) }}" 
                       class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                        <i class="bi bi-chevron-left me-1"></i> Prev {{ $daysCount }} Days
                    </a>
                    
                    <a href="{{ route('admin.tape-chart.index', ['days' => $daysCount]) }}" 
                       class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
                        <i class="bi bi-calendar-check me-1"></i> Today
                    </a>
                    
                    <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->copy()->addDays($daysCount)->toDateString(), 'days' => $daysCount]) }}" 
                       class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                        Next {{ $daysCount }} Days <i class="bi bi-chevron-right ms-1"></i>
                    </a>

                    <!-- Jump to Date Picker -->
                    <form action="{{ route('admin.tape-chart.index') }}" method="GET" class="d-flex align-items-center ms-lg-2">
                        <input type="hidden" name="days" value="{{ $daysCount }}">
                        <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" 
                               class="form-control form-control-sm rounded-pill px-3 shadow-xs" style="max-width: 150px;" 
                               onchange="this.form.submit()">
                    </form>
                </div>

                <!-- Right: View Range Selector & Search Filter -->
                <div class="col-12 col-lg-5 d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                    
                    <!-- Days Pill Range -->
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->toDateString(), 'days' => 7]) }}" 
                           class="btn {{ $daysCount == 7 ? 'btn-dark' : 'btn-outline-secondary' }}">7 Days</a>
                        <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->toDateString(), 'days' => 14]) }}" 
                           class="btn {{ $daysCount == 14 ? 'btn-dark' : 'btn-outline-secondary' }}">14 Days</a>
                        <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->toDateString(), 'days' => 30]) }}" 
                           class="btn {{ $daysCount == 30 ? 'btn-dark' : 'btn-outline-secondary' }}">30 Days</a>
                    </div>

                    <!-- Filter Input -->
                    <div class="input-group input-group-sm style-search" style="max-width: 200px;">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="tapeChartRoomFilter" class="form-control border-start-0 rounded-end-pill" placeholder="Filter room...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend & Timeline Sub-header -->
        <div class="card-footer bg-light-subtle py-2.5 px-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small fw-semibold text-dark">
                <i class="bi bi-calendar3 text-primary me-1.5"></i> 
                Timeline: <strong>{{ $startDate->format('M d, Y') }}</strong> &rarr; <strong>{{ $startDate->copy()->addDays($daysCount - 1)->format('M d, Y') }}</strong>
            </div>

            <!-- Status Legend -->
            <div class="d-flex flex-wrap align-items-center gap-3 small">
                <span class="d-flex align-items-center gap-1.5">
                    <span style="width:14px;height:14px;border-radius:4px;background: linear-gradient(135deg, #10b981 0%, #059669 100%);"></span> 
                    <strong>Checked-In</strong>
                </span>
                <span class="d-flex align-items-center gap-1.5">
                    <span style="width:14px;height:14px;border-radius:4px;background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"></span> 
                    <strong>Confirmed</strong>
                </span>
                <span class="d-flex align-items-center gap-1.5">
                    <span style="width:14px;height:14px;border-radius:4px;background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);"></span> 
                    <strong>Held / Pending</strong>
                </span>
                <span class="d-flex align-items-center gap-1.5">
                    <span style="width:14px;height:14px;border-radius:4px;background: linear-gradient(135deg, #64748b 0%, #475569 100%);"></span> 
                    <strong>Checked-Out</strong>
                </span>
                <span class="d-flex align-items-center gap-1.5">
                    <span style="width:14px;height:14px;border-radius:4px;background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"></span> 
                    <strong>Out of Order</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Tape Chart Main Timeline Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center" id="tapeChartTable" style="font-size: 0.82rem;">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start ps-3 sticky-col bg-dark text-white border-end shadow-xs" style="width: 170px; min-width: 170px;">
                            Physical Room
                        </th>
                        @foreach($dates as $d)
                            @php
                                $isToday = $d->isToday();
                                $isWeekend = $d->isWeekend();
                                $colHeaderBg = $isToday ? 'bg-primary text-white' : ($isWeekend ? 'bg-secondary bg-opacity-25 text-white' : 'bg-dark text-white');
                            @endphp
                            <th style="min-width: 75px;" class="{{ $colHeaderBg }} border-end py-2">
                                <div class="small text-uppercase opacity-75 fw-normal" style="font-size: 0.7rem;">{{ $d->format('D') }}</div>
                                <div class="fw-bold fs-6 lh-1 mt-0.5">{{ $d->format('d') }}</div>
                                <div class="small opacity-75" style="font-size: 0.68rem;">{{ $d->format('M') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dateStrings = array_map(fn($d) => $d->toDateString(), $dates);
                        $totalDaysCount = count($dateStrings);
                    @endphp

                    @forelse($groupedRooms as $roomTypeName => $typeRooms)
                        <!-- Room Type Group Header -->
                        <tr class="table-secondary text-dark fw-bold group-row border-bottom">
                            <td colspan="{{ $totalDaysCount + 1 }}" class="text-start ps-3 py-2 bg-light shadow-xs">
                                <i class="bi bi-door-open-fill text-primary me-2"></i>
                                <span>{{ $roomTypeName }}</span>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-2 fw-normal" style="font-size: 0.72rem;">
                                    {{ count($typeRooms) }} {{ Str::plural('room', count($typeRooms)) }}
                                </span>
                            </td>
                        </tr>

                        @foreach($typeRooms as $rm)
                            <tr class="room-row" data-room-number="{{ strtolower($rm->room_number) }}" data-room-type="{{ strtolower($roomTypeName) }}">
                                <!-- Physical Room Header Cell -->
                                <td class="text-start ps-3 bg-white sticky-col border-end shadow-xs" style="width: 170px; min-width: 170px;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold text-dark fs-6">Room <code>{{ $rm->room_number }}</code></div>
                                            @if($rm->floor)
                                                <div class="text-muted small" style="font-size: 0.7rem;">Floor {{ $rm->floor }}</div>
                                            @endif
                                        </div>
                                        @php
                                            $statusBadgeClass = match($rm->status) {
                                                'clean', 'inspected' => 'bg-success-subtle text-success border border-success-subtle',
                                                'dirty'              => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                'occupied'           => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                                'out_of_order'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                default              => 'bg-secondary-subtle text-secondary'
                                            };
                                        @endphp
                                        <span class="badge rounded-pill {{ $statusBadgeClass }} small" style="font-size: 0.65rem;">
                                            {{ ucfirst($rm->status) }}
                                        </span>
                                    </div>
                                </td>

                                @php
                                    $colIdx = 0;
                                @endphp

                                @while($colIdx < $totalDaysCount)
                                    @php
                                        $curDateStr = $dateStrings[$colIdx];
                                        $cell = $matrix[$rm->id][$curDateStr] ?? null;
                                    @endphp

                                    @if($cell)
                                        @php
                                            $itemKey = $cell['key'];
                                            $colspan = 1;
                                            for ($j = $colIdx + 1; $j < $totalDaysCount; $j++) {
                                                $nextDateStr = $dateStrings[$j];
                                                $nextCell = $matrix[$rm->id][$nextDateStr] ?? null;
                                                if ($nextCell && $nextCell['key'] === $itemKey) {
                                                    $colspan++;
                                                } else {
                                                    break;
                                                }
                                            }

                                            $statusStyle = match($cell['status']) {
                                                'checked_in'  => 'background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;',
                                                'checked_out' => 'background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: #fff;',
                                                'confirmed'   => 'background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff;',
                                                'held'        => 'background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: #fff;',
                                                default       => 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff;'
                                            };
                                        @endphp

                                        <td colspan="{{ $colspan }}" class="p-1 align-middle text-start" style="position: relative; height: 54px;">
                                            <div class="booking-pill shadow-sm rounded-3 px-2.5 py-2 d-flex align-items-center justify-content-between position-relative h-100"
                                                 style="{{ $statusStyle }} cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease;"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#reservationModal-{{ $cell['key'] }}">
                                                
                                                <div class="d-flex align-items-center gap-2 overflow-hidden me-1">
                                                    <span class="badge bg-white text-dark shadow-xs fw-bold px-1.5 py-0.5 rounded-pill" style="font-size: 0.65rem;">
                                                        @if($cell['status'] === 'checked_in')
                                                            <i class="bi bi-key-fill text-success me-0.5"></i>IN
                                                        @elseif($cell['status'] === 'confirmed')
                                                            <i class="bi bi-check-circle-fill text-warning me-0.5"></i>CONF
                                                        @else
                                                            <i class="bi bi-clock-fill text-primary me-0.5"></i>HELD
                                                        @endif
                                                    </span>
                                                    <div class="text-truncate">
                                                        <div class="fw-bold lh-1 text-truncate" style="font-size: 0.84rem;">
                                                            {{ $cell['guest_name'] }}
                                                        </div>
                                                        <div class="small opacity-90 lh-1 mt-1 text-truncate" style="font-size: 0.7rem;">
                                                            #{{ $cell['confirmation'] }} &bull; {{ $cell['nights'] }} Nights
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($cell['balance'] > 0)
                                                    <span class="badge bg-white text-danger fw-bold rounded-pill px-2 py-1 small shadow-xs ms-1 flex-shrink-0" style="font-size: 0.68rem;" title="Balance Due: ${{ number_format($cell['balance'], 2) }}">
                                                        ${{ number_format($cell['balance'], 0) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-white text-success rounded-circle p-1 ms-1 flex-shrink-0" style="font-size: 0.65rem;" title="Paid in Full">
                                                        <i class="bi bi-check-lg"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        @php
                                            $colIdx += $colspan;
                                        @endphp
                                    @else
                                        @php
                                            $cellDateObj = Carbon::parse($curDateStr);
                                            $slotBg = $cellDateObj->isToday() ? 'bg-primary bg-opacity-10' : ($cellDateObj->isWeekend() ? 'bg-secondary bg-opacity-10' : 'bg-light-subtle');
                                        @endphp
                                        <td class="{{ $slotBg }} text-center p-0 align-middle empty-slot-td border-end" 
                                            style="height: 54px; cursor: pointer; transition: background-color 0.15s ease;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#quickBookModal"
                                            data-room-id="{{ $rm->id }}"
                                            data-room-number="{{ $rm->room_number }}"
                                            data-date="{{ $curDateStr }}"
                                            title="Click to Book Room {{ $rm->room_number }} on {{ $cellDateObj->format('M d, Y') }}">
                                            <div class="empty-slot-inner d-flex align-items-center justify-content-center h-100 text-muted opacity-25">
                                                <i class="bi bi-plus-circle fs-6"></i>
                                            </div>
                                        </td>
                                        @php
                                            $colIdx++;
                                        @endphp
                                    @endif
                                @endwhile
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ $totalDaysCount + 1 }}" class="py-5 text-center text-muted">
                                <i class="bi bi-building fs-1 d-block mb-2 text-secondary"></i>
                                No physical rooms configured yet for {{ $property?->name ?: 'this property' }}. 
                                <a href="{{ route('admin.rooms.create') }}" class="btn btn-sm btn-primary ms-2 rounded-pill px-3">Add Physical Rooms</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Render Dynamic Interactive Modals for Matrix Items -->
@php
    $renderedModalKeys = [];
@endphp

@foreach($matrix as $rmId => $dateCells)
    @foreach($dateCells as $dateStr => $cell)
        @if(!in_array($cell['key'], $renderedModalKeys) && !empty($cell['reservation']))
            @php
                $renderedModalKeys[] = $cell['key'];
                $res   = $cell['reservation'];
                $guest = $cell['guest'];
                $stay  = $cell['stay'];
            @endphp

            <div class="modal fade" id="reservationModal-{{ $cell['key'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-dark text-white py-3 rounded-top-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-pill px-2.5 py-1 fs-6">
                                    #{{ $cell['confirmation'] }}
                                </span>
                                <h5 class="modal-title mb-0 fw-bold text-white">
                                    {{ $cell['guest_name'] }}
                                </h5>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold text-uppercase text-secondary small mb-3">Guest Information</h6>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-person-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-6 text-dark">{{ $guest?->fullName ?: 'Guest' }}</div>
                                            <div class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $guest?->email ?: 'N/A' }}</div>
                                            <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $guest?->phone ?: 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-secondary small mb-3">Stay & Room Details</h6>
                                    <div class="bg-light p-3 rounded-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Status:</span>
                                            <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-2.5 py-1">
                                                {{ strtoupper(str_replace('_', ' ', $cell['status'])) }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Check-In:</span>
                                            <span class="fw-semibold text-dark">{{ Carbon::parse($cell['check_in'])->format('M d, Y') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Check-Out:</span>
                                            <span class="fw-semibold text-dark">{{ Carbon::parse($cell['check_out'])->format('M d, Y') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">Balance Due:</span>
                                            <span class="fw-bold {{ $cell['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                                ${{ number_format($cell['balance'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3 rounded-bottom-4 d-flex justify-content-between">
                            <a href="{{ route('admin.reservations.show', $res) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="bi bi-file-earmark-text me-1"></i> View Reservation & Folio
                            </a>

                            <div class="d-flex gap-2">
                                @if($cell['status'] === 'confirmed' || $cell['status'] === 'held')
                                    <form action="{{ route('admin.front-desk.check-in', $res) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="room_id" value="{{ $rmId }}">
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                            <i class="bi bi-key me-1"></i> Execute Check-In
                                        </button>
                                    </form>
                                @elseif($cell['status'] === 'checked_in' && $stay)
                                    <form action="{{ route('admin.front-desk.check-out', $stay) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                            <i class="bi bi-box-arrow-right me-1"></i> Execute Check-Out
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endforeach

<!-- Quick Booking Modal for Empty Slots -->
<div class="modal fade" id="quickBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white py-3 rounded-top-4">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Quick Reservation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-calendar-plus fs-2"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Create Reservation for Room <span id="quickBookRoomNum" class="text-primary"></span></h6>
                <p class="text-muted small mb-4">Starting Check-In Date: <strong id="quickBookDate"></strong></p>
                <div class="d-grid gap-2">
                    <a href="#" id="quickBookBtn" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm">
                        Proceed to Reservation Engine &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterInput = document.getElementById('tapeChartRoomFilter');
        const roomRows = document.querySelectorAll('.room-row');

        if (filterInput) {
            filterInput.addEventListener('keyup', function () {
                const query = this.value.toLowerCase().trim();
                roomRows.forEach(row => {
                    const roomNum = row.getAttribute('data-room-number') || '';
                    const roomType = row.getAttribute('data-room-type') || '';
                    if (roomNum.includes(query) || roomType.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        const quickBookModal = document.getElementById('quickBookModal');
        if (quickBookModal) {
            quickBookModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const roomId = button.getAttribute('data-room-id');
                const roomNum = button.getAttribute('data-room-number');
                const dateStr = button.getAttribute('data-date');

                document.getElementById('quickBookRoomNum').textContent = roomNum;
                document.getElementById('quickBookDate').textContent = dateStr;

                const createRoute = "{{ route('admin.reservations.create') }}";
                document.getElementById('quickBookBtn').href = `${createRoute}?room_id=${roomId}&check_in=${dateStr}`;
            });
        }
    });
</script>

@endsection
