@extends('layouts.app')

@section('title', $property->name . ' — Property')
@section('page-title', $property->name)
@section('breadcrumb', 'Properties › ' . $property->name)

@section('content')

<div class="page-header">
    <div>
        <h1>{{ $property->name }}</h1>
        <p>{{ $property->city ? $property->city . ', ' . $property->country : $property->country }} &bull; <code>{{ $property->code }}</code></p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.switch.property') }}">
            @csrf
            <input type="hidden" name="property_id" value="{{ $property->id }}">
            <button type="submit" class="btn btn-outline-success">
                <i class="bi bi-arrow-right-circle me-1"></i>Switch to This Property
            </button>
        </form>
        <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit Property
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- Property Details -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-building me-2 text-primary"></i>
                <h6>Property Details</h6>
                <span class="badge-status {{ $property->status }} ms-auto">{{ ucfirst($property->status) }}</span>
            </div>
            <div class="card-body">
                <dl class="mb-0" style="font-size: 0.85rem;">
                    <dt class="text-secondary">Code</dt>
                    <dd class="fw-semibold mb-2"><code>{{ $property->code }}</code></dd>

                    <dt class="text-secondary">Type</dt>
                    <dd class="fw-semibold mb-2 text-capitalize">{{ str_replace('_', ' ', $property->type) }}</dd>

                    <dt class="text-secondary">Organization</dt>
                    <dd class="fw-semibold mb-2">{{ $property->organization?->name }}</dd>

                    <dt class="text-secondary">Stars</dt>
                    <dd class="mb-2">{{ $property->star_rating ? str_repeat('★', $property->star_rating) : '—' }}</dd>

                    <dt class="text-secondary">Currency</dt>
                    <dd class="fw-semibold mb-2">{{ $property->currency }}</dd>

                    <dt class="text-secondary">Timezone</dt>
                    <dd class="fw-semibold mb-2">{{ $property->timezone }}</dd>

                    <dt class="text-secondary">Check-In / Check-Out</dt>
                    <dd class="fw-semibold mb-2">{{ $property->getCheckInTime() }} / {{ $property->getCheckOutTime() }}</dd>

                    <dt class="text-secondary">Email</dt>
                    <dd class="mb-2">{{ $property->email ?: '—' }}</dd>

                    <dt class="text-secondary">Phone</dt>
                    <dd class="mb-2">{{ $property->phone ?: '—' }}</dd>

                    <dt class="text-secondary">Booking Engine</dt>
                    <dd class="mb-0">
                        <span class="badge-status {{ $property->booking_engine_enabled ? 'active' : 'inactive' }}">
                            {{ $property->booking_engine_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="col-md-8">
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-grid-1x2"></i></div>
                    <div>
                        <div class="stat-number">{{ $roomTypeCount }}</div>
                        <div class="stat-label">Room Types</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-door-closed"></i></div>
                    <div>
                        <div class="stat-number">{{ $roomCount }}</div>
                        <div class="stat-label">Physical Rooms</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Active Reservations</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Checklist -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-check me-2 text-success"></i>
                <h6>Setup Checklist</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    @php
                        $checklistItems = [
                            ['label' => 'Property Created',         'done' => true,            'href' => null],
                            ['label' => 'Room Types Configured',    'done' => $roomTypeCount > 0, 'href' => '#'],
                            ['label' => 'Physical Rooms Added',     'done' => $roomCount > 0,  'href' => '#'],
                            ['label' => 'Rate Plans Set Up',        'done' => false,           'href' => '#'],
                            ['label' => 'Taxes & Fees Configured',  'done' => false,           'href' => '#'],
                            ['label' => 'Cancellation Policies',    'done' => false,           'href' => '#'],
                            ['label' => 'Booking Engine Enabled',   'done' => $property->booking_engine_enabled, 'href' => route('admin.properties.edit', $property)],
                        ];
                    @endphp

                    @foreach($checklistItems as $item)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                            background: {{ $item['done'] ? 'rgba(16,185,129,0.15)' : 'rgba(229,231,235,0.5)' }};
                            color: {{ $item['done'] ? '#059669' : '#9ca3af' }}; font-size: 0.75rem;">
                            <i class="bi bi-{{ $item['done'] ? 'check-lg' : 'circle' }}"></i>
                        </div>
                        <span class="flex-fill {{ $item['done'] ? 'text-success fw-semibold' : '' }}" style="font-size: 0.875rem;">
                            {{ $item['label'] }}
                        </span>
                        @if(!$item['done'] && $item['href'])
                        <a href="{{ $item['href'] }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                            Configure
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
