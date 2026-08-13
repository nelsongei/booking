@extends('layouts.app')

@section('title', 'Add Property')
@section('page-title', 'Add Property')
@section('breadcrumb', 'Properties › Add New')

@section('content')

<div class="page-header">
    <div>
        <h1>Add Property</h1>
        <p>Register a new hotel or accommodation property</p>
    </div>
    <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.properties.store') }}" id="createPropertyForm">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">

            <!-- Identity -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-building me-2 text-primary"></i>
                    <h6>Property Identity</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="name">Property Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g. Tembo Hotel" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="code">Property Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" value="{{ old('code') }}"
                                   placeholder="e.g. HTL01" maxlength="20" style="text-transform: uppercase;" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="organization_id">Organization <span class="text-danger">*</span></label>
                            <select class="form-select @error('organization_id') is-invalid @enderror"
                                    id="organization_id" name="organization_id" required>
                                <option value="">Select Organization</option>
                                @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id', auth()->user()->organization_id) == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="type">Property Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="hotel"     {{ old('type', 'hotel') == 'hotel'     ? 'selected' : '' }}>Hotel</option>
                                <option value="resort"    {{ old('type') == 'resort'   ? 'selected' : '' }}>Resort</option>
                                <option value="hostel"    {{ old('type') == 'hostel'   ? 'selected' : '' }}>Hostel</option>
                                <option value="apartment" {{ old('type') == 'apartment'? 'selected' : '' }}>Serviced Apartments</option>
                                <option value="villa"     {{ old('type') == 'villa'    ? 'selected' : '' }}>Villa / Boutique</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label" for="star_rating">Stars</label>
                            <select class="form-select" id="star_rating" name="star_rating">
                                <option value="">—</option>
                                @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('star_rating') == $i ? 'selected' : '' }}>
                                    {{ str_repeat('★', $i) }}
                                </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Brief description of the property">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt me-2 text-danger"></i>
                    <h6>Location & Contact</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="address_line1">Address Line 1</label>
                            <input type="text" class="form-control" id="address_line1" name="address_line1"
                                   value="{{ old('address_line1') }}" placeholder="Street address">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label" for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                   value="{{ old('city') }}" placeholder="City">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="state">State / Region</label>
                            <input type="text" class="form-control" id="state" name="state"
                                   value="{{ old('state') }}" placeholder="State">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="country">Country Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="country" name="country"
                                   value="{{ old('country') }}" placeholder="US" maxlength="2"
                                   style="text-transform: uppercase;" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email">Property Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="{{ old('email') }}" placeholder="reception@hotel.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="{{ old('phone') }}" placeholder="+1 555 000 0000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regional Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-globe me-2 text-info"></i>
                    <h6>Regional Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="currency">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="currency" name="currency" required>
                                @foreach($currencies as $code => $label)
                                <option value="{{ $code }}" {{ old('currency', 'USD') == $code ? 'selected' : '' }}>
                                    {{ $code }} — {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                            <select class="form-select" id="timezone" name="timezone" required>
                                @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', 'UTC') == $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="locale">Locale</label>
                            <select class="form-select" id="locale" name="locale">
                                <option value="en"    {{ old('locale', 'en') == 'en'    ? 'selected' : '' }}>English</option>
                                <option value="en-GB" {{ old('locale') == 'en-GB' ? 'selected' : '' }}>English UK</option>
                                <option value="fr"    {{ old('locale') == 'fr'    ? 'selected' : '' }}>French</option>
                                <option value="de"    {{ old('locale') == 'de'    ? 'selected' : '' }}>German</option>
                                <option value="es"    {{ old('locale') == 'es'    ? 'selected' : '' }}>Spanish</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="check_in_time">Check-In Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="check_in_time" name="check_in_time"
                                   value="{{ old('check_in_time', '14:00') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="check_out_time">Check-Out Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="check_out_time" name="check_out_time"
                                   value="{{ old('check_out_time', '12:00') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-toggles me-2 text-success"></i>
                    <h6>Booking Engine</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="booking_engine_enabled"
                               name="booking_engine_enabled" value="1"
                               {{ old('booking_engine_enabled') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="booking_engine_enabled">
                            Enable Public Booking Engine
                        </label>
                    </div>
                    <small class="text-secondary d-block mt-2">
                        Allow guests to book directly on your property's booking page.
                        You can enable this later once rates and rooms are configured.
                    </small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-2 text-success"></i>
                    <h6>Save Property</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Create Property
                        </button>
                        <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Auto-uppercase code
    document.getElementById('code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    document.getElementById('country').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    document.getElementById('createPropertyForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
