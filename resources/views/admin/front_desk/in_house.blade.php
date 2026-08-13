@extends('layouts.app')

@section('title', 'In-House Guests')
@section('page-title', 'In-House Guests Roster')
@section('breadcrumb', 'Front Desk › In-House')

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>In-House Guests</h1>
        <p class="text-secondary small mb-0">Currently checked-in guest roster for <strong>{{ $property?->name ?: 'All Properties' }}</strong></p>
    </div>
    <div class="badge bg-primary fs-6 px-3 py-2 rounded-pill">
        {{ $inHouseStays->count() }} Guests In-House
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Room #</th>
                        <th>Primary Guest</th>
                        <th>Confirmation #</th>
                        <th>Arrival & Departure</th>
                        <th>Nights Remaining</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inHouseStays as $stay)
                        @php $res = $stay->reservation; @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-primary fs-6 font-monospace">Room {{ $stay->room?->room_number ?: 'N/A' }}</span>
                                <div class="text-secondary small">{{ $stay->room?->roomType?->code }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $res?->primaryGuest?->fullName ?: 'Guest' }}</div>
                                <div class="text-secondary small">{{ $res?->primaryGuest?->phone ?: 'No phone' }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin.reservations.show', $res) }}" class="fw-semibold text-decoration-none">
                                    {{ $res?->confirmation_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $stay->arrival_date->format('M d') }} &rarr; {{ $stay->departure_date->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ max(0, now()->startOfDay()->diffInDays($stay->departure_date)) }} Day(s) Left
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#moveRoomModal{{ $stay->id }}">
                                    <i class="bi bi-arrow-left-right me-1"></i>Move Room
                                </button>
                                <form action="{{ route('admin.front-desk.check-out', $stay) }}" method="POST" class="d-inline" onsubmit="return confirm('Check Out guest from Room {{ $stay->room?->room_number }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3">
                                        Check Out
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Room Move Modal -->
                        <div class="modal fade" id="moveRoomModal{{ $stay->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.front-desk.move-room', $stay) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-arrow-left-right text-primary me-2"></i>Room Move — Room {{ $stay->room?->room_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Select New Room <span class="text-danger">*</span></label>
                                                <select name="new_room_id" class="form-select form-select-lg" required>
                                                    <option value="">-- Select Vacant/Clean Room --</option>
                                                    @foreach($availableRooms as $rm)
                                                        @if($rm->id !== $stay->room_id)
                                                            <option value="{{ $rm->id }}">Room {{ $rm->room_number }} ({{ $rm->roomType?->name }}) — {{ ucfirst($rm->status) }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Reason for Room Move</label>
                                                <input type="text" name="reason" class="form-control" placeholder="e.g. AC maintenance, guest upgrade, quiet request">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Execute Room Move</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                No guests currently checked in.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
