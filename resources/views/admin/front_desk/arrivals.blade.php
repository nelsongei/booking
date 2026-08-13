@extends('layouts.app')

@section('title', 'Expected Arrivals')
@section('page-title', 'Expected Arrivals Today')
@section('breadcrumb', 'Front Desk › Arrivals')

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-box-arrow-in-right text-success me-2"></i>Expected Arrivals</h1>
        <p class="text-secondary small mb-0">Roster of guests scheduled to arrive on <strong>{{ $targetDate }}</strong></p>
    </div>

    <form action="{{ route('admin.front-desk.arrivals') }}" method="GET" class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $targetDate }}" onchange="this.form.submit()">
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Confirmation #</th>
                        <th>Primary Guest</th>
                        <th>Room Type</th>
                        <th>Nights</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arrivals as $res)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.reservations.show', $res) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $res->confirmation_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $res->primaryGuest?->fullName ?: 'Guest' }}</div>
                                <div class="text-secondary small">{{ $res->primaryGuest?->email ?: 'No email' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $res->rooms->first()?->roomType?->name ?: 'Standard' }}</span>
                            </td>
                            <td>{{ $res->nights }} Night(s)</td>
                            <td>
                                <span class="badge bg-warning text-dark">Confirmed</span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#checkInModal{{ $res->id }}">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Check In
                                </button>
                                <form action="{{ route('admin.front-desk.no-show', $res) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this guest as No-Show?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Mark No-Show">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Check In Modal -->
                        <div class="modal fade" id="checkInModal{{ $res->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.front-desk.check-in', $res) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-box-arrow-in-right text-success me-2"></i>Check In — {{ $res->confirmation_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Assign Physical Room <span class="text-danger">*</span></label>
                                                <select name="room_id" class="form-select form-select-lg" required>
                                                    <option value="">-- Select Available Room --</option>
                                                    @foreach($availableRooms as $rm)
                                                        <option value="{{ $rm->id }}">Room {{ $rm->room_number }} ({{ $rm->roomType?->name }}) — {{ ucfirst($rm->status) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">ID Type</label>
                                                    <select name="id_type" class="form-select">
                                                        <option value="passport">Passport</option>
                                                        <option value="national_id">National ID Card</option>
                                                        <option value="drivers_license">Driver's License</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">ID Number</label>
                                                    <input type="text" name="id_number" class="form-control" placeholder="e.g. A12345678">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Check-In Notes</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="Special requests, luggage notes, etc."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Confirm Check-In</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                <i class="bi bi-calendar-check fs-1 d-block mb-2 text-secondary"></i>
                                No expected arrivals for <strong>{{ $targetDate }}</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
