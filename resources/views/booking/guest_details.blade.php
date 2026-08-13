@extends('layouts.guest')

@section('title', 'Guest Details — ' . $property->name)

@section('content')
<!-- Step Progress Bar -->
<div class="wizard-container">
    <div class="step-progress-bar">
        <div class="step-item completed">
            <div class="step-number"><i class="fa-solid fa-check"></i></div>
            <span class="step-text">Dates & Guests</span>
        </div>
        <div class="step-item completed">
            <div class="step-number"><i class="fa-solid fa-check"></i></div>
            <span class="step-text">Select Room</span>
        </div>
        <div class="step-item completed">
            <div class="step-number"><i class="fa-solid fa-check"></i></div>
            <span class="step-text">Add-ons</span>
        </div>
        <div class="step-item active">
            <div class="step-number">4</div>
            <span class="step-text">Guest Details</span>
        </div>
        <div class="step-item">
            <div class="step-number">5</div>
            <span class="step-text">Review & Hold</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-custom p-4 p-md-5 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="p-3 bg-light rounded-4 text-warning">
                    <i class="fa-solid fa-user-check fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0 font-serif">Primary Guest Registration</h3>
                    <p class="text-muted small mb-0">Please enter the contact details for this luxury reservation. Your confirmation statement will be delivered to this email.</p>
                </div>
            </div>

            <form action="{{ route('booking.review', ['slug' => $property->slug]) }}" method="POST">
                @csrf
                <input type="hidden" name="check_in" value="{{ $checkIn }}">
                <input type="hidden" name="check_out" value="{{ $checkOut }}">
                <input type="hidden" name="adults" value="{{ $adults }}">
                <input type="hidden" name="children" value="{{ $children }}">
                <input type="hidden" name="rooms" value="{{ $rooms ?? 1 }}">
                <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                <input type="hidden" name="rate_plan_id" value="{{ $ratePlan->id }}">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="guest_first_name" class="form-control form-control-lg" placeholder="e.g. Alexander" value="{{ old('guest_first_name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="guest_last_name" class="form-control form-control-lg" placeholder="e.g. Vance" value="{{ old('guest_last_name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" name="guest_email" class="form-control form-control-lg border-start-0" placeholder="alexander.vance@enterprise.com" value="{{ old('guest_email') }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="tel" name="guest_phone" class="form-control form-control-lg border-start-0" placeholder="+254 700 000 000" value="{{ old('guest_phone') }}" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">Special Requests & Concierge Notes</label>
                        <textarea name="special_requests" class="form-control" rows="3" placeholder="High floor suite, quiet room, late check-in preference, airport transfer details... (Subject to hotel availability)">{{ old('special_requests') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                    <a href="{{ route('booking.search', ['slug' => $property->slug, 'check_in' => $checkIn, 'check_out' => $checkOut, 'adults' => $adults, 'children' => $children]) }}" class="btn btn-outline-custom">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-brand">
                        Proceed to Review & Hold <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Booking Summary Sidebar -->
    <div class="col-lg-4">
        <div class="card-custom p-4 sticky-top bg-white" style="top: 100px;">
            <h5 class="fw-extrabold text-dark mb-4 font-serif border-bottom pb-3">Reservation Summary</h5>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Property</span>
                <span class="fw-bold text-dark">{{ $property->name }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Suite Type</span>
                <span class="fw-bold text-dark">{{ $roomType->name }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Rate Plan</span>
                <span class="fw-bold text-dark">{{ $ratePlan->name }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Check-in</span>
                <span class="fw-semibold text-dark">{{ $checkIn }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Check-out</span>
                <span class="fw-semibold text-dark">{{ $checkOut }}</span>
            </div>
            <div class="d-flex justify-content-between py-3">
                <span class="fw-bold text-dark fs-5 font-serif">Grand Total</span>
                <span class="price-tag fs-3 text-dark">{{ number_format($pricing['total_minor'] / 100, 2) }} <small class="fs-6 text-muted">{{ $pricing['currency'] }}</small></span>
            </div>
        </div>
    </div>
</div>
@endsection

