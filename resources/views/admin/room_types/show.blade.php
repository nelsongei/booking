@extends('layouts.app')

@section('title', $roomType->name . ' — Room Type')
@section('page-title', $roomType->name)
@section('breadcrumb', 'Room Types › ' . $roomType->name)

@section('content')

<div class="page-header">
    <div>
        <h1>{{ $roomType->name }}</h1>
        <p><code>{{ $roomType->code }}</code> &bull; Bed: <span class="text-capitalize">{{ $roomType->bed_type }}</span> &bull; Max Occupancy: {{ $roomType->max_occupancy }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.room-types.edit', $roomType) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-door-closed me-2 text-primary"></i>
                <h6>Room Type Specs</h6>
                <span class="badge-status {{ $roomType->status }} ms-auto">{{ ucfirst($roomType->status) }}</span>
            </div>
            <div class="card-body">
                <dl style="font-size: 0.85rem;" class="mb-0">
                    <dt class="text-secondary">Code</dt>
                    <dd class="fw-semibold mb-2"><code>{{ $roomType->code }}</code></dd>

                    <dt class="text-secondary">Bed Type</dt>
                    <dd class="fw-semibold mb-2 text-capitalize">{{ $roomType->bed_type }}</dd>

                    <dt class="text-secondary">Size</dt>
                    <dd class="fw-semibold mb-2">{{ $roomType->size_sqm ? $roomType->size_sqm . ' m²' : '—' }}</dd>

                    <dt class="text-secondary">View</dt>
                    <dd class="fw-semibold mb-2 text-capitalize">{{ $roomType->view ?: '—' }}</dd>

                    <dt class="text-secondary">Base Occupancy</dt>
                    <dd class="fw-semibold mb-2">{{ $roomType->base_occupancy }} person(s)</dd>

                    <dt class="text-secondary">Max Capacity</dt>
                    <dd class="fw-semibold mb-2">{{ $roomType->max_occupancy }} ({{ $roomType->max_adults }} Adults, {{ $roomType->max_children }} Children)</dd>

                    <dt class="text-secondary">Accessible</dt>
                    <dd class="mb-2">{{ $roomType->is_accessible ? 'Yes' : 'No' }}</dd>

                    <dt class="text-secondary">Smoking</dt>
                    <dd class="mb-0">{{ $roomType->smoking_allowed ? 'Allowed' : 'Non-Smoking' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-stars me-2 text-warning"></i>
                <h6>Amenities</h6>
            </div>
            <div class="card-body">
                @forelse($roomType->amenities as $am)
                <span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 0.78rem;">
                    <i class="bi bi-check me-1 text-success"></i>{{ $am->name }}
                </span>
                @empty
                <span class="text-secondary small">No amenities selected</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-key text-primary"></i>
                    <h6 class="mb-0">Physical Rooms ({{ $roomType->rooms->count() }})</h6>
                </div>
                <a href="{{ route('admin.rooms.create') }}?room_type_id={{ $roomType->id }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus me-1"></i>Add Physical Room
                </a>
            </div>
            <div class="table-responsive">
                @if($roomType->rooms->count() > 0)
                <table class="table">
                    <thead>
                        <tr><th>Room #</th><th>Name</th><th>Building / Floor</th><th>Housekeeping</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($roomType->rooms as $rm)
                        <tr>
                            <td class="fw-bold"><code>{{ $rm->room_number }}</code></td>
                            <td>{{ $rm->name ?: '—' }}</td>
                            <td class="text-secondary small">
                                {{ $rm->building?->name ?: 'Main' }} {{ $rm->floor ? '/ ' . $rm->floor->name : '' }}
                            </td>
                            <td><span class="badge-status {{ $rm->status }}">{{ ucfirst(str_replace('_', ' ', $rm->status)) }}</span></td>
                            <td>
                                <a href="{{ route('admin.rooms.edit', $rm) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="card-body">
                    <div class="empty-state" style="padding: 30px;">
                        <i class="bi bi-door-closed" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No physical rooms assigned to this room type yet.</p>
                        <a href="{{ route('admin.rooms.create') }}?room_type_id={{ $roomType->id }}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus me-1"></i>Add Room
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
