@extends('layouts.app')

@section('title', 'POS Outlets — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>POS Outlets Management</h1>
        <p>Configure restaurant, bar, spa, rooftop lounge, and room service outlets</p>
    </div>
    <div>
        <button class="btn btn-primary rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#newOutletModal">
            <i class="bi bi-plus-lg me-1"></i>New POS Outlet
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    @forelse($outlets as $outlet)
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-shop fs-4"></i>
                        </div>
                        <span class="badge bg-success rounded-pill px-3 py-2">ACTIVE</span>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $outlet->name }}</h5>
                    <div class="text-muted small text-capitalize mb-3">{{ $outlet->type }} Outlet &bull; Code: <code>{{ $outlet->code }}</code></div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <a href="{{ route('admin.pos.terminal') }}" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-bold">
                            <i class="bi bi-grid-fill me-1"></i>Open Terminal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-shop fs-1 mb-2"></i>
            <p>No outlets configured yet. Click "New POS Outlet" above to get started.</p>
        </div>
    @endforelse
</div>

<!-- Modal: New Outlet -->
<div class="modal fade" id="newOutletModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-shop me-2 text-primary"></i>Create New POS Outlet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pos.outlets.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Outlet Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Rooftop Cocktail Lounge" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outlet Type</label>
                        <select name="type" class="form-select" required>
                            <option value="restaurant">Restaurant & Fine Dining</option>
                            <option value="bar">Bar & Lounge</option>
                            <option value="spa">Spa & Wellness</option>
                            <option value="shop">Gift Shop & Boutique</option>
                            <option value="minibar">Minibar & Room Service</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outlet Code (Optional)</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. ROOF-01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Create Outlet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
