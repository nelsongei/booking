@extends('layouts.app')

@section('title', 'Add Room')
@section('page-title', 'New Physical Room')
@section('breadcrumb', 'Rooms › Add New')

@section('content')

<div class="page-header">
    <div>
        <h1>Add Physical Room</h1>
        <p>Register a room number for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.rooms.store') }}" id="createRoomForm">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-key me-2 text-primary"></i>
                    <h6>Room Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('room_number') is-invalid @enderror"
                                   name="room_number" value="{{ old('room_number') }}" placeholder="e.g. 101" required>
                            @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('room_type_id') is-invalid @enderror"
                                    name="room_type_id" required>
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}" {{ old('room_type_id', request('room_type_id')) == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->name }} ({{ $rt->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Display Name / Alias</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. Presidential Suite 101">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Building</label>
                            <select class="form-select" name="building_id">
                                <option value="">Main Building</option>
                                @foreach($buildings as $b)
                                <option value="{{ $b->id }}" {{ old('building_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Floor</label>
                            <select class="form-select" name="floor_id">
                                <option value="">Ground Floor</option>
                                @foreach($floors as $f)
                                <option value="{{ $f->id }}" {{ old('floor_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Internal notes for front desk / housekeeping">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-brush me-2 text-warning"></i>
                    <h6>Initial Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Housekeeping Status</label>
                        <select class="form-select" name="status">
                            <option value="clean"          {{ old('status', 'clean') == 'clean'          ? 'selected' : '' }}>Clean</option>
                            <option value="inspected"      {{ old('status') == 'inspected'      ? 'selected' : '' }}>Inspected</option>
                            <option value="dirty"          {{ old('status') == 'dirty'          ? 'selected' : '' }}>Dirty</option>
                            <option value="out_of_order"   {{ old('status') == 'out_of_order'   ? 'selected' : '' }}>Out of Order</option>
                            <option value="out_of_service" {{ old('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="is_smoking" name="is_smoking" value="1"
                               {{ old('is_smoking') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_smoking">
                            Smoking Allowed in this room
                        </label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-2 text-success"></i>
                    <h6>Save Room</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Create Room
                        </button>
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('createRoomForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
