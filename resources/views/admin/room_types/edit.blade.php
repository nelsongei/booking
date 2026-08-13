@extends('layouts.app')

@section('title', 'Edit — ' . $roomType->name)
@section('page-title', 'Edit Room Type')
@section('breadcrumb', 'Room Types › ' . $roomType->name . ' › Edit')

@section('content')

<div class="page-header">
    <div>
        <h1>Edit: {{ $roomType->name }}</h1>
        <p>Update specifications and amenities</p>
    </div>
    <a href="{{ route('admin.room-types.show', $roomType) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.room-types.update', $roomType) }}" id="editRoomTypeForm">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
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
                                   name="name" value="{{ old('name', $roomType->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bed Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="bed_type" required>
                                @foreach(['king', 'queen', 'double', 'twin', 'single'] as $b)
                                <option value="{{ $b }}" {{ old('bed_type', $roomType->bed_type) == $b ? 'selected' : '' }}>{{ ucfirst($b) }} Bed</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Room Size (m²)</label>
                            <input type="number" class="form-control" name="size_sqm" value="{{ old('size_sqm', $roomType->size_sqm) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">View</label>
                            <input type="text" class="form-control" name="view" value="{{ old('view', $roomType->view) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $roomType->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-people me-2 text-info"></i>
                    <h6>Occupancy Limits</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Base Occupancy</label>
                            <input type="number" class="form-control" name="base_occupancy" value="{{ old('base_occupancy', $roomType->base_occupancy) }}" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Adults</label>
                            <input type="number" class="form-control" name="max_adults" value="{{ old('max_adults', $roomType->max_adults) }}" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Children</label>
                            <input type="number" class="form-control" name="max_children" value="{{ old('max_children', $roomType->max_children) }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Occupancy</label>
                            <input type="number" class="form-control" name="max_occupancy" value="{{ old('max_occupancy', $roomType->max_occupancy) }}" min="1" required>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($amenities) && $amenities->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-stars me-2 text-warning"></i>
                    <h6>Amenities</h6>
                </div>
                <div class="card-body">
                    @php $selectedAmenityIds = $roomType->amenities->pluck('id')->toArray(); @endphp
                    @foreach($amenities as $category => $catAmenities)
                    <div class="mb-3">
                        <div class="fw-semibold text-secondary small text-uppercase mb-2">{{ $category ?: 'General' }}</div>
                        <div class="row g-2">
                            @foreach($catAmenities as $am)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="am_{{ $am->id }}"
                                           name="amenities[]" value="{{ $am->id }}"
                                           {{ in_array($am->id, old('amenities', $selectedAmenityIds)) ? 'checked' : '' }}>
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
                    <h6>Settings</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_accessible" name="is_accessible" value="1"
                               {{ old('is_accessible', $roomType->is_accessible) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_accessible">Wheelchair Accessible</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="smoking_allowed" name="smoking_allowed" value="1"
                               {{ old('smoking_allowed', $roomType->smoking_allowed) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="smoking_allowed">Smoking Allowed</label>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active"   {{ old('status', $roomType->status) == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $roomType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
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
                        <a href="{{ route('admin.room-types.show', $roomType) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('editRoomTypeForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
    });
</script>
@endpush
