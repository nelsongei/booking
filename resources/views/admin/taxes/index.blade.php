@extends('layouts.app')

@section('title', 'Taxes & Fees')
@section('page-title', 'Taxes & Fees')
@section('breadcrumb', 'Configuration › Taxes & Fees')

@section('content')

<div class="page-header">
    <div>
        <h1>Taxes & Fees</h1>
        <p>Configure tax rates and fee structures for {{ $property->name }}</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaxModal">
        <i class="bi bi-plus-lg me-2"></i>Add Tax / Fee
    </button>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-percent me-2 text-primary"></i>
        <h6>Configured Taxes & Fees</h6>
    </div>

    @if($taxes->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tax Name</th>
                    <th>Code</th>
                    <th>Calculation Type</th>
                    <th>Rate / Amount</th>
                    <th>Included in Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taxes as $tax)
                <tr>
                    <td class="fw-semibold">{{ $tax->name }}</td>
                    <td><code class="text-primary small">{{ $tax->code }}</code></td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $tax->type) }}</td>
                    <td class="fw-bold">
                        @if($tax->type === 'percentage')
                        {{ $tax->rate }}%
                        @else
                        {{ number_format($tax->rate, 2) }} {{ $tax->currency }}
                        @endif
                    </td>
                    <td>
                        @if($tax->is_included_in_rate)
                        <span class="badge bg-info text-dark">Included</span>
                        @else
                        <span class="badge bg-light text-secondary border">Added on Top</span>
                        @endif
                    </td>
                    <td><span class="badge-status {{ $tax->is_active ? 'active' : 'inactive' }}">{{ $tax->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTaxModal_{{ $tax->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editTaxModal_{{ $tax->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.taxes.update', $tax) }}">
                                @csrf @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit {{ $tax->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tax Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ $tax->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-select" name="type" required>
                                            <option value="percentage"            {{ $tax->type === 'percentage'            ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="fixed_per_night"       {{ $tax->type === 'fixed_per_night'       ? 'selected' : '' }}>Fixed Amount per Night</option>
                                            <option value="fixed_per_stay"        {{ $tax->type === 'fixed_per_stay'        ? 'selected' : '' }}>Fixed Amount per Stay</option>
                                            <option value="fixed_per_person"      {{ $tax->type === 'fixed_per_person'      ? 'selected' : '' }}>Fixed Amount per Person</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Rate / Amount</label>
                                        <input type="number" class="form-control" name="rate" step="0.0001" value="{{ $tax->rate }}" required>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_included_in_rate" value="1" {{ $tax->is_included_in_rate ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold">Included in room rate</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="applies_to_extras" value="1" {{ $tax->applies_to_extras ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold">Applies to extras & add-ons</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $tax->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold">Active</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-percent"></i>
            <h5>No Taxes Configured</h5>
            <p>Add VAT, Occupancy Tax, or City Fees for your property.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaxModal">
                <i class="bi bi-plus me-1"></i>Add First Tax Rate
            </button>
        </div>
    </div>
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTaxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.taxes.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Tax / Fee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tax / Fee Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. VAT / Sales Tax" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. VAT16" maxlength="20" style="text-transform: uppercase;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Calculation Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed_per_night">Fixed Amount per Night</option>
                            <option value="fixed_per_stay">Fixed Amount per Stay</option>
                            <option value="fixed_per_person">Fixed Amount per Person</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rate / Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="rate" step="0.0001" placeholder="e.g. 16.00 or 5.00" required>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_included_in_rate" value="1">
                        <label class="form-check-label small fw-semibold">Included in room rate</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="applies_to_extras" value="1">
                        <label class="form-check-label small fw-semibold">Applies to extras & add-ons</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tax Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
