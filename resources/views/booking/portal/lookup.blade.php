@extends('layouts.guest')

@section('title', 'Guest Self-Service Portal — Find Reservation')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card-custom p-4 shadow-sm text-center">
            <div class="bg-light p-3 rounded-circle d-inline-flex mb-3 text-warning">
                <i class="fa-solid fa-receipt fs-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Guest Portal</h3>
            <p class="text-muted small mb-4">Enter your Confirmation Number and Email address to retrieve your reservation details.</p>

            <form action="{{ route('booking.portal.search') }}" method="POST">
                @csrf
                <div class="text-start mb-3">
                    <label class="form-label fw-semibold text-dark">Confirmation Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-hashtag text-muted"></i></span>
                        <input type="text" name="confirmation_number" class="form-control form-control-lg text-uppercase" placeholder="e.g. SH001-202608-X89F" value="{{ old('confirmation_number') }}" required>
                    </div>
                </div>

                <div class="text-start mb-4">
                    <label class="form-label fw-semibold text-dark">Guest Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="john.doe@example.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-brand w-100 py-3 mb-3">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Find Reservation
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
