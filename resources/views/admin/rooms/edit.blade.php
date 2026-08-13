@extends('layouts.app')

@section('title', 'Edit Room ' . $room->room_number)
@section('page-title', 'Edit Room ' . $room->room_number)
@section('breadcrumb', 'Rooms › Room ' . $room->room_number . ' › Edit')

@section('content')

<div class="page-header">
    <div>
        <h1>Edit Room {{ $room->room_number }}</h1>
        <p>Update room configuration and housekeeping status</p>
    </div>
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.rooms.update', $room) }}" id="editRoomForm">
    @csrf @method('PUT')

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
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" value="{{ $room->room_number }}" disabled readonly>
                            <small class="text-secondary">Room numbers cannot be changed once created.</small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_type_id" required>
                                @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}" {{ old('room_type_id', $room->room_type_id) == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->name }} ({{ $rt->code }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Display Name / Alias</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $room->name) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Building</label>
                            <select class="form-select" name="building_id">
                                <option value="">Main Building</option>
                                @foreach($buildings as $b)
                                <option value="{{ $b->id }}" {{ old('building_id', $room->building_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Floor</label>
                            <select class="form-select" name="floor_id">
                                <option value="">Ground Floor</option>
                                @foreach($floors as $f)
                                <option value="{{ $f->id }}" {{ old('floor_id', $room->floor_id) == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2">{{ old('notes', $room->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-brush me-2 text-warning"></i>
                    <h6>Status & Options</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Housekeeping Status</label>
                        <select class="form-select" name="status">
                            @foreach(['clean', 'inspected', 'dirty', 'out_of_order', 'out_of_service'] as $st)
                            <option value="{{ $st }}" {{ old('status', $room->status) == $st ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="is_smoking" name="is_smoking" value="1"
                               {{ old('is_smoking', $room->is_smoking) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="is_smoking">
                            Smoking Allowed in this room
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
    document.getElementById('editRoomForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
    });
</script>
@endpush
