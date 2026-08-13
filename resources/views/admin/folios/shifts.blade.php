@extends('layouts.app')

@section('title', 'Cashier Shifts')
@section('page-title', 'Cashier Shifts & Cash Balancing')
@section('breadcrumb', 'Cashiering › Shifts')

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-person-workspace text-primary me-2"></i>Cashier Shifts</h1>
        <p class="text-secondary small mb-0">Manage cashier shift floats, cash receipts, and end-of-shift cash drawer balancing.</p>
    </div>
    <div>
        @if(!$openShift)
            <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                <i class="bi bi-play-circle me-1"></i>Open Cashier Shift
            </button>
        @else
            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                <i class="bi bi-stop-circle me-1"></i>Close Active Shift
            </button>
        @endif
    </div>
</div>

<!-- Active Shift Banner if present -->
@if($openShift)
    <div class="alert alert-warning border-warning rounded-4 shadow-sm p-4 mb-4 d-flex flex-wrap justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark p-3 rounded-circle fs-3">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <div class="fw-bold text-dark fs-5">Active Shift Open</div>
                <div class="text-secondary small">Opened at {{ $openShift->opened_at->format('M d, Y H:i') }} &bull; Staff: <strong>{{ auth()->user()->name }}</strong></div>
            </div>
        </div>

        <div class="mt-3 mt-sm-0 text-sm-end">
            <small class="text-secondary d-block">Opening Float Balance</small>
            <div class="fw-bold fs-4 text-dark">{{ number_format($openShift->opening_balance_minor / 100, 2) }} {{ $property?->currency }}</div>
        </div>
    </div>
@endif

<!-- Shifts History Table -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Shift History & Balancing Records</h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Shift ID</th>
                        <th>Cashier Staff</th>
                        <th>Opened At</th>
                        <th>Status</th>
                        <th class="text-end">Opening Float</th>
                        <th class="text-end">Expected Closing</th>
                        <th class="text-end">Actual Closing</th>
                        <th class="text-end pe-4">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $s)
                        <tr>
                            <td class="ps-4 fw-bold font-monospace">#{{ $s->id }}</td>
                            <td class="fw-semibold">{{ $s->user?->name ?: 'Staff' }}</td>
                            <td class="text-secondary small">{{ $s->opened_at->format('M d, H:i') }}</td>
                            <td>
                                <span class="badge {{ $s->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($s->opening_balance_minor / 100, 2) }}</td>
                            <td class="text-end fw-semibold text-secondary">
                                {{ $s->expected_closing_minor !== null ? number_format($s->expected_closing_minor / 100, 2) : '—' }}
                            </td>
                            <td class="text-end fw-bold text-dark">
                                {{ $s->closing_balance_minor !== null ? number_format($s->closing_balance_minor / 100, 2) : '—' }}
                            </td>
                            <td class="text-end pe-4">
                                @if($s->variance_minor !== null)
                                    @if($s->variance_minor === 0)
                                        <span class="badge bg-success">0.00 (Balanced)</span>
                                    @elseif($s->variance_minor > 0)
                                        <span class="badge bg-info">+{{ number_format($s->variance_minor / 100, 2) }} (Over)</span>
                                    @else
                                        <span class="badge bg-danger">{{ number_format($s->variance_minor / 100, 2) }} (Short)</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-5 text-center text-muted">
                                <i class="bi bi-person-workspace fs-1 d-block mb-2 text-secondary"></i>
                                No cashier shift records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Open Shift Modal -->
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.folios.shifts.open') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="bi bi-play-circle me-2"></i>Open Cashier Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opening Cash Float Balance ({{ $property?->currency }}) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control form-control-lg" placeholder="100.00" value="100.00" required>
                        <small class="text-muted">Enter starting cash float amount in drawer.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Open Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Shift Modal -->
@if($openShift)
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.folios.shifts.close', $openShift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-warning"><i class="bi bi-stop-circle me-2"></i>Close Cashier Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Actual Cash Counted in Drawer ({{ $property?->currency }}) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="closing_balance" class="form-control form-control-lg" placeholder="0.00" required>
                        <small class="text-muted">Perform physical cash count and enter exact total in cash drawer.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Shift Closing Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Variance explanations or handover notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-circle me-1"></i>Close & Balance Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@section('scripts')
@endsection
