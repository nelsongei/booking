@extends('layouts.app')

@section('title', 'Expected Departures')
@section('page-title', 'Expected Departures Today')
@section('breadcrumb', 'Front Desk › Departures')

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-box-arrow-right text-warning me-2"></i>Expected Departures</h1>
        <p class="text-secondary small mb-0">Roster of in-house stays scheduled to check out on <strong>{{ $targetDate }}</strong></p>
    </div>

    <form action="{{ route('admin.front-desk.departures') }}" method="GET" class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $targetDate }}" onchange="this.form.submit()">
    </form>
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
                        <th>Checked In At</th>
                        <th>Balance Due</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departures as $stay)
                        @php $res = $stay->reservation; @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-dark fs-6 font-monospace">Room {{ $stay->room?->room_number ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $res?->primaryGuest?->fullName ?: 'Guest' }}</div>
                                <div class="text-secondary small">{{ $stay->room?->roomType?->name }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin.reservations.show', $res) }}" class="fw-semibold text-decoration-none">
                                    {{ $res?->confirmation_number }}
                                </a>
                            </td>
                            <td class="text-secondary small">{{ $stay->checked_in_at?->format('M d, H:i') ?: '—' }}</td>
                            <td>
                                @if(($res?->balance_minor ?? 0) > 0)
                                    <span class="badge bg-danger">{{ number_format($res->balance_minor / 100, 2) }} {{ $res->currency }}</span>
                                @else
                                    <span class="badge bg-success">Paid in Full</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('admin.front-desk.check-out', $stay) }}" method="POST" class="d-inline" onsubmit="return confirm('Complete Check-Out for Room {{ $stay->room?->room_number }}? This will mark the room as dirty.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">
                                        <i class="bi bi-box-arrow-right me-1"></i>Check Out
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                <i class="bi bi-door-closed fs-1 d-block mb-2 text-secondary"></i>
                                No expected departures for <strong>{{ $targetDate }}</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
