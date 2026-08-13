@extends('layouts.guest')

@section('title', 'Reservation Details — ' . $reservation->confirmation_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Header status card -->
        <div class="card-custom p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Confirmation Number</span>
                    <h2 class="fw-bold font-monospace text-dark mb-0">{{ $reservation->confirmation_number }}</h2>
                </div>
                <div>
                    @php
                        $statusBadge = match($reservation->status) {
                            'confirmed'  => 'bg-success',
                            'checked_in' => 'bg-primary',
                            'checked_out'=> 'bg-secondary',
                            'cancelled'  => 'bg-danger',
                            default      => 'bg-warning text-dark'
                        };
                    @endphp
                    <span class="badge {{ $statusBadge }} fs-6 px-3 py-2 rounded-pill text-uppercase">
                        {{ str_replace('_', ' ', $reservation->status) }}
                    </span>
                </div>
            </div>

            <p class="text-muted mb-0"><i class="fa-solid fa-hotel text-warning me-1"></i> <strong>{{ $reservation->property->name ?? 'Hotel Property' }}</strong></p>
        </div>

        <!-- Reservation Details Card -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-info text-warning me-2"></i> Stay Information</h5>

            <div class="row g-3 mb-4 p-3 bg-light rounded-3">
                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold d-block">Check-In</small>
                    <div class="fw-bold text-dark fs-5">{{ \Carbon\Carbon::parse($reservation->check_in)->format('M d, Y') }}</div>
                </div>

                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold d-block">Check-Out</small>
                    <div class="fw-bold text-dark fs-5">{{ \Carbon\Carbon::parse($reservation->check_out)->format('M d, Y') }}</div>
                </div>
            </div>

            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Guest Name</span>
                <span class="fw-bold text-dark">{{ $reservation->primaryGuest->first_name ?? '' }} {{ $reservation->primaryGuest->last_name ?? '' }}</span>
            </div>

            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Contact Email</span>
                <span class="fw-semibold text-dark">{{ $reservation->primaryGuest->email ?? '' }}</span>
            </div>

            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Duration</span>
                <span class="fw-semibold text-dark">{{ $reservation->nights }} {{ $reservation->nights == 1 ? 'Night' : 'Nights' }}</span>
            </div>

            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Occupancy</span>
                <span class="fw-semibold text-dark">{{ $reservation->adults }} Adults @if($reservation->children > 0), {{ $reservation->children }} Children @endif</span>
            </div>

            <div class="d-flex justify-content-between py-3">
                <span class="fw-bold text-dark fs-5">Total Amount</span>
                <span class="price-tag fs-4">{{ number_format($reservation->total_minor / 100, 2) }} {{ $reservation->currency }}</span>
            </div>
        </div>

        <!-- Self-Service Cancellation Option -->
        @if(!in_array($reservation->status, ['checked_in', 'checked_out', 'cancelled']))
            <div class="card-custom p-4 mb-4 border-danger">
                <h5 class="fw-bold text-danger mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Cancel Reservation</h5>
                <p class="text-muted small mb-3">If your plans have changed, you may cancel your reservation below.</p>

                <form action="{{ route('booking.portal.cancel', ['confirmationNumber' => $reservation->confirmation_number]) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small">Cancellation Reason (Optional)</label>
                        <input type="text" name="reason" class="form-control" placeholder="Change of plans, emergency, etc.">
                    </div>
                    <button type="submit" class="btn btn-outline-danger px-4 rounded-pill">
                        <i class="fa-solid fa-ban me-1"></i> Cancel Reservation
                    </button>
                </form>
            </div>
        @endif

        <div class="text-center mb-5">
            <a href="{{ route('booking.portal.lookup') }}" class="btn btn-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Lookup
            </a>
        </div>
    </div>
</div>
@endsection
