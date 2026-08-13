@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')
@section('breadcrumb', 'Configuration › Users')

@section('content')

<div class="page-header">
    <div>
        <h1>Users</h1>
        <p>Manage staff access and roles across your properties</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Add User
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>All Users</h6>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="tableSearch" placeholder="Search users...">
        </div>
    </div>

    @if($users->count() > 0)
    <div class="table-responsive">
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Organization</th>
                    <th>Roles</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #3b6ff0, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-secondary small">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-secondary small">{{ $user->organization?->name ?? 'Platform' }}</td>
                    <td>
                        @foreach($user->roles->take(2) as $role)
                        <span class="badge bg-primary rounded-pill me-1" style="font-size: 0.68rem;">
                            {{ $role->name }}
                        </span>
                        @endforeach
                        @if($user->roles->count() > 2)
                        <span class="text-secondary small">+{{ $user->roles->count() - 2 }} more</span>
                        @endif
                        @if($user->is_platform_admin)
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.68rem;">Platform Admin</span>
                        @endif
                    </td>
                    <td class="text-secondary small">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td><span class="badge-status {{ $user->status }}">{{ ucfirst($user->status) }}</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-body border-top d-flex justify-content-end">
        {{ $users->links() }}
    </div>
    @endif

    @else
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <h5>No Users Yet</h5>
            <p>Invite your team members to manage the platform.</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i>Add First User
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
        document.querySelectorAll('#usersTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
