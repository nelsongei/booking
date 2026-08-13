@extends('layouts.app')

@section('title', 'Reservation ' . $reservation->confirmation_number)
@section('page-title', 'Reservation ' . $reservation->confirmation_number)
@section('breadcrumb', 'Reservations › ' . $reservation->confirmation_number)

@section('content')

<div class="page-header">
    <div>
        <h1><code>{{ $reservation->confirmation_number }}</code></h1>
        <p>Guest: <strong>{{ $reservation->primaryGuest?->fullName ?: 'Guest' }}</strong> &bull; Status: <span class="badge-status {{ $reservation->status }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span></p>
    </div>
    <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-4">
    <!-- Main Reservation Details -->
    <div class="col-md-8">
        <!-- Stay Overview -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-range text-primary"></i>
                    <h6 class="mb-0">Stay Overview</h6>
                </div>
                <span class="badge bg-light text-dark border">{{ $reservation->nights }} Night(s)</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-secondary small">Check-In Date</div>
                        <div class="fw-bold fs-6">{{ $reservation->check_in->format('D, M d, Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-secondary small">Check-Out Date</div>
                        <div class="fw-bold fs-6">{{ $reservation->check_out->format('D, M d, Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-secondary small">Guests</div>
                        <div class="fw-bold fs-6">{{ $reservation->adults }} Adult(s), {{ $reservation->children }} Child(ren)</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-secondary small">Room Type</div>
                        <div class="fw-semibold">{{ $reservation->rooms->first()?->roomType?->name ?: 'Standard' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-secondary small">Rate Plan</div>
                        <div class="fw-semibold">{{ $reservation->ratePlan?->name ?: 'Standard Rate' }}</div>
                    </div>
                </div>

                @if($reservation->special_requests)
                <hr>
                <div class="text-secondary small">Special Requests</div>
                <div class="fw-medium text-dark mt-1">{{ $reservation->special_requests }}</div>
                @endif
            </div>
        </div>

        <!-- Nightly Charges Breakdown -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-receipt me-2 text-success"></i>
                <h6>Nightly Charges & Pricing Breakdown</h6>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Rate Breakdown</th>
                            <th class="text-end">Night Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservation->rooms->first()?->nights ?? [] as $night)
                        <tr>
                            <td>{{ $night->date->format('D, M d, Y') }}</td>
                            <td class="text-secondary small">Base room charge + taxes</td>
                            <td class="text-end fw-bold">{{ number_format($night->total_minor / 100, 2) }} {{ $night->currency }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end text-secondary">Subtotal:</td>
                            <td class="text-end fw-semibold">{{ number_format($reservation->subtotal_minor / 100, 2) }} {{ $reservation->currency }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end text-secondary">Taxes & Fees:</td>
                            <td class="text-end fw-semibold">{{ number_format($reservation->tax_minor / 100, 2) }} {{ $reservation->currency }}</td>
                        </tr>
                        <tr class="fs-6">
                            <td colspan="2" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($reservation->total_minor / 100, 2) }} {{ $reservation->currency }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Status Transition History -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2 text-info"></i>
                <h6>Status History & Audit Trail</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @forelse($reservation->statusHistory as $hist)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--sidebar-accent); flex-shrink: 0;"></div>
                        <div class="flex-fill">
                            <span class="fw-semibold">Status changed to <span class="badge-status {{ $hist->to_status }}">{{ ucfirst(str_replace('_', ' ', $hist->to_status)) }}</span></span>
                            @if($hist->reason)<span class="text-secondary small">({{ $hist->reason }})</span>@endif
                        </div>
                        <div class="text-secondary small">{{ $hist->changed_at->format('M d, H:i') }} &bull; {{ $hist->changedBy?->name ?: 'System' }}</div>
                    </div>
                    @empty
                    <div class="text-secondary small">No state transitions recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions & Guest Profile -->
    <div class="col-md-4">
        <!-- Guest Profile -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person me-2 text-primary"></i>
                <h6>Primary Guest Profile</h6>
            </div>
            <div class="card-body">
                @if($reservation->primaryGuest)
                <dl class="mb-0" style="font-size: 0.85rem;">
                    <dt class="text-secondary">Name</dt>
                    <dd class="fw-bold mb-2">{{ $reservation->primaryGuest->fullName }}</dd>

                    <dt class="text-secondary">Email</dt>
                    <dd class="mb-2">{{ $reservation->primaryGuest->email ?: '—' }}</dd>

                    <dt class="text-secondary">Phone</dt>
                    <dd class="mb-0">{{ $reservation->primaryGuest->phone ?: '—' }}</dd>
                </dl>
                @else
                <span class="text-secondary small">No guest profile attached</span>
                @endif
            </div>
        </div>

        <!-- Payments & Invoicing -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card text-success"></i>
                    <h6 class="mb-0">Payments & Invoicing</h6>
                </div>
                <span class="badge bg-success">{{ number_format($reservation->balance_minor / 100, 2) }} {{ $reservation->currency }} Due</span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-plus-circle me-1"></i>Process Payment / Charge Card
                    </button>

                    <a href="{{ route('admin.reservations.invoice.download', $reservation) }}" class="btn btn-sm btn-outline-dark w-100">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice PDF
                    </a>

                    <form method="POST" action="{{ route('admin.reservations.send-confirmation', $reservation) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-envelope me-1"></i>Resend Confirmation Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- State Transition Actions -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-toggles me-2 text-warning"></i>
                <h6>Update Reservation Status</h6>
            </div>
            <div class="card-body">
                @if(!$reservation->isCancelled())
                <form method="POST" action="{{ route('admin.reservations.status', $reservation) }}" class="d-grid gap-2">
                    @csrf
                    @if($reservation->status === 'confirmed')
                    <button type="submit" name="status" value="checked_in" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Check In Guest
                    </button>
                    @elseif($reservation->status === 'checked_in')
                    <button type="submit" name="status" value="checked_out" class="btn btn-warning">
                        <i class="bi bi-box-arrow-right me-1"></i>Check Out Guest
                    </button>
                    @endif

                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i>Cancel Reservation
                    </button>
                </form>
                @else
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle me-1"></i>Reservation Cancelled
                    @if($reservation->cancellation_reason)
                    <div class="small mt-1">Reason: {{ $reservation->cancellation_reason }}</div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Process Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.reservations.payments.store', $reservation) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Amount ({{ $reservation->currency }}) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" value="{{ number_format($reservation->balance_minor / 100, 2, '.', '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="provider" class="form-select">
                            <option value="stripe">Stripe Online Gateway / Saved Card</option>
                            <option value="cash">Cash Payment</option>
                            <option value="pos_terminal">POS Card Terminal</option>
                            <option value="bank_transfer">Bank Wire Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Record & Charge Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.reservations.status', $reservation) }}">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Reservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small">Cancelling this reservation will release the locked room inventory back to the availability pool.</p>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="2" placeholder="e.g. Guest requested cancellation due to travel changes" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
