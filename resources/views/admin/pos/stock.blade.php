@extends('layouts.app')

@section('title', 'POS Stock & Inventory — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>POS Stock & Ingredient Control</h1>
        <p>Track raw ingredient stock, reorder levels, unit costs, and low-stock alerts</p>
    </div>
    <div>
        <button class="btn btn-primary rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#newStockModal">
            <i class="bi bi-plus-lg me-1"></i>Add Stock Item
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
        <h5 class="m-0 font-weight-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Raw Stock Inventory Roster</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>Stock Item Name</th>
                        <th>Unit</th>
                        <th class="text-center">Qty on Hand</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Stock Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockItems as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->name }}</td>
                            <td><code>{{ $item->unit_of_measure }}</code></td>
                            <td class="text-center fw-bold">{{ number_format($item->quantity_on_hand, 2) }}</td>
                            <td class="text-center text-muted">{{ number_format($item->reorder_level, 2) }}</td>
                            <td class="text-end">{{ $property->currency }} {{ number_format($item->unit_cost_minor / 100, 2) }}</td>
                            <td class="text-end">
                                @if($item->quantity_on_hand <= $item->reorder_level)
                                    <span class="badge bg-danger">Low Stock Alert</span>
                                @else
                                    <span class="badge bg-success">Optimal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No stock items configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: New Stock Item -->
<div class="modal fade" id="newStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Add Raw Stock Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pos.stock.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ingredient / Item Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Beef Ribeye Cut" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit of Measure</label>
                        <select name="unit_of_measure" class="form-select" required>
                            <option value="kg">Kilograms (kg)</option>
                            <option value="liters">Liters (L)</option>
                            <option value="pieces">Pieces / Units</option>
                            <option value="bottles">Bottles</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Initial Quantity</label>
                            <input type="number" step="0.01" name="quantity_on_hand" class="form-control" placeholder="50.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Reorder Level Alert</label>
                            <input type="number" step="0.01" name="reorder_level" class="form-control" placeholder="10.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Cost ({{ $property->currency }})</label>
                        <input type="number" step="0.01" name="unit_cost" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Stock Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
