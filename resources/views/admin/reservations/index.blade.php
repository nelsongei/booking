@extends('layouts.app')

@section('title', 'Reservations')
@section('page-title', 'Reservations')
@section('breadcrumb', 'Reservations › All Bookings')

@section('content')

<div class="page-header">
    <div>
        <h1>Reservations</h1>
        <p>Central Reservation Management for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>New Reservation
    </a>
</div>

<!-- Filters & Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reservations.index') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <div class="search-wrap w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input w-100" name="search"
                           value="{{ request('search') }}" placeholder="Search by confirmation #, guest name, or email...">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Filter by Status</label>
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show', 'inquiry', 'held'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Reservations Table -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Reservations ({{ $reservations->total() }})</h6>
    </div>

    @if($reservations->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Confirmation #</th>
                    <th>Primary Guest</th>
                    <th>Dates / Stay</th>
                    <th>Room Type</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $res)
                <tr>
                    <td>
                        <a href="{{ route('admin.reservations.show', $res) }}" class="fw-bold text-primary text-decoration-none">
                            <code>{{ $res->confirmation_number }}</code>
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $res->primaryGuest?->fullName ?: 'Guest' }}</div>
                        <div class="text-secondary small">{{ $res->primaryGuest?->email }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $res->check_in->format('M d') }} — {{ $res->check_out->format('M d, Y') }}</div>
                        <div class="text-secondary small">{{ $res->nights }} night(s) &bull; {{ $res->adults }}A, {{ $res->children }}C</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $res->rooms->first()?->roomType?->name ?: 'Standard' }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-primary">{{ number_format($res->total_minor / 100, 2) }} {{ $res->currency }}</div>
                    </td>
                    <td>
                        <span class="badge-status {{ $res->status }}">{{ ucfirst(str_replace('_', ' ', $res->status)) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.reservations.show', $res) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($reservations->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $reservations->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-calendar-check"></i>
            <h5>No Reservations Found</h5>
            <p>Create the first reservation for {{ $property->name }}.</p>
            <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>New Reservation
            </a>
        </div>
    </div>
    @endif
</div>

@endsection
