@extends('layouts.app')

@section('title', $user->name . ' — User Profile')
@section('page-title', $user->name)
@section('breadcrumb', 'Users › ' . $user->name)

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #3b6ff0, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
            {{ substr($user->name, 0, 2) }}
        </div>
        <div>
            <h1 class="mb-0">{{ $user->name }}</h1>
            <p class="mb-0 text-secondary">{{ $user->email }}</p>
        </div>
    </div>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-1"></i>Edit User
    </a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person me-2 text-primary"></i>
                <h6>Account Info</h6>
                <span class="badge-status {{ $user->status }} ms-auto">{{ ucfirst($user->status) }}</span>
            </div>
            <div class="card-body">
                <dl style="font-size: 0.85rem;" class="mb-0">
                    <dt class="text-secondary">Name</dt>
                    <dd class="fw-semibold mb-2">{{ $user->name }}</dd>

                    <dt class="text-secondary">Email</dt>
                    <dd class="mb-2">{{ $user->email }}</dd>

                    <dt class="text-secondary">Organization</dt>
                    <dd class="mb-2">{{ $user->organization?->name ?? 'Platform' }}</dd>

                    <dt class="text-secondary">Platform Admin</dt>
                    <dd class="mb-2">{{ $user->is_platform_admin ? 'Yes' : 'No' }}</dd>

                    <dt class="text-secondary">MFA</dt>
                    <dd class="mb-2">{{ $user->mfa_enabled ? 'Enabled' : 'Disabled' }}</dd>

                    <dt class="text-secondary">Last Login</dt>
                    <dd class="mb-2">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</dd>

                    <dt class="text-secondary">Member Since</dt>
                    <dd class="mb-0">{{ $user->created_at->format('M d, Y') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-check me-2 text-warning"></i>
                <h6>Roles</h6>
            </div>
            <div class="card-body">
                @forelse($user->roles as $role)
                <span class="badge bg-primary rounded-pill me-1 mb-1">{{ $role->name }}</span>
                @empty
                <span class="text-secondary small">No roles assigned</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-buildings me-2 text-success"></i>
                <h6>Property Assignments</h6>
            </div>
            <div class="card-body">
                @forelse($user->propertyAssignments as $assignment)
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background: var(--body-bg);">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; flex-shrink: 0;">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold">{{ $assignment->property?->name ?? 'Unknown Property' }}</div>
                        <div class="text-secondary small">{{ $assignment->role_name }}</div>
                    </div>
                    <span class="badge-status {{ $assignment->is_active ? 'active' : 'inactive' }}">
                        {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                @empty
                <div class="empty-state" style="padding: 30px;">
                    <i class="bi bi-buildings" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">No property assignments</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
