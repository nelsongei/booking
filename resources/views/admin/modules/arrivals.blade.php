@extends('layouts.app')

@section('title', 'Arrivals')
@section('page-title', 'Today\'s Expected Arrivals')
@section('breadcrumb', 'Front Desk › Arrivals')

@section('content')

<div class="page-header">
    <div>
        <h1>Expected Arrivals</h1>
        <p>Guests arriving today at {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-box-arrow-in-right me-2 text-success"></i>Arrivals List</h6>
        <span class="badge bg-success">0 Expected Today</span>
    </div>

    <div class="card-body text-center py-5">
        <div class="empty-state">
            <i class="bi bi-box-arrow-in-right" style="font-size: 3rem; color: var(--success);"></i>
            <h5 class="mt-3">No Arrivals Pending Today</h5>
            <p class="text-secondary">Expected arrival records will populate automatically when guest reservations are checked in.</p>
        </div>
    </div>
</div>

@endsection
