@extends('layouts.app')

@section('title', 'Edit — ' . $ratePlan->name)
@section('page-title', 'Edit Rate Plan')
@section('breadcrumb', 'Rate Plans › ' . $ratePlan->name . ' › Edit')

@section('content')

<div class="page-header">
    <div>
        <h1>Edit: {{ $ratePlan->name }}</h1>
        <p>Update pricing rules and policy bindings</p>
    </div>
    <a href="{{ route('admin.rate-plans.show', $ratePlan) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.rate-plans.update', $ratePlan) }}" id="editRatePlanForm">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-tag me-2 text-primary"></i>
                    <h6>Rate Plan Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Rate Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $ratePlan->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-control" name="currency" value="{{ $ratePlan->currency }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Meal Plan</label>
                            <select class="form-select" name="meal_plan_id">
                                <option value="">Room Only (RO)</option>
                                @foreach($mealPlans as $mp)
                                <option value="{{ $mp->id }}" {{ old('meal_plan_id', $ratePlan->meal_plan_id) == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->name }} ({{ $mp->code }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cancellation Policy</label>
                            <select class="form-select" name="cancellation_policy_id">
                                <option value="">Standard Policy</option>
                                @foreach($cancelPols as $cp)
                                <option value="{{ $cp->id }}" {{ old('cancellation_policy_id', $ratePlan->cancellation_policy_id) == $cp->id ? 'selected' : '' }}>{{ $cp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $ratePlan->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-door-closed me-2 text-info"></i>
                    <h6>Applicable Room Types</h6>
                </div>
                <div class="card-body">
                    @php $selectedRtIds = $ratePlan->roomTypes->pluck('id')->toArray(); @endphp
                    <div class="row g-2">
                        @foreach($roomTypes as $rt)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rt_{{ $rt->id }}"
                                       name="room_type_ids[]" value="{{ $rt->id }}"
                                       {{ in_array($rt->id, old('room_type_ids', $selectedRtIds)) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="rt_{{ $rt->id }}">
                                    <span class="fw-semibold">{{ $rt->name }}</span>
                                    <span class="text-secondary ms-1">({{ $rt->code }})</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-sliders me-2 text-warning"></i>
                    <h6>Rate Controls</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                               {{ old('is_public', $ratePlan->is_public) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_public">Visible on Booking Engine</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_refundable" name="is_refundable" value="1"
                               {{ old('is_refundable', $ratePlan->is_refundable) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_refundable">Refundable Rate</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="breakfast_included" name="breakfast_included" value="1"
                               {{ old('breakfast_included', $ratePlan->breakfast_included) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="breakfast_included">Includes Breakfast</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Min Advance Days</label>
                        <input type="number" class="form-control" name="min_advance_days" value="{{ old('min_advance_days', $ratePlan->min_advance_days) }}" min="0">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" {{ old('is_active', $ratePlan->is_active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('is_active', $ratePlan->is_active) ? 'selected' : '' }}>Inactive</option>
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
                        <a href="{{ route('admin.rate-plans.show', $ratePlan) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('editRatePlanForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
    });
</script>
@endpush
