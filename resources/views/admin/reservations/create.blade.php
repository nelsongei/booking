@extends('layouts.app')

@section('title', 'New Reservation')
@section('page-title', 'New Reservation')
@section('breadcrumb', 'Reservations › Create')

@section('content')

<div class="page-header">
    <div>
        <h1>New Reservation</h1>
        <p>Book a stay for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.reservations.store') }}" id="createReservationForm">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">

            <!-- Guest Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person me-2 text-primary"></i>
                    <h6>Guest Profile Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('guest_first_name') is-invalid @enderror"
                                   name="guest_first_name" value="{{ old('guest_first_name') }}" placeholder="John" required>
                            @error('guest_first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('guest_last_name') is-invalid @enderror"
                                   name="guest_last_name" value="{{ old('guest_last_name') }}" placeholder="Smith" required>
                            @error('guest_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('guest_email') is-invalid @enderror"
                                   name="guest_email" value="{{ old('guest_email') }}" placeholder="john@example.com" required>
                            @error('guest_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="guest_phone" value="{{ old('guest_phone') }}" placeholder="+1 555 0192">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stay Specifications -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-calendar-range me-2 text-info"></i>
                    <h6>Stay Dates & Room Selection</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-In Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="check_in" value="{{ old('check_in', now()->toDateString()) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Check-Out Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="check_out" value="{{ old('check_out', now()->addDays(2)->toDateString()) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('room_type_id') is-invalid @enderror" name="room_type_id" required>
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}" {{ old('room_type_id') == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->name }} ({{ $rt->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rate Plan <span class="text-danger">*</span></label>
                            <select class="form-select @error('rate_plan_id') is-invalid @enderror" name="rate_plan_id" required>
                                <option value="">Select Rate Plan</option>
                                @foreach($ratePlans as $rp)
                                <option value="{{ $rp->id }}" {{ old('rate_plan_id') == $rp->id ? 'selected' : '' }}>
                                    {{ $rp->name }} ({{ $rp->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('rate_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Adults <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="adults" value="{{ old('adults', 2) }}" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Children</label>
                            <input type="number" class="form-control" name="children" value="{{ old('children', 0) }}" min="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Special Requests</label>
                            <textarea class="form-control" name="special_requests" rows="2" placeholder="e.g. Late check-in, quiet room, high floor">{{ old('special_requests') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-check-lg me-2 text-success"></i>
                    <h6>Create Reservation</h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">
                        Submitting will lock inventory for the selected dates and calculate taxes according to configured rates.
                    </p>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-calendar-check me-2"></i>Create Reservation
                        </button>
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('createReservationForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
