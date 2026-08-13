@extends('layouts.guest')

@section('title', 'Select Your Suite — ' . $property->name)

@section('content')
<!-- Step Progress Bar -->
<div class="wizard-container">
    <div class="step-progress-bar">
        <div class="step-item completed">
            <div class="step-number"><i class="fa-solid fa-check"></i></div>
            <span class="step-text">Dates & Guests</span>
        </div>
        <div class="step-item active">
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

<!-- Search Parameters Summary Header -->
<div class="card-custom p-3 p-md-4 mb-4 d-flex flex-wrap justify-content-between align-items-center bg-white shadow-sm border">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-light rounded-4 text-warning">
            <i class="fa-solid fa-calendar-days fs-4"></i>
        </div>
        <div>
            <div class="fw-extrabold text-dark fs-5 font-serif">
                {{ \Carbon\Carbon::parse($checkIn)->format('M d, Y') }} &rarr; {{ \Carbon\Carbon::parse($checkOut)->format('M d, Y') }}
                <span class="badge bg-dark rounded-pill ms-2 fw-semibold fs-6 px-3">
                    {{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) }} {{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) == 1 ? 'Night' : 'Nights' }}
                </span>
            </div>
            <small class="text-muted fw-semibold">
                <i class="fa-solid fa-user me-1 text-warning"></i> {{ $adults }} {{ $adults == 1 ? 'Adult' : 'Adults' }}
                @if($children > 0), {{ $children }} {{ $children == 1 ? 'Child' : 'Children' }} @endif
                <span class="mx-2">&bull;</span>
                <i class="fa-solid fa-hotel me-1 text-warning"></i> {{ $rooms ?? 1 }} {{ ($rooms ?? 1) == 1 ? 'Room Requested' : 'Rooms Requested' }}
                <span class="mx-2">&bull;</span>
                <i class="fa-solid fa-layer-group me-1 text-warning"></i> {{ count($availableRoomTypes) }} Room Categories Available
            </small>
        </div>
    </div>
    <a href="{{ route('booking.index', ['slug' => $property->slug, 'check_in' => $checkIn, 'check_out' => $checkOut, 'adults' => $adults, 'children' => $children, 'rooms' => $rooms ?? 1]) }}" class="nav-pill-btn mt-2 mt-sm-0">
        <i class="fa-solid fa-pen me-1"></i> Modify Search
    </a>
</div>

<!-- Available Room Types List -->
@if(empty($availableRoomTypes))
    <div class="card-custom p-5 text-center my-4 bg-white">
        <i class="fa-solid fa-bed text-muted display-3 mb-3"></i>
        <h3 class="fw-extrabold text-dark font-serif">No Suites Available</h3>
        <p class="text-muted mb-4 max-w-md mx-auto">We couldn't find available room inventory for your selected dates and guest count. Please adjust your travel dates.</p>
        <a href="{{ route('booking.index', ['slug' => $property->slug]) }}" class="btn btn-brand">Try Different Dates</a>
    </div>
