@extends('layouts.guest')

@section('title', $property->name . ' — Official Luxury Direct Booking')

@section('content')
<!-- Hero Section -->
<div class="hero-banner-premium">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                <span class="hero-badge-star">
                    <i class="fa-solid fa-crown"></i> {{ $property->getHeroBadge() }}
                </span>
                <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: rgba(15, 23, 42, 0.6) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(4px);">
                    <i class="fa-solid fa-shield-cat text-warning me-1"></i> Best Rate Guaranteed
                </span>
            </div>
            <h1 class="display-4 fw-extrabold text-white mb-2 font-serif" style="letter-spacing: -0.02em;">{{ $property->name }}</h1>
            <p class="lead text-white-50 mb-2 fs-5 fw-medium">{{ $property->getTagline() }}</p>
            <p class="text-white-50 mb-4 small">
                <i class="fa-solid fa-location-dot text-warning me-2"></i>
                {{ $property->address_line1 }}, {{ $property->city }}, {{ $property->country }}
            </p>
            <div class="d-flex flex-wrap gap-4 pt-2 border-top border-white border-opacity-10">
                <span class="text-white-50 small fw-semibold"><i class="fa-solid fa-wifi text-warning me-2 fs-6"></i> High-Speed Wi-Fi</span>
                <span class="text-white-50 small fw-semibold"><i class="fa-solid fa-clock text-warning me-2 fs-6"></i> Check-In: {{ $property->getCheckInTime() }}</span>
                <span class="text-white-50 small fw-semibold"><i class="fa-solid fa-ban-smoke text-warning me-2 fs-6"></i> Non-Smoking Suites</span>
                <span class="text-white-50 small fw-semibold"><i class="fa-solid fa-bell-concierge text-warning me-2 fs-6"></i> 24/7 Concierge</span>
            </div>
        </div>
    </div>
</div>

<!-- Step Progress Bar -->
<div class="wizard-container">
    <div class="step-progress-bar">
        <div class="step-item active">
            <div class="step-number">1</div>
            <span class="step-text">Dates & Guests</span>
        </div>
        <div class="step-item">
            <div class="step-number">2</div>
            <span class="step-text">Select Room</span>
        </div>
        <div class="step-item">
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

