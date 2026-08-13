@extends('layouts.app')

@section('title', 'Pricing Engine & Quote Inspector')
@section('page-title', 'Pricing Engine & Quote Inspector')
@section('breadcrumb', 'Pricing › Quote Inspector')

@section('content')

<div class="page-header">
    <div>
        <h1>Pricing Engine Inspector</h1>
        <p>Simulate pricing calculations, verify surcharges, taxes, and traces for {{ $property->name }}</p>
    </div>
</div>

<div class="row g-4">
    <!-- Test Calculator Form -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calculator me-2 text-primary"></i>
                <h6>Calculate Rate Quote</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.quotes.generate') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_type_id" required>
                            @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }} ({{ $rt->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rate Plan <span class="text-danger">*</span></label>
                        <select class="form-select" name="rate_plan_id" required>
                            @foreach($ratePlans as $rp)
                            <option value="{{ $rp->id }}">{{ $rp->name }} ({{ $rp->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Check-In <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="check_in" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Check-Out <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="check_out" value="{{ now()->addDays(2)->toDateString() }}" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Adults <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="adults" value="2" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Children</label>
                            <input type="number" class="form-control" name="children" value="0" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Promo Code (Optional)</label>
                        <input type="text" class="form-control" name="promo_code" placeholder="e.g. SUMMER20">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-play-circle me-1"></i>Run Pricing Calculation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Output & Trace Inspector -->
    <div class="col-lg-7">
        @if($activeQuote)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 text-white"><i class="bi bi-check-circle me-2"></i>Quote Result & Breakdown</h6>
                <code>{{ $activeQuote->ulid }}</code>
            </div>
            <div class="card-body">
                @php $out = $activeQuote->output; @endphp
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded" style="background: var(--body-bg);">
                    <div>
                        <div class="text-secondary small">Total Stay Cost ({{ $activeQuote->currency }})</div>
                        <div class="fs-2 fw-bold text-primary">
                            {{ number_format($activeQuote->total_minor / 100, 2) }} {{ $activeQuote->currency }}
                        </div>
                        <div class="small text-secondary">{{ $out['nights'] }} night(s) &bull; {{ $out['guests']['adults'] }} Adult(s), {{ $out['guests']['children'] }} Child(ren)</div>
                    </div>
                    <div class="text-end">
                        <div class="small">Subtotal: <strong>{{ number_format($out['subtotal_minor'] / 100, 2) }}</strong></div>
                        <div class="small">Taxes & Fees: <strong>{{ number_format($out['tax_total_minor'] / 100, 2) }}</strong></div>
                    </div>
                </div>

                <!-- Nightly Itemization -->
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Nightly Breakdown</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Base Rate</th>
                                <th>Extra Adult</th>
                                <th>Extra Child</th>
                                <th class="text-end">Night Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($out['nightly'] as $night)
                            <tr>
                                <td>{{ $night['date'] }}</td>
                                <td>{{ number_format($night['base_rate_minor'] / 100, 2) }}</td>
                                <td>{{ number_format($night['extra_adult_minor'] / 100, 2) }}</td>
                                <td>{{ number_format($night['extra_child_minor'] / 100, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($night['night_total_minor'] / 100, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Calculation Trace Log -->
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Calculation Execution Trace</h6>
                <div class="bg-dark text-success p-3 rounded font-monospace small" style="max-height: 180px; overflow-y: auto;">
                    @foreach($activeQuote->trace as $t)
                    <div>&gt; {{ $t }}</div>
                    @endforeach
                    <div>&gt; Calculation completed successfully in minor integer arithmetic.</div>
                </div>
            </div>
        </div>
        @else
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <i class="bi bi-calculator" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h5 class="mt-3">No Active Calculation</h5>
                    <p class="text-secondary">Fill in the test form on the left to inspect step-by-step pricing engine calculations.</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
