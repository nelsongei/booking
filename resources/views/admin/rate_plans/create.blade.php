@extends('layouts.app')

@section('title', 'Add Rate Plan')
@section('page-title', 'New Rate Plan')
@section('breadcrumb', 'Rate Plans › Add New')

@section('content')

<div class="page-header">
    <div>
        <h1>New Rate Plan</h1>
        <p>Define pricing rules and policy bindings for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.rate-plans.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.rate-plans.store') }}" id="createRatePlanForm">
    @csrf

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
                                   name="name" value="{{ old('name') }}" placeholder="e.g. Best Available Rate (BAR)" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   name="code" value="{{ old('code') }}" placeholder="e.g. BAR" maxlength="20"
                                   style="text-transform: uppercase;" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="currency" value="{{ old('currency', $property->currency) }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Meal Plan</label>
                            <select class="form-select" name="meal_plan_id">
                                <option value="">Room Only (RO)</option>
                                @foreach($mealPlans as $mp)
                                <option value="{{ $mp->id }}" {{ old('meal_plan_id') == $mp->id ? 'selected' : '' }}>
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
                                <option value="{{ $cp->id }}" {{ old('cancellation_policy_id') == $cp->id ? 'selected' : '' }}>{{ $cp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Public rate description for guests">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applicable Room Types -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-door-closed me-2 text-info"></i>
                    <h6>Applicable Room Types</h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">Select which room types can be booked under this rate plan.</p>
                    <div class="row g-2">
                        @foreach($roomTypes as $rt)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rt_{{ $rt->id }}"
                                       name="room_type_ids[]" value="{{ $rt->id }}"
                                       {{ in_array($rt->id, old('room_type_ids', [])) ? 'checked' : '' }}>
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
                               {{ old('is_public', true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_public">
                            Visible on Booking Engine
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_refundable" name="is_refundable" value="1"
                               {{ old('is_refundable', true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_refundable">
                            Refundable Rate
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="breakfast_included" name="breakfast_included" value="1"
                               {{ old('breakfast_included') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="breakfast_included">
                            Includes Breakfast
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Min Advance Booking (Days)</label>
                        <input type="number" class="form-control" name="min_advance_days" value="{{ old('min_advance_days', 0) }}" min="0">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-2 text-success"></i>
                    <h6>Save Rate Plan</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Create Rate Plan
                        </button>
                        <a href="{{ route('admin.rate-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
    document.getElementById('createRatePlanForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