<!-- Search Card -->
<div class="card-custom p-4 p-md-5 mb-5 shadow-lg position-relative" style="margin-top: -20px; z-index: 100; background: #ffffff; overflow: visible !important;">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div>
            <h3 class="fw-extrabold text-dark mb-1 font-serif">Find Available Rooms</h3>
            <p class="text-muted small mb-0">Select your dates to discover available luxury suites & exclusive rate plans.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-bolt text-warning me-1"></i> Live Inventory Hold
            </span>
        </div>
    </div>

    <form action="{{ route('booking.search', ['slug' => $property->slug]) }}" method="GET" id="searchForm">
        <div class="row g-3 align-items-end">
            <!-- Dual-Month Travel Dates Picker -->
            <div class="col-md-6 col-sm-12 position-relative">
                <label class="form-label fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.05em;">Select Travel Dates</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-calendar-days text-primary fs-5"></i></span>
                    <input type="text" id="dateRangeInput" class="form-control form-control-lg border-start-0 ps-0 fw-bold text-dark fs-6 cursor-pointer" placeholder="Select dates" readonly style="height: 48px; background: #ffffff;">
                    <span class="input-group-text bg-white border-start-0">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold px-3 py-2 rounded-pill" id="nightCountBadge">
                            {{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) }} {{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) == 1 ? 'Night' : 'Nights' }}
                        </span>
                    </span>
                </div>

                <!-- Hidden Input Fields for Backend Submission -->
                <input type="hidden" name="check_in" id="inputCheckIn" value="{{ $checkIn }}">
                <input type="hidden" name="check_out" id="inputCheckOut" value="{{ $checkOut }}">
            </div>

            <!-- Occupancy Popover Selector (Adults, Children, Rooms, Pets) -->
            <div class="col-md-4 col-sm-12 position-relative" style="z-index: 1050;">
                <label class="form-label fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.05em;">Select Occupancy</label>
                <button type="button" class="form-control form-control-lg d-flex align-items-center justify-content-between text-start bg-white border cursor-pointer px-3" id="occupancyBtn" onclick="toggleOccupancyPopover(event)" style="height: 48px;">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <i class="fa-regular fa-user text-muted fs-5"></i>
                        <span class="fw-bold text-dark fs-6" id="occupancySummary">
                            {{ $adults }} {{ $adults == 1 ? 'adult' : 'adults' }} &middot; {{ $children }} {{ $children == 1 ? 'child' : 'children' }} &middot; 1 room
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-muted small ms-2"></i>
                </button>

                <!-- Hidden Input Fields for Form Submission -->
                <input type="hidden" name="adults" id="inputAdults" value="{{ $adults ?? 2 }}">
                <input type="hidden" name="children" id="inputChildren" value="{{ $children ?? 0 }}">
                <input type="hidden" name="rooms" id="inputRooms" value="1">
                <input type="hidden" name="has_pets" id="inputPets" value="0">

                <!-- Occupancy Popover Dropdown Card -->
                <div class="occupancy-popover-card shadow-lg rounded-4 p-4 bg-white border position-absolute" id="occupancyPopover" style="display: none; top: 100%; left: 0; right: 0; min-width: 320px; z-index: 9999; margin-top: 8px;">
                    <!-- Adults Stepper -->
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="fw-bold text-dark fs-6">Adults</span>
                        <div class="stepper-box d-flex align-items-center gap-3 border rounded-3 px-2 py-1">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold fs-4" id="btnMinusAdults" onclick="changeOccupancy('adults', -1)">&minus;</button>
                            <span class="fw-bold text-dark fs-6 text-center" style="min-width: 24px;" id="valAdults">{{ $adults ?? 2 }}</span>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold fs-4" id="btnPlusAdults" onclick="changeOccupancy('adults', 1)">&plus;</button>
                        </div>
                    </div>

                    <!-- Children Stepper -->
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="fw-bold text-dark fs-6">Children</span>
                        <div class="stepper-box d-flex align-items-center gap-3 border rounded-3 px-2 py-1">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold fs-4" id="btnMinusChildren" onclick="changeOccupancy('children', -1)">&minus;</button>
                            <span class="fw-bold text-dark fs-6 text-center" style="min-width: 24px;" id="valChildren">{{ $children ?? 0 }}</span>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold fs-4" id="btnPlusChildren" onclick="changeOccupancy('children', 1)">&plus;</button>
                        </div>
                    </div>

                    <!-- Rooms Stepper -->
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="fw-bold text-dark fs-6">Rooms</span>
                        <div class="stepper-box d-flex align-items-center gap-3 border rounded-3 px-2 py-1">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold fs-4" id="btnMinusRooms" onclick="changeOccupancy('rooms', -1)">&minus;</button>
                            <span class="fw-bold text-dark fs-6 text-center" style="min-width: 24px;" id="valRooms">1</span>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold fs-4" id="btnPlusRooms" onclick="changeOccupancy('rooms', 1)">&plus;</button>
                        </div>
                    </div>

                    <!-- Pets Toggle -->
                    <div class="py-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-bold text-dark fs-6">Traveling with pets?</span>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input fs-4 cursor-pointer" type="checkbox" role="switch" id="switchPets" onchange="togglePets(this.checked)">
                            </div>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.76rem; line-height: 1.35;">
                            Assistance animals aren't considered pets.<br>
                            <a href="#" onclick="alert('Assistance animals are welcome at all times without additional fee.'); return false;" class="text-primary text-decoration-none fw-semibold">Read more about traveling with assistance animals</a>
                        </p>
                    </div>

                    <!-- Done Button -->
                    <div class="pt-3">
                        <button type="button" class="btn btn-outline-primary w-100 fw-bold py-2 rounded-3" onclick="closeOccupancyPopover()">Done</button>
                    </div>
                </div>
            </div>

            <!-- Search Button -->
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-brand w-100 py-3" style="height: 48px;">
                    <i class="fa-solid fa-magnifying-glass fs-6 me-1"></i> Search
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Property Overview & Amenities Grid -->
<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="card-custom p-4 p-md-5 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="p-3 bg-light rounded-4 text-warning">
                    <i class="fa-solid fa-hotel fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-extrabold text-dark mb-0 font-serif">About {{ $property->name }}</h4>
                    <small class="text-muted fw-semibold">Luxury Hotel & Executive Residences</small>
                </div>
            </div>

            <p class="text-secondary leading-relaxed mb-4" style="font-size: 0.98rem; line-height: 1.7;">
                {{ $property->description ?? 'Experience world-class hospitality, luxurious accommodations, and flawless service at our premier hotel property. Book directly with us for guaranteed lowest rates and instant confirmation.' }}
            </p>

            <h6 class="fw-bold text-dark text-uppercase mb-3 small" style="letter-spacing: 0.08em;">Signature Privileges Included</h6>
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center p-3 rounded-4 border bg-white shadow-sm h-100">
                        <div class="p-3 rounded-3 me-3" style="background: #fdf8eb; color: var(--brand-gold);">
                            <i class="fa-solid fa-headset fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">24/7 Front Desk</div>
                            <small class="text-muted">Round-the-clock reception support</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center p-3 rounded-4 border bg-white shadow-sm h-100">
                        <div class="p-3 rounded-3 me-3" style="background: #ecfdf5; color: #059669;">
                            <i class="fa-solid fa-shield-halved fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Instant Room Hold</div>
                            <small class="text-muted">15-minute guaranteed hold</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center p-3 rounded-4 border bg-white shadow-sm h-100">
                        <div class="p-3 rounded-3 me-3" style="background: #eff6ff; color: #2563eb;">
                            <i class="fa-solid fa-utensils fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $property->getRestaurantName() }}</div>
                            <small class="text-muted">Signature Oceanfront Dining</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center p-3 rounded-4 border bg-white shadow-sm h-100">
                        <div class="p-3 rounded-3 me-3" style="background: #fdf4ff; color: #c026d3;">
                            <i class="fa-solid fa-spa fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Wellness & Spa</div>
                            <small class="text-muted">Relaxation & fitness centers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-custom p-4 p-md-4 h-100 bg-white">
            <h5 class="fw-extrabold text-dark mb-4 font-serif border-bottom pb-3">
                <i class="fa-solid fa-circle-info text-warning me-2"></i> Hotel Policies
            </h5>
            <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                <li class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                    <span class="text-muted small fw-semibold"><i class="fa-solid fa-clock text-dark me-2"></i> Standard Check-In</span>
                    <span class="badge bg-dark text-white rounded-pill px-3 py-2">{{ $property->getCheckInTime() }}</span>
                </li>
                <li class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                    <span class="text-muted small fw-semibold"><i class="fa-solid fa-right-from-bracket text-dark me-2"></i> Standard Check-Out</span>
                    <span class="badge bg-dark text-white rounded-pill px-3 py-2">{{ $property->getCheckOutTime() }}</span>
                </li>
                <li class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                    <span class="text-muted small fw-semibold"><i class="fa-solid fa-coins text-dark me-2"></i> Accepted Currency</span>
                    <span class="fw-bold text-dark">{{ $property->currency }}</span>
                </li>
                <li class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                    <span class="text-muted small fw-semibold"><i class="fa-solid fa-phone text-dark me-2"></i> Concierge Hotline</span>
                    <span class="fw-bold text-dark">{{ $property->phone ?? '+254 20 000 0001' }}</span>
                </li>
            </ul>

            <div class="mt-4 p-3 rounded-4 bg-light text-center border">
                <i class="fa-solid fa-credit-card fs-4 text-warning mb-2 d-block"></i>
                <div class="fw-bold text-dark small">No Advance Payment Required</div>
                <small class="text-muted">Pay at property desk upon arrival</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let stateAdults = {{ $adults ?? 2 }};
    let stateChildren = {{ $children ?? 0 }};
    let stateRooms = 1;
    let statePets = false;

    // Initialize Dual-Month Flatpickr Range Calendar
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#dateRangeInput", {
                mode: "range",
                showMonths: 2,
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: ["{{ $checkIn }}", "{{ $checkOut }}"],
                altInput: true,
                altFormat: "D, j M Y",
                altInputClass: "form-control form-control-lg border-start-0 ps-0 fw-bold text-dark fs-6 cursor-pointer",
                monthSelectorType: "static",
                onReady: function(selectedDates, dateStr, instance) {
                    updateDateSummary(selectedDates, instance);
                },
                onChange: function(selectedDates, dateStr, instance) {
                    updateDateSummary(selectedDates, instance);
                }
            });
        }
    });

    function updateDateSummary(selectedDates, instance) {
        if (selectedDates && selectedDates.length === 2) {
            const checkIn = instance.formatDate(selectedDates[0], "Y-m-d");
            const checkOut = instance.formatDate(selectedDates[1], "Y-m-d");
            document.getElementById('inputCheckIn').value = checkIn;
            document.getElementById('inputCheckOut').value = checkOut;

            const diffTime = Math.abs(selectedDates[1] - selectedDates[0]);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const badge = document.getElementById('nightCountBadge');
            if (badge) {
                badge.textContent = diffDays + (diffDays === 1 ? ' Night' : ' Nights');
            }
        }
    }

    function toggleOccupancyPopover(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        const pop = document.getElementById('occupancyPopover');
        if (pop) {
            if (pop.style.display === 'block') {
                pop.style.display = 'none';
            } else {
                pop.style.display = 'block';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const pop = document.getElementById('occupancyPopover');
        if (pop) {
            pop.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        document.addEventListener('click', function() {
            if (pop) {
                pop.style.display = 'none';
            }
        });
    });

    function changeOccupancy(type, delta) {
        if (type === 'adults') {
            stateAdults = Math.max(1, Math.min(10, stateAdults + delta));
            document.getElementById('valAdults').textContent = stateAdults;
        } else if (type === 'children') {
            stateChildren = Math.max(0, Math.min(6, stateChildren + delta));
            document.getElementById('valChildren').textContent = stateChildren;
        } else if (type === 'rooms') {
            stateRooms = Math.max(1, Math.min(5, stateRooms + delta));
            document.getElementById('valRooms').textContent = stateRooms;
        }
        updateSummary();
    }

    function togglePets(checked) {
        statePets = checked;
        updateSummary();
    }

    function updateSummary() {
        document.getElementById('inputAdults').value = stateAdults;
        document.getElementById('inputChildren').value = stateChildren;
        document.getElementById('inputRooms').value = stateRooms;
        document.getElementById('inputPets').value = statePets ? 1 : 0;

        const adultStr = stateAdults + (stateAdults === 1 ? ' adult' : ' adults');
        const childStr = stateChildren + (stateChildren === 1 ? ' child' : ' children');
        const roomStr = stateRooms + (stateRooms === 1 ? ' room' : ' rooms');

        document.getElementById('occupancySummary').textContent = `${adultStr} \u00b7 ${childStr} \u00b7 ${roomStr}`;
    }

    function closeOccupancyPopover() {
        updateSummary();
        const pop = document.getElementById('occupancyPopover');
        if (pop) {
            pop.style.display = 'none';
        }
    }
</script>
@endpush

