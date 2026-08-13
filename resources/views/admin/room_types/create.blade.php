@extends('layouts.app')

@section('title', 'Add Room Type')
@section('page-title', 'New Room Type')
@section('breadcrumb', 'Room Types › Add New')

@section('content')

<div class="page-header">
    <div>
        <h1>New Room Type</h1>
        <p>Define a room category for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.room-types.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.room-types.store') }}" id="createRoomTypeForm">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Basic Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-door-closed me-2 text-primary"></i>
                    <h6>Room Specifications</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Room Type Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" placeholder="e.g. Deluxe Ocean View King" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   name="code" value="{{ old('code') }}" placeholder="e.g. DLXK" maxlength="20"
                                   style="text-transform: uppercase;" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bed Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="bed_type" required>
                                <option value="king"   {{ old('bed_type', 'king') == 'king'   ? 'selected' : '' }}>King Bed</option>
                                <option value="queen"  {{ old('bed_type') == 'queen'  ? 'selected' : '' }}>Queen Bed</option>
                                <option value="double" {{ old('bed_type') == 'double' ? 'selected' : '' }}>Double Bed</option>
                                <option value="twin"   {{ old('bed_type') == 'twin'   ? 'selected' : '' }}>Twin Beds</option>
                                <option value="single" {{ old('bed_type') == 'single' ? 'selected' : '' }}>Single Bed</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Room Size (m²)</label>
                            <input type="number" class="form-control" name="size_sqm" value="{{ old('size_sqm') }}" placeholder="e.g. 35">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">View</label>
                            <input type="text" class="form-control" name="view" value="{{ old('view') }}" placeholder="e.g. Sea, Garden, City">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Public description shown on booking engine">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Occupancy Limits -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-people me-2 text-info"></i>
                    <h6>Occupancy Limits</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Base Occupancy <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="base_occupancy" value="{{ old('base_occupancy', 2) }}" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Max Adults <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_adults" value="{{ old('max_adults', 2) }}" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Max Children</label>
                            <input type="number" class="form-control" name="max_children" value="{{ old('max_children', 1) }}" min="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Max Occupancy <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_occupancy" value="{{ old('max_occupancy', 3) }}" min="1" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            @if(isset($amenities) && $amenities->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-stars me-2 text-warning"></i>
                    <h6>Amenities</h6>
                </div>
                <div class="card-body">
                    @foreach($amenities as $category => $catAmenities)
                    <div class="mb-3">
                        <div class="fw-semibold text-secondary small text-uppercase mb-2">{{ $category ?: 'General' }}</div>
                        <div class="row g-2">
                            @foreach($catAmenities as $am)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="am_{{ $am->id }}"
                                           name="amenities[]" value="{{ $am->id }}"
                                           {{ in_array($am->id, old('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="am_{{ $am->id }}">
                                        {{ $am->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-toggles me-2 text-success"></i>
                    <h6>Settings & Options</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_accessible" name="is_accessible" value="1"
                               {{ old('is_accessible') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_accessible">
                            Wheelchair Accessible
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="smoking_allowed" name="smoking_allowed" value="1"
                               {{ old('smoking_allowed') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="smoking_allowed">
                            Smoking Allowed
                        </label>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active"   {{ old('status', 'active') == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-2 text-success"></i>
                    <h6>Save Room Type</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Create Room Type
                        </button>
                        <a href="{{ route('admin.room-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.querySelector('[name=code]').addEventListener('input', function() { this.value = this.value.toUpperCase(); });
    document.getElementById('createRoomTypeForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
