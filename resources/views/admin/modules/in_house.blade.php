@extends('layouts.app')

@section('title', 'In-House Guests')
@section('page-title', 'In-House Guests')
@section('breadcrumb', 'Front Desk › In-House')

@section('content')

<div class="page-header">
    <div>
        <h1>In-House Guests</h1>
        <p>Currently checked-in guest roster for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-house me-2 text-primary"></i>In-House List</h6>
        <span class="badge bg-primary">0 Active Stays</span>
    </div>

    <div class="card-body text-center py-5">
        <div class="empty-state">
            <i class="bi bi-house" style="font-size: 3rem; color: var(--info);"></i>
            <h5 class="mt-3">No Guests Currently In-House</h5>
            <p class="text-secondary">Guests checked in by front desk staff will appear here with active room assignments and open folios.</p>
        </div>
    </div>
</div>

@endsection
