@extends('layouts.app')

@section('title', 'Room Types')
@section('page-title', 'Room Types')
@section('breadcrumb', 'Configuration › Room Types')

@section('content')

<div class="page-header">
    <div>
        <h1>Room Types</h1>
        <p>Manage room categories, occupancies, and specifications for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.room-types.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Room Type
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-grid-1x2 me-2 text-primary"></i>Room Types ({{ $roomTypes->total() }})</h6>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="tableSearch" placeholder="Search room types...">
        </div>
    </div>

    @if($roomTypes->count() > 0)
    <div class="table-responsive">
        <table class="table" id="roomTypesTable">
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th>Code</th>
                    <th>Bed Type</th>
                    <th>Max Occupancy</th>
                    <th>Physical Rooms</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomTypes as $rt)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #3b6ff0, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="bi bi-door-closed"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $rt->name }}</div>
                                <div class="text-secondary small">{{ $rt->size_sqm ? $rt->size_sqm . ' m²' : '' }} {{ $rt->view ? '&bull; ' . ucfirst($rt->view) . ' view' : '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="text-primary small">{{ $rt->code }}</code></td>
                    <td class="text-capitalize">{{ $rt->bed_type }}</td>
                    <td>
                        <span class="fw-semibold">{{ $rt->max_occupancy }}</span>
                        <span class="text-secondary small">({{ $rt->max_adults }}A, {{ $rt->max_children }}C)</span>
                    </td>
                    <td>
                        <span class="badge bg-primary rounded-pill">{{ $rt->rooms_count }}</span>
                    </td>
                    <td><span class="badge-status {{ $rt->status }}">{{ ucfirst($rt->status) }}</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.room-types.show', $rt) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.room-types.edit', $rt) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($roomTypes->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $roomTypes->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-door-closed"></i>
            <h5>No Room Types Defined</h5>
            <p>Create room categories (e.g. Deluxe King, Ocean Suite) to define your inventory.</p>
            <a href="{{ route('admin.room-types.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Create First Room Type
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
        document.querySelectorAll('#roomTypesTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
