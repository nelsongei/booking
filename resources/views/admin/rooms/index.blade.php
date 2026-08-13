@extends('layouts.app')

@section('title', 'Physical Rooms')
@section('page-title', 'Physical Rooms')
@section('breadcrumb', 'Configuration › Rooms')

@section('content')

<div class="page-header">
    <div>
        <h1>Physical Rooms</h1>
        <p>Manage individual rooms and housekeeping status for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Room
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.rooms.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Filter by Room Type</label>
                <select class="form-select" name="room_type_id" onchange="this.form.submit()">
                    <option value="">All Room Types</option>
                    @foreach($roomTypes as $rt)
                    <option value="{{ $rt->id }}" {{ request('room_type_id') == $rt->id ? 'selected' : '' }}>
                        {{ $rt->name }} ({{ $rt->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Filter by Status</label>
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['clean', 'dirty', 'inspected', 'out_of_order', 'out_of_service'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-key me-2 text-primary"></i>Rooms ({{ $rooms->total() }})</h6>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="tableSearch" placeholder="Search room #...">
        </div>
    </div>

    @if($rooms->count() > 0)
    <div class="table-responsive">
        <table class="table" id="roomsTable">
            <thead>
                <tr>
                    <th>Room #</th>
                    <th>Name</th>
                    <th>Room Type</th>
                    <th>Building / Floor</th>
                    <th>Smoking</th>
                    <th>Housekeeping Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $rm)
                <tr>
                    <td class="fw-bold fs-6"><code>{{ $rm->room_number }}</code></td>
                    <td>{{ $rm->name ?: '—' }}</td>
                    <td class="fw-semibold">{{ $rm->roomType?->name }}</td>
                    <td class="text-secondary small">
                        {{ $rm->building?->name ?: 'Main' }} {{ $rm->floor ? '/ ' . $rm->floor->name : '' }}
                    </td>
                    <td>
                        @if($rm->is_smoking)
                        <span class="badge bg-warning text-dark">Smoking</span>
                        @else
                        <span class="badge bg-light text-secondary border">Non-Smoking</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status {{ $rm->status }}">
                            {{ ucfirst(str_replace('_', ' ', $rm->status)) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.rooms.edit', $rm) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($rooms->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $rooms->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-key"></i>
            <h5>No Rooms Found</h5>
            <p>Add physical rooms (e.g. Room 101, 102) to build your operational floor map.</p>
            <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Add First Room
            </a>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#roomsTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