@else
    @if(($rooms ?? 1) > 1)
    <!-- Multi-Room Customization Header Bar -->
    <div class="card-custom p-4 mb-4 bg-white shadow-sm border rounded-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h5 class="fw-extrabold text-dark mb-1 font-serif">
                    <i class="fa-solid fa-layer-group text-warning me-2"></i> Customize {{ $rooms }} Requested Rooms
                </h5>
                <p class="text-muted small mb-0">Select your preferred suite and rate plan for each room slot below.</p>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold" id="multiRoomStatusBadge">
                Configuring Room 1 of {{ $rooms }}
            </span>
        </div>
        
        <div class="d-flex flex-wrap gap-2 pt-3 border-top" id="roomSlotsBar">
            @for($r = 1; $r <= $rooms; $r++)
            <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-bold room-slot-btn {{ $r == 1 ? 'active bg-dark text-white' : '' }}" id="roomTab_{{ $r }}" onclick="selectRoomSlot({{ $r }})">
                <i class="fa-solid fa-bed me-1"></i> Room {{ $r }}: <span id="roomTabLabel_{{ $r }}" class="fw-normal text-muted">Click to select...</span>
            </button>
            @endfor
        </div>
    </div>
    @endif

    <div class="d-flex flex-column gap-4 mb-5">
        @foreach($availableRoomTypes as $index => $item)
            @php
                $rt = $item['room_type'];
                $minAvail = $item['min_available'];
                $plans = $item['rate_plans'];
                $roomImage = $rt->getImageUrl();
            @endphp
            <div class="card-custom overflow-hidden shadow-md">
                <div class="row g-0">
                    <!-- Room Thumbnail & Photo -->
                    <div class="col-lg-5 position-relative bg-dark" style="min-height: 280px; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.6) 100%), url('{{ $roomImage }}') center/cover no-repeat;">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-emerald-600 bg-success text-white rounded-pill px-3 py-2 fw-semibold shadow-sm">
                            <i class="fa-solid fa-check me-1"></i> {{ $minAvail }} {{ $minAvail == 1 ? 'Suite' : 'Suites' }} Available
                        </span>
                        @if($index == 0)
                        <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-crown me-1"></i> Popular Choice
                        </span>
                        @endif

                        <div class="position-absolute bottom-0 start-0 p-4 text-white">
                            <span class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-25 rounded-pill px-3 py-1 mb-2 small fw-bold">{{ $rt->code }}</span>
                            <h3 class="fw-extrabold mb-1 font-serif text-white">{{ $rt->name }}</h3>
                            <small class="text-white-50"><i class="fa-solid fa-users me-1 text-warning"></i> Up to {{ $rt->max_occupancy }} Guests</small>
                        </div>
                    </div>

                    <!-- Room Details & Rate Plans -->
                    <div class="col-lg-7 p-4 p-md-4 d-flex flex-column justify-content-between bg-white">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="fw-extrabold text-dark mb-0 font-serif">{{ $rt->name }}</h4>
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small fw-semibold">
                                    <i class="fa-solid fa-ruler-combined text-warning me-1"></i> 45 m² / 485 ft²
                                </span>
                            </div>
                            <p class="text-muted small mb-3 leading-relaxed">{{ $rt->description ?? 'Luxuriously appointed suite featuring plush bedding, marble bath, and signature hotel amenities.' }}</p>

                            <!-- Room Amenities Badges -->
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="amenity-chip"><i class="fa-solid fa-wifi text-warning"></i> Free High-Speed Wi-Fi</span>
                                <span class="amenity-chip"><i class="fa-solid fa-snowflake text-info"></i> Climate Control</span>
                                <span class="amenity-chip"><i class="fa-solid fa-tv text-secondary"></i> 55" Smart TV</span>
                                <span class="amenity-chip"><i class="fa-solid fa-bath text-primary"></i> Marble Bath & Shower</span>
                                <span class="amenity-chip"><i class="fa-solid fa-mug-hot text-amber"></i> Espresso Machine</span>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- Rate Options -->
                        <div>
                            <h6 class="fw-bold text-dark text-uppercase small mb-3" style="letter-spacing: 0.05em;"><i class="fa-solid fa-tags text-warning me-2"></i> Select Rate Plan</h6>

                            <div class="d-flex flex-column gap-3">
                                @foreach($plans as $planItem)
                                    @php
                                        $rp = $planItem['rate_plan'];
                                        $q = $planItem['quote'];
                                        $priceSingle = $q['total_minor_single'] ?? $q['total_minor'];
                                    @endphp
                                    <div class="border rounded-4 p-3.5 p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center bg-light transition-all hover-shadow">
                                        <div class="mb-2 mb-sm-0">
                                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                {{ $rp->name }}
                                                @if($rp->is_default)
                                                    <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small">Best Value</span>
                                                @endif
                                            </div>
                                            <small class="text-muted d-block">{{ $rp->description ?? 'Standard direct flexible booking rate.' }}</small>
                                            <div class="text-success small fw-semibold mt-1">
                                                <i class="fa-solid fa-shield-check me-1"></i> Includes taxes & fees ({{ number_format($q['tax_total_minor'] / 100, 2) }} {{ $q['currency'] }})
                                            </div>
                                        </div>

                                        <div class="text-sm-end ms-sm-3 border-top border-sm-0 pt-2 pt-sm-0">
                                            <div class="price-tag mb-0">
                                                {{ number_format($priceSingle / 100, 2) }} <span class="fs-6 fw-bold text-muted">{{ $q['currency'] }}</span>
                                            </div>
                                            <small class="text-muted d-block mb-2">Per suite / {{ $q['nights'] }} {{ $q['nights'] == 1 ? 'night' : 'nights' }}</small>

                                            @if(($rooms ?? 1) > 1)
                                            <button type="button" class="btn btn-brand btn-sm px-4 py-2 btn-assign-room" onclick="assignSuiteToSlot('{{ $rt->id }}', '{{ addslashes($rt->name) }}', '{{ $rp->id }}', '{{ addslashes($rp->name) }}', {{ $priceSingle }}, '{{ $q['currency'] }}')">
                                                <i class="fa-solid fa-plus me-1"></i> Select for Room 1
                                            </button>
                                            @else
                                            <form action="{{ route('booking.addons', ['slug' => $property->slug]) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="check_in" value="{{ $checkIn }}">
                                                <input type="hidden" name="check_out" value="{{ $checkOut }}">
                                                <input type="hidden" name="adults" value="{{ $adults }}">
                                                <input type="hidden" name="children" value="{{ $children }}">
                                                <input type="hidden" name="rooms" value="1">
                                                <input type="hidden" name="room_type_id" value="{{ $rt->id }}">
                                                <input type="hidden" name="rate_plan_id" value="{{ $rp->id }}">

                                                <button type="submit" class="btn btn-brand btn-sm px-4 py-2">
                                                    Select Suite <i class="fa-solid fa-arrow-right ms-1"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if(($rooms ?? 1) > 1)
