@extends('layouts.app')

@section('title', 'Properties')
@section('page-title', 'Properties')
@section('breadcrumb', 'Configuration › Properties')

@section('content')

<div class="page-header">
    <div>
        <h1>Properties</h1>
        <p>Manage hotels and properties in your portfolio</p>
    </div>
    <a href="{{ route('admin.properties.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Property
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-buildings me-2 text-primary"></i>All Properties</h6>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="tableSearch" placeholder="Search properties...">
        </div>
    </div>

    @if($properties->count() > 0)
    <div class="table-responsive">
        <table class="table" id="propertiesTable">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Code</th>
                    <th>Organization</th>
                    <th>Currency</th>
                    <th>Type</th>
                    <th>Booking Engine</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($properties as $property)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $property->name }}</div>
                                @if($property->city)
                                <div class="text-secondary small">{{ $property->city }}{{ $property->country ? ', ' . $property->country : '' }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td><code class="text-primary small">{{ $property->code }}</code></td>
                    <td class="text-secondary small">{{ $property->organization?->name ?? '—' }}</td>
                    <td>{{ $property->currency }}</td>
                    <td>
                        <span class="text-capitalize">{{ str_replace('_', ' ', $property->type) }}</span>
                    </td>
                    <td>
                        @if($property->booking_engine_enabled)
                        <span class="badge-status active">Online</span>
                        @else
                        <span class="badge-status inactive">Offline</span>
                        @endif
                    </td>
                    <td><span class="badge-status {{ $property->status }}">{{ ucfirst($property->status) }}</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.switch.property') }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Switch to this property">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($properties->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $properties->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-buildings"></i>
            <h5>No Properties Yet</h5>
            <p>Add your first hotel property to start managing reservations.</p>
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Add First Property
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
        document.querySelectorAll('#propertiesTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
