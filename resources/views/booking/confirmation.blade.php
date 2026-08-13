@extends('layouts.guest')

@section('title', 'Reservation Confirmed — ' . $property->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 text-center">

        <!-- Success Badge Header -->
        <div class="mb-4 pt-2">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle p-4 shadow-lg mb-3" style="width: 100px; height: 100px; background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; box-shadow: 0 12px 30px rgba(16, 185, 129, 0.4) !important;">
                <i class="fa-solid fa-check display-4"></i>
            </div>
            <h1 class="display-5 fw-extrabold text-dark font-serif mb-2">Reservation Confirmed!</h1>
            <p class="lead text-muted fs-5">Thank you for booking with {{ $property->name }}. Your 5-star luxury stay is officially confirmed.</p>
        </div>

        <!-- Confirmation Number Card -->
        <div class="card-custom p-4 p-md-5 mb-4 bg-white border-2 border-success text-start shadow-md">
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-4 mb-4">
                <div>
                    <small class="text-muted text-uppercase fw-bold d-block mb-1" style="letter-spacing: 0.08em;">Official Confirmation Code</small>
                    <span class="display-6 fw-extrabold text-success font-monospace" style="letter-spacing: 0.05em;">{{ $reservation->confirmation_number }}</span>
                </div>
                <div class="mt-3 mt-sm-0">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-6 px-4 py-2 rounded-pill fw-extrabold">
                        <i class="fa-solid fa-circle-check me-1"></i> Confirmed & Locked
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6 border-end-md">
                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="letter-spacing: 0.05em;">Primary Guest Details</small>
                    <div class="fw-extrabold text-dark fs-5 font-serif">{{ $reservation->primaryGuest->first_name ?? '' }} {{ $reservation->primaryGuest->last_name ?? '' }}</div>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-envelope text-warning me-2"></i> {{ $reservation->primaryGuest->email ?? '' }}</small>
                    <small class="text-muted d-block"><i class="fa-solid fa-phone text-warning me-2"></i> {{ $reservation->primaryGuest->phone ?? '' }}</small>
                </div>

                <div class="col-md-6">
                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="letter-spacing: 0.05em;">Property Information</small>
                    <div class="fw-extrabold text-dark fs-5 font-serif">{{ $property->name }}</div>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-map-location-dot text-warning me-2"></i> {{ $property->address_line1 }}, {{ $property->city }}</small>
                    <small class="text-muted d-block"><i class="fa-solid fa-headset text-warning me-2"></i> {{ $property->phone ?? 'Concierge Desk' }}</small>
                </div>
            </div>
        </div>

        <!-- Stay Details Summary Card -->
        <div class="card-custom p-4 p-md-5 mb-4 text-start bg-white shadow-sm">
            <h4 class="fw-extrabold text-dark mb-4 font-serif border-bottom pb-3">
                <i class="fa-solid fa-calendar-check text-warning me-2"></i> Stay Overview & Summary
            </h4>

            <div class="row g-3 mb-4 p-4 bg-light rounded-4 border">
                <div class="col-sm-6 border-end-sm">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1" style="letter-spacing: 0.05em;">Check-In</small>
                    <div class="fw-extrabold text-dark fs-4 font-serif">{{ \Carbon\Carbon::parse($reservation->check_in)->format('M d, Y') }}</div>
                    <small class="text-muted fw-semibold">From {{ $property->getCheckInTime() }}</small>
                </div>

                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1" style="letter-spacing: 0.05em;">Check-Out</small>
                    <div class="fw-extrabold text-dark fs-4 font-serif">{{ \Carbon\Carbon::parse($reservation->check_out)->format('M d, Y') }}</div>
                    <small class="text-muted fw-semibold">Until {{ $property->getCheckOutTime() }}</small>
                </div>
            </div>

            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Stay Duration</span>
                <span class="fw-bold text-dark">{{ $reservation->nights }} {{ $reservation->nights == 1 ? 'Night' : 'Nights' }}</span>
            </div>

            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Guest Count</span>
                <span class="fw-bold text-dark">{{ $reservation->adults }} Adults @if($reservation->children > 0), {{ $reservation->children }} Children @endif</span>
            </div>

            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Total Charges (Incl. Taxes & Fees)</span>
                <span class="fw-bold text-dark fs-5 font-serif">{{ number_format($reservation->total_minor / 100, 2) }} {{ $reservation->currency }}</span>
            </div>

            @if($reservation->balance_minor == 0)
            <div class="d-flex justify-content-between py-3 align-items-center border-bottom bg-light p-3 rounded-4 mt-3">
                <div>
                    <span class="fw-bold text-dark fs-5 font-serif">Payment Status</span>
                    <small class="text-success d-block fw-semibold"><i class="fa-solid fa-credit-card me-1"></i> Paid in Full via Stripe (Credit Card)</small>
                </div>
                <span class="badge bg-success text-white fs-6 px-4 py-2 rounded-pill fw-bold shadow-sm">
                    <i class="fa-solid fa-circle-check me-1"></i> Paid in Full
                </span>
            </div>
            @else
            <div class="d-flex justify-content-between py-3 align-items-center">
                <span class="fw-bold text-dark fs-5 font-serif">Balance Due at Front Desk</span>
                <span class="price-tag fs-3 text-success">{{ number_format($reservation->balance_minor / 100, 2) }} <small class="fs-6 text-muted">{{ $reservation->currency }}</small></span>
            </div>
            @endif
        </div>

        <!-- Action Links -->
        <div class="d-flex flex-wrap gap-3 justify-content-center mb-5">
            <button onclick="window.print()" class="btn btn-outline-custom px-4">
                <i class="fa-solid fa-print me-2"></i> Print Confirmation Receipt
            </button>
            <a href="{{ route('booking.portal.show', ['confirmationNumber' => $reservation->confirmation_number]) }}" class="btn btn-brand px-4">
                <i class="fa-solid fa-circle-user me-2"></i> Manage in Guest Portal
            </a>
            <a href="{{ route('booking.index', ['slug' => $property->slug]) }}" class="btn btn-light border rounded-pill px-4 fw-bold">
                Make Another Reservation
            </a>
        </div>

    </div>
</div>
@endsection

