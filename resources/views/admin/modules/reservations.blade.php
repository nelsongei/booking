@extends('layouts.app')

@section('title', 'Reservations')
@section('page-title', 'Reservations')
@section('breadcrumb', 'Reservations › All Bookings')

@section('content')

<div class="page-header flex-wrap gap-3">
    <div>
        <h1>Reservations List</h1>
        <p>Central Reservation Management for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBookingModal">
            <i class="bi bi-plus-lg me-2"></i>New Reservation
        </button>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" onsubmit="return false;">
            <div class="col-md-4">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control ps-5 rounded-pill" placeholder="Search by booking ID, guest, room...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select rounded-pill">
                    <option value="">All Statuses</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="checked_in">Checked-In</option>
                    <option value="checked_out">Checked-Out</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control rounded-pill" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-primary w-100 rounded-pill fw-bold">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Reservations Roster Table (Lodgify Style) -->
<div class="card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2 text-dark"></i>Bookings Ledger</h6>
        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">Total Bookings: {{ $reservations->count() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Name</th>
                    <th>Room Type</th>
                    <th>Room Number</th>
                    <th>Duration</th>
                    <th>Check-In & Check-Out</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                    <tr>
                        <td class="fw-bold"><code>{{ $res->confirmation_number }}</code></td>
                        <td class="fw-bold text-dark">
                            {{ $res->primaryGuest?->fullName ?: 'Walk-In Guest' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                {{ $res->rooms->first()?->roomType?->name ?: 'Standard' }}
                            </span>
                        </td>
                        <td class="fw-semibold">
                            {{ $res->stays->first()?->room?->room_number ?: 'Unassigned' }}
                        </td>
                        <td class="small text-muted">{{ $res->nights }} night(s)</td>
                        <td class="small">
                            {{ $res->check_in->format('M d, Y') }} — {{ $res->check_out->format('M d, Y') }}
                        </td>
                        <td class="fw-bold text-dark">
                            ${{ number_format($res->total_minor / 100, 2) }}
                        </td>
                        <td>
                            <span class="badge-status {{ $res->status }}">{{ ucfirst(str_replace('_', ' ', $res->status)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.reservations.show', $res) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-check fs-1 text-muted d-block mb-2"></i>
                            No reservations found for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
