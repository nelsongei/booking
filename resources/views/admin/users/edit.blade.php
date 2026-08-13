@extends('layouts.app')

@section('title', 'Edit — ' . $user->name)
@section('page-title', 'Edit User')
@section('breadcrumb', 'Users › ' . $user->name . ' › Edit')

@section('content')

<div class="page-header">
    <div>
        <h1>Edit: {{ $user->name }}</h1>
        <p>Update user account details and permissions</p>
    </div>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person me-2 text-primary"></i>
                    <h6>Account Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                @foreach(['active', 'suspended', 'invited'] as $s)
                                <option value="{{ $s }}" {{ old('status', $user->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-key me-2 text-warning"></i>
                    <h6>Change Password <span class="text-secondary small fw-normal">(leave blank to keep current)</span></h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Min 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="new_password_confirmation">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-shield-check me-2 text-warning"></i>
                    <h6>Roles</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @php $currentRoles = $user->roles->pluck('name')->toArray(); @endphp
                        @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_{{ $role->name }}"
                                       name="roles[]" value="{{ $role->name }}"
                                       {{ in_array($role->name, old('roles', $currentRoles)) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="role_{{ $role->name }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-2 text-success"></i>
                    <h6>Save Changes</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                    <hr>
                    <small class="text-secondary"><i class="bi bi-clock me-1"></i>Updated {{ $user->updated_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('editUserForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
    });
</script>
@endpush
