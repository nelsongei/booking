@extends('layouts.app')

@section('title', 'POS Menu & Catalog — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>POS Menu & Catalog Management</h1>
        <p>Manage dish items, beverages, pricing, taxability, and outlet catalog assignments</p>
    </div>
    <div>
        <button class="btn btn-primary rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#newMenuItemModal">
            <i class="bi bi-plus-lg me-1"></i>Add Menu Item
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 font-weight-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Menu Item Catalog</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Outlet</th>
                        <th class="text-end">Price ({{ $property->currency }})</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->name }}</td>
                            <td><span class="badge bg-light text-dark text-capitalize">{{ $item->category }}</span></td>
                            <td>Main Restaurant</td>
                            <td class="text-end fw-bold">{{ $property->currency }} {{ number_format($item->price_minor / 100, 2) }}</td>
                            <td class="text-end"><span class="badge bg-success">Available</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No menu items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Item -->
<div class="modal fade" id="newMenuItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pos.menu.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Outlet</label>
                        <select name="pos_outlet_id" class="form-select" required>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Lobster Thermidor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="mains">Mains & Entrees</option>
                            <option value="starters">Starters & Appetizers</option>
                            <option value="beverages">Beverages & Bar</option>
                            <option value="desserts">Desserts & Sweets</option>
                            <option value="services">Spa & Other Services</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price ({{ $property->currency }})</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
