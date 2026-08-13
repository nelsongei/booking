@extends('layouts.app')

@section('title', 'Organizations')
@section('page-title', 'Organizations')
@section('breadcrumb', 'Platform Administration › Organizations')

@section('content')

<div class="page-header">
    <div>
        <h1>Organizations</h1>
        <p>Manage hotel groups on the platform</p>
    </div>
    <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>New Organization
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>All Organizations</h6>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="tableSearch" placeholder="Search organizations...">
        </div>
    </div>

    @if($organizations->count() > 0)
    <div class="table-responsive">
        <table class="table" id="organizationsTable">
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Slug</th>
                    <th>Currency</th>
                    <th>Country</th>
                    <th>Properties</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($organizations as $org)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #3b6ff0, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                                {{ substr($org->name, 0, 2) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $org->name }}</div>
                                @if($org->legal_name)
                                <div class="text-secondary small">{{ $org->legal_name }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td><code class="text-primary small">{{ $org->slug }}</code></td>
                    <td>{{ $org->default_currency }}</td>
                    <td>{{ $org->country ?: '—' }}</td>
                    <td>
                        <span class="badge bg-primary rounded-pill">{{ $org->properties_count }}</span>
                    </td>
                    <td><span class="badge-status {{ $org->status }}">{{ ucfirst($org->status) }}</span></td>
                    <td class="text-secondary small">{{ $org->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.organizations.show', $org) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.organizations.edit', $org) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($organizations->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $organizations->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-diagram-3"></i>
            <h5>No Organizations Yet</h5>
            <p>Create the first hotel group to get started.</p>
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Create Organization
            </a>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Client-side table search
    document.getElementById('tableSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#organizationsTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
