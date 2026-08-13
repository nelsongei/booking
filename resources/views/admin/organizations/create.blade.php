@extends('layouts.app')

@section('title', 'Create Organization')
@section('page-title', 'New Organization')
@section('breadcrumb', 'Organizations › Create')

@section('content')

<div class="page-header">
    <div>
        <h1>New Organization</h1>
        <p>Add a hotel group or property portfolio</p>
    </div>
    <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.organizations.store') }}" id="createOrgForm">
    @csrf

    <div class="row g-4">
        <!-- Basic Info -->
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
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g. Tembo Hotels Group" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="slug">URL Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug" value="{{ old('slug') }}"
                                   placeholder="Auto-generated">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="legal_name">Legal Name</label>
                            <input type="text" class="form-control @error('legal_name') is-invalid @enderror"
                                   id="legal_name" name="legal_name" value="{{ old('legal_name') }}"
                                   placeholder="Registered company name">
                            @error('legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="tax_identifier">Tax / VAT Number</label>
                            <input type="text" class="form-control"
                                   id="tax_identifier" name="tax_identifier" value="{{ old('tax_identifier') }}"
                                   placeholder="Optional">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email">Contact Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="admin@hotelgroup.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" class="form-control"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="+1 555 000 0000">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="website">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror"
                                   id="website" name="website" value="{{ old('website') }}"
                                   placeholder="https://hotelgroup.com">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-globe me-2 text-primary"></i>
                    <h6>Regional Defaults</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="default_currency">Default Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('default_currency') is-invalid @enderror"
                                    id="default_currency" name="default_currency" required>
                                @php
                                    $currencies = ['USD' => 'US Dollar', 'EUR' => 'Euro', 'GBP' => 'British Pound', 'KES' => 'Kenyan Shilling', 'UGX' => 'Ugandan Shilling', 'TZS' => 'Tanzanian Shilling', 'ZAR' => 'South African Rand', 'NGN' => 'Nigerian Naira', 'GHS' => 'Ghanaian Cedi', 'AED' => 'UAE Dirham', 'AUD' => 'Australian Dollar', 'CAD' => 'Canadian Dollar'];
                                @endphp
                                @foreach($currencies as $code => $label)
                                <option value="{{ $code }}" {{ old('default_currency', 'USD') == $code ? 'selected' : '' }}>
                                    {{ $code }} — {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('default_currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="default_timezone">Timezone <span class="text-danger">*</span></label>
                            <select class="form-select @error('default_timezone') is-invalid @enderror"
                                    id="default_timezone" name="default_timezone" required>
                                @foreach(DateTimeZone::listIdentifiers() as $tz)
                                <option value="{{ $tz }}" {{ old('default_timezone', 'UTC') == $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                                @endforeach
                            </select>
                            @error('default_timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="default_locale">Locale <span class="text-danger">*</span></label>
                            <select class="form-select" id="default_locale" name="default_locale" required>
                                <option value="en" {{ old('default_locale', 'en') == 'en' ? 'selected' : '' }}>English (en)</option>
                                <option value="en-GB">English UK (en-GB)</option>
                                <option value="fr">French (fr)</option>
                                <option value="de">German (de)</option>
                                <option value="es">Spanish (es)</option>
                                <option value="ar">Arabic (ar)</option>
                                <option value="sw">Swahili (sw)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                   value="{{ old('country') }}" placeholder="2-letter code e.g. US" maxlength="2">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-check2-square me-2 text-success"></i>
                    <h6>Save Organization</h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">Create this organization to start adding properties and users.</p>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-lg me-2"></i>Create Organization
                        </button>
                        <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slug = document.getElementById('slug');
        if (!slug.dataset.manual) {
            slug.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manual = 'true';
    });

    // Loading state on submit
    document.getElementById('createOrgForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        btn.disabled = true;
    });
</script>
@endpush
