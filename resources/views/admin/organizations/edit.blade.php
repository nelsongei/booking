@extends('layouts.app')

@section('title', 'Edit — ' . $organization->name)
@section('page-title', 'Edit Organization')
@section('breadcrumb', 'Organizations › ' . $organization->name . ' › Edit')

@section('content')

<div class="page-header">
    <div>
        <h1>Edit: {{ $organization->name }}</h1>
        <p>Update organization details and settings</p>
    </div>
    <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.organizations.update', $organization) }}" id="editOrgForm">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-building me-2 text-primary"></i>
                    <h6>Organization Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="name">Organization Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $organization->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                @foreach(['active', 'suspended', 'trial'] as $s)
                                <option value="{{ $s }}" {{ old('status', $organization->status) == $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Legal Name</label>
                            <input type="text" class="form-control" name="legal_name"
                                   value="{{ old('legal_name', $organization->legal_name) }}"  placeholder="Registered company name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Default Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="default_currency" required>
                                @php $currencies = ['USD','EUR','GBP','KES','UGX','TZS','ZAR','NGN','AED','AUD','CAD']; @endphp
                                @foreach($currencies as $c)
                                <option value="{{ $c }}" {{ old('default_currency', $organization->default_currency) == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Timezone <span class="text-danger">*</span></label>
                            <select class="form-select" name="default_timezone" required>
                                @foreach(DateTimeZone::listIdentifiers() as $tz)
                                <option value="{{ $tz }}" {{ old('default_timezone', $organization->default_timezone) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Locale <span class="text-danger">*</span></label>
                            <select class="form-select" name="default_locale" required>
                                @foreach(['en' => 'English', 'en-GB' => 'English UK', 'fr' => 'French', 'de' => 'German', 'es' => 'Spanish', 'sw' => 'Swahili'] as $code => $label)
                                <option value="{{ $code }}" {{ old('default_locale', $organization->default_locale) == $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country" maxlength="2"
                                   value="{{ old('country', $organization->country) }}"
                                   style="text-transform: uppercase;" placeholder="US">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   value="{{ old('email', $organization->email) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone"
                                   value="{{ old('phone', $organization->phone) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website"
                                   value="{{ old('website', $organization->website) }}">
                        </div>
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
                        <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                    <hr>
                    <small class="text-secondary">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: {{ $organization->updated_at->diffForHumans() }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('editOrgForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
    });
</script>
@endpush
