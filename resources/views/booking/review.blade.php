@extends('layouts.guest')

@section('title', 'Review & Lock — ' . $property->name)

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
        <div class="step-item completed">
            <div class="step-number"><i class="fa-solid fa-check"></i></div>
            <span class="step-text">Guest Details</span>
        </div>
        <div class="step-item active">
            <div class="step-number">5</div>
            <span class="step-text">Review & Hold</span>
        </div>
    </div>
</div>

<!-- 15-Minute Inventory Hold Live Timer Notice -->
<div class="card-custom p-4 mb-4 bg-white border-2 border-danger shadow-sm d-flex flex-wrap justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-danger text-white rounded-4">
            <i class="fa-solid fa-lock fs-3"></i>
        </div>
        <div>
            <div class="fw-extrabold text-dark fs-5 font-serif">Inventory Locked for You</div>
            <div class="text-muted small">We've locked this suite inventory exclusively for you while you complete your booking.</div>
        </div>
    </div>

    <div class="mt-3 mt-sm-0">
        <div class="timer-badge fs-5" id="holdCountdown">
            <i class="fa-solid fa-stopwatch"></i> <span id="timerDisplay">14:59</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Final Review Card -->
    <div class="col-lg-8">
        <div class="card-custom p-4 p-md-5 mb-4">
            <h3 class="fw-extrabold text-dark mb-4 font-serif pb-3 border-bottom">
                <i class="fa-solid fa-file-contract text-warning me-2"></i> Review Reservation
            </h3>

            <!-- Guest & Stay Information -->
            <div class="row g-3 mb-4 p-4 bg-light rounded-4 border">
                <div class="col-md-6 border-end-md">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1 style-letter-spacing" style="letter-spacing:0.05em;">Primary Guest</small>
                    <div class="fw-bold text-dark fs-5 font-serif">{{ $guestData['first_name'] }} {{ $guestData['last_name'] }}</div>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-envelope text-warning me-1"></i> {{ $guestData['email'] }}</small>
                    <small class="text-muted d-block"><i class="fa-solid fa-phone text-warning me-1"></i> {{ $guestData['phone'] }}</small>
                </div>

                <div class="col-md-6">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1 style-letter-spacing" style="letter-spacing:0.05em;">Dates & Occupancy</small>
                    <div class="fw-bold text-dark fs-5 font-serif">{{ $checkIn }} &rarr; {{ $checkOut }}</div>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-moon text-warning me-1"></i> {{ $pricing['nights'] }} {{ $pricing['nights'] == 1 ? 'Night' : 'Nights' }}</small>
                    <small class="text-muted d-block"><i class="fa-solid fa-users text-warning me-1"></i> {{ $adults }} {{ $adults == 1 ? 'Adult' : 'Adults' }} @if($children > 0), {{ $children }} Children @endif</small>
                </div>
            </div>

            <!-- Room & Rate Plan Card -->
            <div class="border rounded-4 p-4 mb-4 bg-white shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 mb-2 fw-bold">Selected Suite</span>
                        <h4 class="fw-extrabold text-dark mb-1 font-serif">{{ $roomType->name }}</h4>
                        <small class="text-muted">{{ $ratePlan->name }} — {{ $ratePlan->description ?? 'Standard Rate Plan' }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-extrabold text-dark fs-5 font-serif">{{ number_format($pricing['subtotal_minor'] / 100, 2) }} {{ $pricing['currency'] }}</div>
                        <small class="text-muted">Subtotal</small>
                    </div>
                </div>
            </div>

            <!-- Special Requests if present -->
            @if(!empty($guestData['special_requests']))
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-comment-dots text-warning me-1"></i> Special Concierge Requests</h6>
                    <div class="p-3 bg-light rounded-4 text-secondary small border">{{ $guestData['special_requests'] }}</div>
                </div>
            @endif

            <!-- Payment Options Selection -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-credit-card text-primary me-2"></i> Select Payment Preference</h6>

                <div class="row g-3">
                    <!-- Stripe Online Card Option -->
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 bg-white cursor-pointer transition-all border-2 payment-option-box active-payment border-primary" id="boxStripe" onclick="setPaymentMode('stripe')" style="background: #f8fafc;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payMethodStripe" value="stripe" checked onclick="setPaymentMode('stripe')">
                                    <label class="form-check-label fw-bold text-dark ms-1 cursor-pointer" for="payMethodStripe">
                                        Credit / Debit Card
                                    </label>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 small fw-bold">Stripe</span>
                            </div>
                            <small class="text-muted d-block mb-2">Pay securely online & receive instant receipt confirmation.</small>
                            <div class="d-flex gap-2 text-muted fs-5">
                                <i class="fa-brands fa-cc-visa text-primary"></i>
                                <i class="fa-brands fa-cc-mastercard text-danger"></i>
                                <i class="fa-brands fa-cc-amex text-info"></i>
                                <i class="fa-brands fa-cc-discover text-warning"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pay at Property Option -->
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 bg-white cursor-pointer transition-all border-2 payment-option-box" id="boxDesk" onclick="setPaymentMode('pay_at_desk')">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payMethodDesk" value="pay_at_desk" onclick="setPaymentMode('pay_at_desk')">
                                    <label class="form-check-label fw-bold text-dark ms-1 cursor-pointer" for="payMethodDesk">
                                        Pay Upon Arrival
                                    </label>
                                </div>
                                <span class="badge bg-light text-dark border px-2.5 py-1 small fw-bold">At Hotel</span>
                            </div>
                            <small class="text-muted d-block mb-2">Hold room with 15-min guarantee, pay cash/card at front desk.</small>
                            <small class="text-success fw-semibold"><i class="fa-solid fa-hotel me-1"></i> Pay at Check-in Desk</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stripe Card Form Fields Container -->
            <div class="card-custom p-4 mb-4 border bg-white shadow-sm rounded-4" id="stripeCardFormBox">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-success fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0">Stripe Encrypted Payment Details</h6>
                    </div>
                    <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small"><i class="fa-solid fa-lock text-success me-1"></i> 256-Bit SSL Encrypted</span>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark small">Name on Card <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" id="stripeCardHolder" class="form-control form-control-lg" placeholder="e.g. {{ $guestData['first_name'] }} {{ $guestData['last_name'] }}" value="{{ $guestData['first_name'] }} {{ $guestData['last_name'] }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark small">Card Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-credit-card text-muted" id="cardTypeIcon"></i></span>
                            <input type="text" id="stripeCardNumInput" class="form-control form-control-lg" placeholder="4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242" value="4242 4242 4242 4242" maxlength="19" oninput="handleCardFormat(this)">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-circle-check text-success"></i></span>
                        </div>
                    </div>

                    <div class="col-md-6 col-6">
                        <label class="form-label fw-semibold text-dark small">Expiration Date <span class="text-danger">*</span></label>
                        <input type="text" id="stripeCardExpInput" class="form-control form-control-lg" placeholder="MM / YY" value="12/28" maxlength="5">
                    </div>

                    <div class="col-md-6 col-6">
                        <label class="form-label fw-semibold text-dark small">CVC / Security Code <span class="text-danger">*</span></label>
                        <input type="password" id="stripeCardCvcInput" class="form-control form-control-lg" placeholder="123" value="123" maxlength="4">
                    </div>
                </div>

                <!-- Hidden inputs passed to confirm route -->
                <input type="hidden" name="stripe_payment_id" id="inputStripePaymentId" value="ch_stripe_demo123">
                <input type="hidden" name="card_last4" id="inputCardLast4" value="4242">
                <input type="hidden" name="card_brand" id="inputCardBrand" value="visa">
            </div>

            <!-- Terms & Confirmation Form -->
            <form action="{{ route('booking.confirm', ['slug' => $property->slug]) }}" method="POST" id="confirmBookingForm">
                @csrf
                <input type="hidden" name="check_in" value="{{ $checkIn }}">
                <input type="hidden" name="check_out" value="{{ $checkOut }}">
                <input type="hidden" name="adults" value="{{ $adults }}">
                <input type="hidden" name="children" value="{{ $children }}">
                <input type="hidden" name="rooms" value="{{ $rooms ?? 1 }}">
                <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                <input type="hidden" name="rate_plan_id" value="{{ $ratePlan->id }}">
                <input type="hidden" name="guest_first_name" value="{{ $guestData['first_name'] }}">
                <input type="hidden" name="guest_last_name" value="{{ $guestData['last_name'] }}">
                <input type="hidden" name="guest_email" value="{{ $guestData['email'] }}">
                <input type="hidden" name="guest_phone" value="{{ $guestData['phone'] }}">
                <input type="hidden" name="special_requests" value="{{ $guestData['special_requests'] ?? '' }}">
                <input type="hidden" name="hold_ulid" value="{{ $hold->ulid ?? '' }}">
                <input type="hidden" name="payment_method" id="formPaymentMethod" value="stripe">
                <input type="hidden" name="stripe_payment_id" id="formStripePaymentId" value="ch_stripe_demo123">
                <input type="hidden" name="card_last4" id="formCardLast4" value="4242">
                <input type="hidden" name="card_brand" id="formCardBrand" value="visa">

                <div class="form-check mb-4 p-3 bg-light rounded-4 border">
                    <input class="form-check-input ms-0 me-2" type="checkbox" id="termsCheck" required style="width: 20px; height: 20px;">
                    <label class="form-check-label small text-dark cursor-pointer fw-semibold" for="termsCheck">
                        I agree to the official hotel policies, standard check-in rules (Check-in from {{ $property->getCheckInTime() }}), and direct booking terms of service.
                    </label>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('booking.search', ['slug' => $property->slug, 'check_in' => $checkIn, 'check_out' => $checkOut, 'adults' => $adults, 'children' => $children]) }}" class="btn btn-outline-custom">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-brand btn-lg px-5 py-3 shadow" id="btnSubmitPayment">
                        <i class="fa-solid fa-lock me-2"></i> Pay {{ number_format($pricing['total_minor'] / 100, 2) }} {{ $pricing['currency'] }} & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pricing Summary Sidebar -->
    <div class="col-lg-4">
        <div class="card-custom p-4 sticky-top bg-white" style="top: 100px;">
            <h5 class="fw-extrabold text-dark mb-4 font-serif border-bottom pb-3">Price Breakdown</h5>

            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Suite Subtotal</span>
                <span class="fw-semibold text-dark">{{ number_format($pricing['subtotal_minor'] / 100, 2) }} {{ $pricing['currency'] }}</span>
            </div>

            <div class="d-flex justify-content-between py-2.5 border-bottom">
                <span class="text-muted small">Taxes & Fees</span>
                <span class="fw-semibold text-dark">{{ number_format($pricing['tax_total_minor'] / 100, 2) }} {{ $pricing['currency'] }}</span>
            </div>

            <div class="d-flex justify-content-between py-3">
                <span class="fw-bold text-dark fs-5 font-serif">Total Due</span>
                <span class="price-tag fs-3 text-dark">{{ number_format($pricing['total_minor'] / 100, 2) }} <small class="fs-6 text-muted">{{ $pricing['currency'] }}</small></span>
            </div>

            <div class="alert alert-success rounded-4 small mb-0 border-0" style="background: #d1fae5; color: #065f46;">
                <i class="fa-solid fa-shield-check me-2 fs-6"></i> <strong>Zero Payment Due Today.</strong> Pay upon check-in at property front desk.
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function setPaymentMode(mode) {
        const boxStripe = document.getElementById('boxStripe');
        const boxDesk = document.getElementById('boxDesk');
        const radioStripe = document.getElementById('payMethodStripe');
        const radioDesk = document.getElementById('payMethodDesk');
        const cardBox = document.getElementById('stripeCardFormBox');
        const formMode = document.getElementById('formPaymentMethod');
        const btnSubmit = document.getElementById('btnSubmitPayment');

        if (mode === 'stripe') {
            boxStripe.classList.add('border-primary', 'active-payment');
            boxStripe.style.background = "#f8fafc";
            boxDesk.classList.remove('border-primary', 'active-payment');
            boxDesk.style.background = "#ffffff";
            radioStripe.checked = true;
            cardBox.style.display = "block";
            formMode.value = "stripe";
            btnSubmit.innerHTML = `<i class="fa-solid fa-lock me-2"></i> Pay {{ number_format($pricing['total_minor'] / 100, 2) }} {{ $pricing['currency'] }} & Complete`;
        } else {
            boxDesk.classList.add('border-primary', 'active-payment');
            boxDesk.style.background = "#f8fafc";
            boxStripe.classList.remove('border-primary', 'active-payment');
            boxStripe.style.background = "#ffffff";
            radioDesk.checked = true;
            cardBox.style.display = "none";
            formMode.value = "pay_at_desk";
            btnSubmit.innerHTML = `<i class="fa-solid fa-check-circle me-2"></i> Confirm Reservation (Pay at Hotel)`;
        }
    }

    function handleCardFormat(input) {
        let value = input.value.replace(/\D/g, '');
        let formatted = value.match(/.{1,4}/g)?.join(' ') || '';
        input.value = formatted.substring(0, 19);

        let last4 = value.length >= 4 ? value.slice(-4) : '4242';
        document.getElementById('formCardLast4').value = last4;

        const icon = document.getElementById('cardTypeIcon');
        if (value.startsWith('4')) {
            icon.className = "fa-brands fa-cc-visa text-primary fs-5";
            document.getElementById('formCardBrand').value = 'visa';
        } else if (value.startsWith('5')) {
            icon.className = "fa-brands fa-cc-mastercard text-danger fs-5";
            document.getElementById('formCardBrand').value = 'mastercard';
        } else if (value.startsWith('3')) {
            icon.className = "fa-brands fa-cc-amex text-info fs-5";
            document.getElementById('formCardBrand').value = 'amex';
        } else {
            icon.className = "fa-solid fa-credit-card text-muted";
            document.getElementById('formCardBrand').value = 'visa';
        }
    }

    // 15-Minute Live Hold Countdown Timer JS
    document.addEventListener('DOMContentLoaded', function () {
        var expiresAt = new Date("{{ $hold->expires_at?->toIso8601String() ?? '' }}").getTime();
        var timerDisplay = document.getElementById('timerDisplay');

        var countdownInterval = setInterval(function () {
            var now = new Date().getTime();
            var distance = expiresAt - now;

            if (distance < 0) {
                clearInterval(countdownInterval);
                timerDisplay.innerHTML = "EXPIRED";
                alert("Your 15-minute room hold has expired. Please restart your booking search.");
                window.location.href = "{{ route('booking.search', ['slug' => $property->slug]) }}";
                return;
            }

            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            timerDisplay.innerHTML = minutes + ":" + seconds;
        }, 1000);
    });
</script>
@endsection

