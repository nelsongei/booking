@extends('layouts.guest')

@section('title', 'Enhance Your Stay — ' . $property->name)

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
        <div class="step-item active">
            <div class="step-number">3</div>
            <span class="step-text">Add-ons</span>
        </div>
        <div class="step-item">
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
                    <i class="fa-solid fa-wand-magic-sparkles fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0 font-serif">Personalize Your Stay</h3>
                    <p class="text-muted small mb-0">Select optional luxury experiences and VIP services for your visit.</p>
                </div>
            </div>

            <form action="{{ route('booking.guest-details', ['slug' => $property->slug]) }}" method="POST">
                @csrf
                <input type="hidden" name="check_in" value="{{ $checkIn }}">
                <input type="hidden" name="check_out" value="{{ $checkOut }}">
                <input type="hidden" name="adults" value="{{ $adults }}">
                <input type="hidden" name="children" value="{{ $children }}">
                <input type="hidden" name="rooms" value="{{ $rooms ?? 1 }}">
                <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                <input type="hidden" name="rate_plan_id" value="{{ $ratePlan->id }}">

                <!-- Add-on Items Grid -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="border rounded-4 p-4 d-flex align-items-center justify-content-between bg-white shadow-sm transition-all hover-shadow">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check me-2">
                                <input class="form-check-input" type="checkbox" style="width: 22px; height: 22px;" name="addons[]" value="breakfast" id="addon_breakfast">
                            </div>
                            <div class="p-3 rounded-4 me-2" style="background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-utensils fs-3"></i>
                            </div>
                            <div>
                                <label for="addon_breakfast" class="fw-bold text-dark mb-1 form-check-label fs-6 cursor-pointer">Daily Artisan Buffet Breakfast</label>
                                <div class="text-muted small">Freshly prepared gourmet breakfast buffet every morning for all registered guests.</div>
                            </div>
                        </div>
                        <div class="fw-extrabold text-dark ms-3 text-end" style="min-width: 110px;">
                            + 25.00 <small class="text-muted d-block fw-normal">{{ $pricing['currency'] }}/night</small>
                        </div>
                    </div>

                    <div class="border rounded-4 p-4 d-flex align-items-center justify-content-between bg-white shadow-sm transition-all hover-shadow">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check me-2">
                                <input class="form-check-input" type="checkbox" style="width: 22px; height: 22px;" name="addons[]" value="airport_shuttle" id="addon_shuttle">
                            </div>
                            <div class="p-3 rounded-4 me-2" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-car fs-3"></i>
                            </div>
                            <div>
                                <label for="addon_shuttle" class="fw-bold text-dark mb-1 form-check-label fs-6 cursor-pointer">Private Airport Chauffeur Transfer</label>
                                <div class="text-muted small">Private luxury vehicle transfer service to/from airport terminal.</div>
                            </div>
                        </div>
                        <div class="fw-extrabold text-dark ms-3 text-end" style="min-width: 110px;">
                            + 45.00 <small class="text-muted d-block fw-normal">{{ $pricing['currency'] }}</small>
                        </div>
                    </div>

                    <div class="border rounded-4 p-4 d-flex align-items-center justify-content-between bg-white shadow-sm transition-all hover-shadow">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check me-2">
                                <input class="form-check-input" type="checkbox" style="width: 22px; height: 22px;" name="addons[]" value="late_checkout" id="addon_checkout">
                            </div>
                            <div class="p-3 rounded-4 me-2" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fa-solid fa-clock-three fs-3"></i>
                            </div>
                            <div>
                                <label for="addon_checkout" class="fw-bold text-dark mb-1 form-check-label fs-6 cursor-pointer">Guaranteed Late Check-Out (2:00 PM)</label>
                                <div class="text-muted small">Enjoy extra leisure time in your suite on check-out day until 2:00 PM.</div>
                            </div>
                        </div>
                        <div class="fw-extrabold text-dark ms-3 text-end" style="min-width: 110px;">
                            + 30.00 <small class="text-muted d-block fw-normal">{{ $pricing['currency'] }}</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                    <a href="{{ route('booking.search', ['slug' => $property->slug, 'check_in' => $checkIn, 'check_out' => $checkOut, 'adults' => $adults, 'children' => $children]) }}" class="btn btn-outline-custom">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Rooms
                    </a>
                    <button type="submit" class="btn btn-brand">
                        Continue to Guest Details <i class="fa-solid fa-arrow-right ms-1"></i>
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
                <span class="text-muted small">Suite Selection</span>
                <span class="fw-bold text-dark">{{ $roomType->name }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Rate Privilege</span>
                <span class="fw-bold text-dark">{{ $ratePlan->name }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Check-In / Out</span>
                <span class="fw-semibold text-dark">{{ $checkIn }} &rarr; {{ $checkOut }}</span>
            </div>
            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Stay Duration</span>
                <span class="fw-semibold text-dark">{{ $pricing['nights'] }} {{ $pricing['nights'] == 1 ? 'Night' : 'Nights' }}</span>
            </div>
            <div class="d-flex justify-content-between py-3">
                <span class="fw-bold text-dark fs-5 font-serif">Estimated Total</span>
                <span class="price-tag fs-3 text-dark">{{ number_format($pricing['total_minor'] / 100, 2) }} <small class="fs-6 text-muted">{{ $pricing['currency'] }}</small></span>
            </div>
        </div>
    </div>
</div>
@endsection

