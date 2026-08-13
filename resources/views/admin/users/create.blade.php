@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Add User')
@section('breadcrumb', 'Users › Add New')

@section('content')

<div class="page-header">
    <div>
        <h1>Add User</h1>
        <p>Create a new staff account and assign roles</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
    @csrf

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
                                   name="name" value="{{ old('name') }}" placeholder="Jane Smith" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" placeholder="jane@hotel.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" placeholder="Min 8 characters" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Repeat password" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Organization <span class="text-danger">*</span></label>
                            <select class="form-select @error('organization_id') is-invalid @enderror"
                                    name="organization_id" required>
                                <option value="">Select Organization</option>
                                @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id', auth()->user()->organization_id) == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Account Status</label>
                            <select class="form-select" name="status">
                                <option value="active"   {{ old('status', 'active') == 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="invited"  {{ old('status') == 'invited'  ? 'selected' : '' }}>Invited</option>
                                <option value="suspended"{{ old('status') == 'suspended'? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-shield-check me-2 text-warning"></i>
                    <h6>Roles</h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">Select one or more roles to assign to this user.</p>
                    <div class="row g-2">
                        @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_{{ $role->name }}"
                                       name="roles[]" value="{{ $role->name }}"
                                       {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="role_{{ $role->name }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-buildings me-2 text-success"></i>
                    <h6>Property Access</h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">Select which properties this user can access.</p>
                    <div class="row g-2">
                        @foreach($properties as $prop)
                        <div class="col-md-6">
                            <div class="form-check d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" id="prop_{{ $prop->id }}"
                                       name="property_ids[]" value="{{ $prop->id }}"
                                       {{ in_array($prop->id, old('property_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="prop_{{ $prop->id }}">
                                    <span class="fw-semibold">{{ $prop->name }}</span>
                                    <span class="text-secondary ms-1 badge bg-light text-dark" style="font-size: 0.65rem;">{{ $prop->code }}</span>
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
                    <i class="bi bi-person-check me-2 text-success"></i>
                    <h6>Create Account</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-person-plus me-2"></i>Create User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('createUserForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