<!-- Sticky Bottom Multi-Room Summary Bar -->
<div class="fixed-bottom p-3 text-white border-top shadow-lg" id="stickyMultiRoomBar" style="display: none; z-index: 10000; background: rgba(15, 23, 42, 0.95) !important; backdrop-filter: blur(12px);">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 bg-warning text-dark rounded-circle fs-4 fw-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 font-serif text-white">All {{ $rooms }} Suites Configured!</div>
                <small class="text-white-50" id="multiRoomSelectionSummaryText">Review your customized multi-room selection and proceed to add-ons.</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <div class="text-end">
                <small class="text-white-50 d-block">Combined Stay Total</small>
                <span class="fs-4 fw-extrabold text-warning" id="multiRoomTotalDisplay">0.00 KES</span>
            </div>

            <form action="{{ route('booking.addons', ['slug' => $property->slug]) }}" method="POST">
                @csrf
                <input type="hidden" name="check_in" value="{{ $checkIn }}">
                <input type="hidden" name="check_out" value="{{ $checkOut }}">
                <input type="hidden" name="adults" value="{{ $adults }}">
                <input type="hidden" name="children" value="{{ $children }}">
                <input type="hidden" name="rooms" value="{{ $rooms }}">
                <input type="hidden" name="room_type_id" id="inputPrimaryRoomTypeId" value="">
                <input type="hidden" name="rate_plan_id" id="inputPrimaryRatePlanId" value="">
                <input type="hidden" name="room_selections" id="inputRoomSelections" value="">

                <button type="submit" class="btn btn-brand btn-lg px-4 py-2.5 shadow-sm fw-bold">
                    Continue to Add-ons <i class="fa-solid fa-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let totalRoomsRequested = {{ $rooms ?? 1 }};
    let currentActiveSlot = 1;
    let selectedRoomsState = {};

    function selectRoomSlot(slotNum) {
        currentActiveSlot = slotNum;
        updateSlotsUI();
    }

    function assignSuiteToSlot(roomTypeId, roomName, ratePlanId, rateName, priceMinor, currency) {
        selectedRoomsState[currentActiveSlot] = {
            room_type_id: roomTypeId,
            room_name: roomName,
            rate_plan_id: ratePlanId,
            rate_name: rateName,
            price_minor: priceMinor,
            currency: currency
        };

        if (currentActiveSlot < totalRoomsRequested) {
            currentActiveSlot++;
        }
        updateSlotsUI();
    }

    function updateSlotsUI() {
        let configuredCount = 0;
        let totalPriceMinor = 0;
        let currency = 'KES';
        let summaryParts = [];

        for (let r = 1; r <= totalRoomsRequested; r++) {
            const tab = document.getElementById(`roomTab_${r}`);
            const label = document.getElementById(`roomTabLabel_${r}`);
            const item = selectedRoomsState[r];

            if (tab && label) {
                if (r === currentActiveSlot) {
                    tab.className = "btn btn-dark btn-sm rounded-pill px-3 py-2 fw-bold room-slot-btn shadow-sm";
                } else {
                    tab.className = "btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-bold room-slot-btn";
                }

                if (item) {
                    label.textContent = `${item.room_name} (${(item.price_minor / 100).toLocaleString('en-US', {minimumFractionDigits: 2})})`;
                    label.className = "fw-bold text-warning ms-1";
                    configuredCount++;
                    totalPriceMinor += item.price_minor;
                    currency = item.currency;
                    summaryParts.push(`Room ${r}: ${item.room_name}`);
                } else {
                    label.textContent = "Click to select...";
                    label.className = "fw-normal text-muted ms-1";
                }
            }
        }

        document.querySelectorAll('.btn-assign-room').forEach(btn => {
            btn.innerHTML = `<i class="fa-solid fa-plus me-1"></i> Select for Room ${currentActiveSlot}`;
        });

        const badge = document.getElementById('multiRoomStatusBadge');
        if (badge) {
            if (configuredCount === totalRoomsRequested) {
                badge.className = "badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold";
                badge.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> All ${totalRoomsRequested} Rooms Selected!`;
            } else {
                badge.className = "badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold";
                badge.innerHTML = `Configuring Room ${currentActiveSlot} of ${totalRoomsRequested}`;
            }
        }

        const bar = document.getElementById('stickyMultiRoomBar');
        if (bar) {
            if (configuredCount === totalRoomsRequested) {
                bar.style.display = 'block';
                document.getElementById('multiRoomTotalDisplay').textContent = `${(totalPriceMinor / 100).toLocaleString('en-US', {minimumFractionDigits: 2})} ${currency}`;
                document.getElementById('multiRoomSelectionSummaryText').textContent = summaryParts.join(' • ');
                document.getElementById('inputRoomSelections').value = JSON.stringify(selectedRoomsState);
                if (selectedRoomsState[1]) {
                    document.getElementById('inputPrimaryRoomTypeId').value = selectedRoomsState[1].room_type_id;
                    document.getElementById('inputPrimaryRatePlanId').value = selectedRoomsState[1].rate_plan_id;
                }
            } else {
                bar.style.display = 'none';
            }
        }
    }
</script>
@endpush
@endif
@endsection

