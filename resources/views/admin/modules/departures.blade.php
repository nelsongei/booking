@extends('layouts.app')

@section('title', 'Departures')
@section('page-title', 'Today\'s Expected Departures')
@section('breadcrumb', 'Front Desk › Departures')

@section('content')

<div class="page-header">
    <div>
        <h1>Expected Departures</h1>
        <p>Guests checking out today at {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-box-arrow-right me-2 text-warning"></i>Departures List</h6>
        <span class="badge bg-warning text-dark">0 Pending Checkout</span>
    </div>

    <div class="card-body text-center py-5">
        <div class="empty-state">
            <i class="bi bi-box-arrow-right" style="font-size: 3rem; color: var(--warning);"></i>
            <h5 class="mt-3">No Departures Due Today</h5>
            <p class="text-secondary">Checked-in stays scheduled for departure today will appear here for folio settlement and room release.</p>
        </div>
    </div>
</div>

@endsection
