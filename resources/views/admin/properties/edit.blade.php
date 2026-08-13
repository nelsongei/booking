@extends('layouts.app')

@section('title', 'Edit — ' . $property->name)
@section('page-title', 'Edit Property')
@section('breadcrumb', 'Properties › ' . $property->name . ' › Edit')

@section('content')

<div class="page-header">
    <div>
        <h1>Edit: {{ $property->name }}</h1>
        <p>Update property details and settings</p>
    </div>
    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.properties.update', $property) }}" id="editPropertyForm">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-building me-2 text-primary"></i>
                    <h6>Property Identity</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Property Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $property->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                @foreach(['active', 'inactive', 'setup'] as $s)
                                <option value="{{ $s }}" {{ old('status', $property->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                @foreach(['hotel', 'resort', 'hostel', 'apartment', 'villa'] as $t)
                                <option value="{{ $t }}" {{ old('type', $property->type) == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Stars</label>
                            <select class="form-select" name="star_rating">
                                <option value="">—</option>
                                @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('star_rating', $property->star_rating) == $i ? 'selected' : '' }}>
                                    {{ str_repeat('★', $i) }}
                                </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $property->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt me-2 text-danger"></i>
                    <h6>Location & Contact</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address_line1"
                                   value="{{ old('address_line1', $property->address_line1) }}" placeholder="Street address">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="{{ old('city', $property->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State / Region</label>
                            <input type="text" class="form-control" name="state" value="{{ old('state', $property->state) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="country" maxlength="2"
                                   value="{{ old('country', $property->country) }}"
                                   style="text-transform: uppercase;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $property->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $property->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" value="{{ old('website', $property->website) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-globe me-2 text-info"></i>
                    <h6>Regional Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency" required>
                                @foreach($currencies as $code => $label)
                                <option value="{{ $code }}" {{ old('currency', $property->currency) == $code ? 'selected' : '' }}>
                                    {{ $code }} — {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Timezone <span class="text-danger">*</span></label>
                            <select class="form-select" name="timezone" required>
                                @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $property->timezone) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Locale</label>
                            <select class="form-select" name="locale">
                                @foreach(['en' => 'English', 'en-GB' => 'English UK', 'fr' => 'French', 'de' => 'German', 'es' => 'Spanish'] as $code => $label)
                                <option value="{{ $code }}" {{ old('locale', $property->locale) == $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Check-In Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="check_in_time"
                                   value="{{ old('check_in_time', $property->getCheckInTime()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Check-Out Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="check_out_time"
                                   value="{{ old('check_out_time', $property->getCheckOutTime()) }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-toggles me-2 text-success"></i>
                    <h6>Booking Engine</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="booking_engine_enabled"
                               name="booking_engine_enabled" value="1"
                               {{ old('booking_engine_enabled', $property->booking_engine_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="booking_engine_enabled">
                            Enable Public Booking Engine
                        </label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-2 text-success"></i>
                    <h6>Save Changes</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                    <hr>
                    <small class="text-secondary"><i class="bi bi-clock me-1"></i>Updated {{ $property->updated_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.querySelector('[name=country]').addEventListener('input', function() { this.value = this.value.toUpperCase(); });
    document.getElementById('editPropertyForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
    });
</script>
@endpush
