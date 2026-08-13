@extends('layouts.app')

@section('title', 'Availability & Inventory Matrix')
@section('page-title', 'Availability & Inventory Matrix')
@section('breadcrumb', 'Inventory › Availability Matrix')

@section('content')

<div class="page-header">
    <div>
        <h1>Availability & Inventory Matrix</h1>
        <p>Real-time room availability, blocks, and holds for {{ $property->name }}</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustInventoryModal">
        <i class="bi bi-sliders me-2"></i>Adjust Inventory / Block Rooms
    </button>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.inventory.matrix') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Check-In Date</label>
                <input type="date" class="form-control" name="check_in" value="{{ $checkIn }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Check-Out Date</label>
                <input type="date" class="form-control" name="check_out" value="{{ $checkOut }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Update Date Range
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 14-Day Availability Matrix Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>
        <h6>14-Day Room Type Availability</h6>
    </div>
    <div class="card-body">
        @if(count($availability['room_types']) > 0)
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-start" style="width: 180px;">Room Type</th>
                        @for($i = 0; $i < 14; $i++)
                        @php $d = \Carbon\Carbon::parse($checkIn)->addDays($i); @endphp
                        <th style="min-width: 65px; font-size: 0.72rem;">
                            <div>{{ $d->format('D') }}</div>
                            <div class="fw-bold">{{ $d->format('M d') }}</div>
                        </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($availability['room_types'] as $rtId => $data)
                    <tr>
                        <td class="text-start">
                            <div class="fw-bold">{{ $data['room_type_name'] }}</div>
                            <code class="text-primary small">{{ $data['room_type_code'] }}</code>
                        </td>
                        @for($i = 0; $i < 14; $i++)
                        @php
                            $dateStr = \Carbon\Carbon::parse($checkIn)->addDays($i)->toDateString();
                            $dayData = $data['daily_availability'][$dateStr] ?? null;
                            $avail   = $dayData ? $dayData['available'] : 0;
                        @endphp
                        <td style="background: {{ $avail > 0 ? 'rgba(16,185,129,0.08)' : 'rgba(239,68,68,0.08)' }};">
                            @if($dayData)
                            <div class="fw-bold {{ $avail > 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.9rem;">
                                {{ $avail }}
                            </div>
                            <div class="text-secondary" style="font-size: 0.65rem;" title="Total/Blocked/Sold/Holds">
                                {{ $dayData['total'] }}/{{ $dayData['blocked'] }}/{{ $dayData['sold'] }}/{{ $dayData['holds'] }}
                            </div>
                            @else
                            <span class="text-secondary">—</span>
                            @endif
                        </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding: 40px;">
            <i class="bi bi-grid-3x3-gap"></i>
            <h5>No Room Types Found</h5>
            <p>Create room types first to view availability matrix.</p>
        </div>
        @endif
    </div>
</div>

<!-- Active Holds List -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2 text-warning"></i>Active Transient Holds ({{ $holds->count() }})</h6>
    </div>

    @if($holds->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Hold ULID</th>
                    <th>Room Type</th>
                    <th>Check-In / Out</th>
                    <th>Rooms</th>
                    <th>Expires In</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($holds as $hold)
                <tr>
                    <td><code>{{ $hold->ulid }}</code></td>
                    <td class="fw-semibold">{{ $hold->roomType?->name }}</td>
                    <td>{{ $hold->check_in->format('M d') }} — {{ $hold->check_out->format('M d') }}</td>
                    <td><span class="badge bg-primary rounded-pill">{{ $hold->rooms_count }}</span></td>
                    <td class="text-warning fw-semibold small">{{ $hold->expires_at->diffForHumans() }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.inventory.holds.release', $hold) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i>Release Hold
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body">
        <div class="empty-state" style="padding: 30px;">
            <p class="mb-0 text-secondary">No active transient inventory holds at the moment.</p>
        </div>
    </div>
    @endif
</div>

<!-- Adjust Inventory Modal -->
<div class="modal fade" id="adjustInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.inventory.adjust') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Inventory / Block Rooms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_type_id" required>
                            @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }} ({{ $rt->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" value="{{ now()->addDays(7)->toDateString() }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="block">Block Rooms (Maintenance / OOO)</option>
                            <option value="unblock">Unblock Rooms</option>
                            <option value="protect">Protect Inventory (Hold for Walk-ins)</option>
                            <option value="overbooking">Allow Overbooking Limit</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" value="1" min="1" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Reason / Reference</label>
                        <input type="text" class="form-control" name="reason" placeholder="e.g. Plumbing maintenance in Block A">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
