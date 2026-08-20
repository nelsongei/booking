@extends('layouts.app')

@section('title', 'Data Migration Importer — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>Property Data Migration Toolkit</h1>
        <p>Import historical guest profiles, reservations, and opening balance records with dry-run validation</p>
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
        <h5 class="m-0 font-weight-bold"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>CSV Migration Upload & Dry-Run Preview</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.migration.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Data Record Type</label>
                    <select name="file_type" class="form-select" required>
                        <option value="guests">Guest Profiles (email, first_name, last_name, phone)</option>
                        <option value="reservations">Historical Reservations (confirmation_number, dates, rates)</option>
                        <option value="rooms">Physical Rooms & Inventory</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Select CSV File</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>
                <div class="col-md-3 pt-4">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-eye me-2"></i>Dry-Run Validate CSV
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
