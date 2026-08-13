@extends('layouts.app')

@section('title', 'Rate Plans')
@section('page-title', 'Rate Plans')
@section('breadcrumb', 'Configuration › Rate Plans')

@section('content')

<div class="page-header">
    <div>
        <h1>Rate Plans</h1>
        <p>Configure pricing rules, meal plans, and policies for {{ $property->name }}</p>
    </div>
    <a href="{{ route('admin.rate-plans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Rate Plan
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-tags me-2 text-primary"></i>Rate Plans ({{ $ratePlans->total() }})</h6>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="tableSearch" placeholder="Search rate plans...">
        </div>
    </div>

    @if($ratePlans->count() > 0)
    <div class="table-responsive">
        <table class="table" id="ratePlansTable">
            <thead>
                <tr>
                    <th>Rate Plan</th>
                    <th>Code</th>
                    <th>Currency</th>
                    <th>Meal Plan</th>
                    <th>Refundable</th>
                    <th>Public</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratePlans as $rp)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #14b8a6, #0d9488); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="bi bi-tag"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $rp->name }}</div>
                                <div class="text-secondary small">{{ $rp->roomTypes->count() }} room type(s) attached</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="text-primary small">{{ $rp->code }}</code></td>
                    <td>{{ $rp->currency }}</td>
                    <td>{{ $rp->mealPlan?->name ?: 'Room Only' }}</td>
                    <td>
                        @if($rp->is_refundable)
                        <span class="badge bg-success">Refundable</span>
                        @else
                        <span class="badge bg-danger">Non-Refundable</span>
                        @endif
                    </td>
                    <td>
                        @if($rp->is_public)
                        <span class="badge-status active">Public</span>
                        @else
                        <span class="badge-status inactive">Private</span>
                        @endif
                    </td>
                    <td><span class="badge-status {{ $rp->is_active ? 'active' : 'inactive' }}">{{ $rp->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.rate-plans.show', $rp) }}" class="btn btn-sm btn-outline-primary" title="Daily Rates Calendar">
                                <i class="bi bi-calendar3"></i> Rates
                            </a>
                            <a href="{{ route('admin.rate-plans.edit', $rp) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($ratePlans->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $ratePlans->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-tags"></i>
            <h5>No Rate Plans Configured</h5>
            <p>Create rate plans (e.g. Standard Rate, Bed & Breakfast, Non-Refundable) to price your rooms.</p>
            <a href="{{ route('admin.rate-plans.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Create First Rate Plan
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
        document.querySelectorAll('#ratePlansTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
