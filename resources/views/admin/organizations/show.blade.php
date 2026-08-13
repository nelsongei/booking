@extends('layouts.app')

@section('title', 'Edit — ' . $organization->name)
@section('page-title', $organization->name)
@section('breadcrumb', 'Organizations › ' . $organization->name)

@section('content')

<div class="page-header">
    <div>
        <h1>{{ $organization->name }}</h1>
        <p><code>{{ $organization->slug }}</code> &bull; {{ $organization->default_currency }} &bull; {{ $organization->country ?: 'No country' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Properties -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-buildings text-primary"></i>
                    <h6 class="mb-0">Properties ({{ $properties->count() }})</h6>
                </div>
                <a href="{{ route('admin.properties.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus me-1"></i>Add Property
                </a>
            </div>
            <div class="table-responsive">
                @if($properties->count() > 0)
                <table class="table">
                    <thead>
                        <tr><th>Property</th><th>Code</th><th>Currency</th><th>Rooms</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $prop)
                        <tr>
                            <td class="fw-semibold">{{ $prop->name }}</td>
                            <td><code class="text-primary small">{{ $prop->code }}</code></td>
                            <td>{{ $prop->currency }}</td>
                            <td>{{ $prop->rooms_count }}</td>
                            <td><span class="badge-status {{ $prop->status }}">{{ ucfirst($prop->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.properties.show', $prop) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="card-body">
                    <div class="empty-state" style="padding: 30px;">
                        <i class="bi bi-buildings" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No properties yet</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Users -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-people text-primary"></i>
                    <h6 class="mb-0">Users ({{ $users->total() }})</h6>
                </div>
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </a>
            </div>
            <div class="table-responsive">
                @if($users->count() > 0)
                <table class="table">
                    <thead>
                        <tr><th>User</th><th>Roles</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-secondary small">{{ $user->email }}</div>
                            </td>
                            <td>
                                @foreach($user->roles->take(2) as $role)
                                <span class="badge bg-primary rounded-pill me-1" style="font-size: 0.68rem;">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td><span class="badge-status {{ $user->status }}">{{ ucfirst($user->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="card-body">
                    <div class="empty-state" style="padding: 30px;">
                        <p class="mb-0">No users yet</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Details sidebar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2 text-primary"></i>
                <h6>Organization Info</h6>
            </div>
            <div class="card-body">
                <dl style="font-size: 0.85rem;" class="mb-0">
                    <dt class="text-secondary">Legal Name</dt>
                    <dd class="mb-2 fw-semibold">{{ $organization->legal_name ?: '—' }}</dd>

                    <dt class="text-secondary">Currency</dt>
                    <dd class="mb-2 fw-semibold">{{ $organization->default_currency }}</dd>

                    <dt class="text-secondary">Timezone</dt>
                    <dd class="mb-2 fw-semibold">{{ $organization->default_timezone }}</dd>

                    <dt class="text-secondary">Country</dt>
                    <dd class="mb-2 fw-semibold">{{ $organization->country ?: '—' }}</dd>

                    <dt class="text-secondary">Email</dt>
                    <dd class="mb-2">{{ $organization->email ?: '—' }}</dd>

                    <dt class="text-secondary">Status</dt>
                    <dd class="mb-0"><span class="badge-status {{ $organization->status }}">{{ ucfirst($organization->status) }}</span></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@endsection
