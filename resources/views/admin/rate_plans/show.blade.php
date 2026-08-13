@extends('layouts.app')

@section('title', $ratePlan->name . ' — Daily Rates Calendar')
@section('page-title', $ratePlan->name)
@section('breadcrumb', 'Rate Plans › ' . $ratePlan->name)

@section('content')

<div class="page-header">
    <div>
        <h1>{{ $ratePlan->name }}</h1>
        <p><code>{{ $ratePlan->code }}</code> &bull; Currency: {{ $ratePlan->currency }} &bull; Daily Rate Management</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.rate-plans.edit', $ratePlan) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Edit Settings
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Rate Setter -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning-charge me-2 text-warning"></i>
                <h6>Batch Set Daily Rates</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.rate-plans.daily-rates', $ratePlan) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_type_id" required>
                            <option value="">Select Room Type</option>
                            @foreach($ratePlan->roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }} ({{ $rt->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" value="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" value="{{ now()->addDays(30)->toDateString() }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nightly Rate ({{ $ratePlan->currency }}) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0" placeholder="e.g. 150.00" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Apply Rates
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daily Rates Grid Preview -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar3 me-2 text-primary"></i>
                <h6>Next 14 Days Rate Matrix</h6>
            </div>
            <div class="card-body">
                @if($ratePlan->roomTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table text-center table-bordered">
                        <thead>
                            <tr>
                                <th class="text-start">Room Type</th>
                                @for($i = 0; $i < 14; $i++)
                                @php $d = now()->addDays($i); @endphp
                                <th style="min-width: 60px; font-size: 0.7rem;">
                                    <div>{{ $d->format('D') }}</div>
                                    <div class="fw-bold">{{ $d->format('M d') }}</div>
                                </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ratePlan->roomTypes as $rt)
                            <tr>
                                <td class="text-start fw-semibold small">{{ $rt->code }}</td>
                                @for($i = 0; $i < 14; $i++)
                                @php
                                    $dateStr = now()->addDays($i)->toDateString();
                                    $dayRates = $rates->get($rt->id);
                                    $entry = $dayRates ? $dayRates->firstWhere('date', $dateStr) : null;
                                @endphp
                                <td>
                                    @if($entry)
                                    <span class="fw-bold text-success" style="font-size: 0.78rem;">
                                        {{ number_format($entry->amount_minor / 100, 0) }}
                                    </span>
                                    @else
                                    <span class="text-secondary small" style="font-size: 0.7rem;">—</span>
                                    @endif
                                </td>
                                @endfor
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state" style="padding: 30px;">
                    <p class="mb-0">No room types attached to this rate plan yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
