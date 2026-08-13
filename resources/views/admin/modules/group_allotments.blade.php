@extends('layouts.app')

@section('title', 'Corporate Accounts & Group Allotments')
@section('page-title', 'Corporate Accounts & Group Room Allotments')
@section('breadcrumb', 'Scale › Group Allotments')

@section('content')

<div class="page-header flex-wrap gap-3">
    <div>
        <h1>Corporate Accounts & Group Room Blocks</h1>
        <p>Negotiated rates, credit limits & group room pickup tracking for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCorporateModal">
            <i class="bi bi-building-add me-1"></i> New Corporate Account
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAllotmentModal">
            <i class="bi bi-layer-forward me-1"></i> New Group Allotment
        </button>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="allotmentTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="allotments-tab" data-bs-toggle="tab" data-bs-target="#allotments" type="button">
            <i class="bi bi-layers me-1"></i> Group Room Allotments ({{ $groupAllotments->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="corporate-tab" data-bs-toggle="tab" data-bs-target="#corporate" type="button">
            <i class="bi bi-briefcase me-1"></i> Corporate Accounts ({{ $corporateAccounts->count() }})
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Group Allotments Tab -->
    <div class="tab-pane fade show active" id="allotments" role="tabpanel">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-layers me-2 text-primary"></i>Active Group Room Blocks</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Group Code</th>
                            <th>Group Name</th>
                            <th>Corporate Client</th>
                            <th>Stay Window</th>
                            <th>Rooms Allocated</th>
                            <th>Pickup Progress</th>
                            <th>Negotiated Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupAllotments as $alt)
                            @php
                                $pct = $alt->rooms_allocated > 0 ? round(($alt->rooms_picked_up / $alt->rooms_allocated) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold"><code>{{ $alt->code }}</code></td>
                                <td>{{ $alt->name }}</td>
                                <td>{{ $alt->corporateAccount?->company_name ?: 'Direct Group' }}</td>
                                <td class="small">{{ $alt->start_date->format('M d') }} – {{ $alt->end_date->format('M d, Y') }}</td>
                                <td class="fw-bold text-center">{{ $alt->rooms_allocated }}</td>
                                <td style="min-width: 140px;">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>{{ $alt->rooms_picked_up }} / {{ $alt->rooms_allocated }}</span>
                                        <span>{{ $pct }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                    </div>
                                </td>
                                <td class="fw-bold text-primary">
                                    {{ number_format($alt->negotiated_rate_minor / 100, 2) }} {{ $property?->currency }}
                                </td>
                                <td><span class="badge-status {{ $alt->status }}">{{ ucfirst($alt->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No group allotments created.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Corporate Accounts Tab -->
    <div class="tab-pane fade" id="corporate" role="tabpanel">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Corporate Client Roster</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Corp Code</th>
                            <th>Company Name</th>
                            <th>Tax ID / VAT</th>
                            <th>Contact Person</th>
                            <th>Contact Email</th>
                            <th>Credit Limit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($corporateAccounts as $corp)
                            <tr>
                                <td class="fw-bold"><code>{{ $corp->code }}</code></td>
                                <td class="fw-bold text-primary">{{ $corp->company_name }}</td>
                                <td>{{ $corp->tax_id ?: '—' }}</td>
                                <td>{{ $corp->contact_name ?: '—' }}</td>
                                <td>{{ $corp->contact_email ?: '—' }}</td>
                                <td class="fw-semibold">
                                    {{ number_format($corp->credit_limit_minor / 100, 2) }} {{ $property?->currency }}
                                </td>
                                <td><span class="badge-status {{ $corp->status }}">{{ ucfirst($corp->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No corporate accounts registered.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Corporate Account -->
<div class="modal fade" id="newCorporateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.corporate-accounts.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2 text-primary"></i>New Corporate Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" required placeholder="e.g. Acme Corporation">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Corporate Code</label>
                        <input type="text" name="code" class="form-control" required placeholder="ACME01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Credit Limit ({{ $property?->currency }})</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" required value="5000.00">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_name" class="form-control" placeholder="John Smith">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" placeholder="john@acme.com">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: New Group Allotment -->
<div class="modal fade" id="newAllotmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.group-allotments.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-layer-forward me-2 text-primary"></i>New Group Room Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Group Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Tech Conference 2026">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Group Code</label>
                        <input type="text" name="code" class="form-control" required placeholder="GRP-TECH26">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Corporate Account (Optional)</label>
                        <select name="corporate_account_id" class="form-select">
                            <option value="">-- Direct Group --</option>
                            @foreach($corporateAccounts as $crp)
                                <option value="{{ $crp->id }}">{{ $crp->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required value="{{ now()->addDays(3)->toDateString() }}">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rooms Allocated</label>
                        <input type="number" name="rooms_allocated" class="form-control" required value="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Negotiated Nightly Rate ({{ $property?->currency }})</label>
                        <input type="number" step="0.01" name="negotiated_rate" class="form-control" required value="120.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Allocate Block</button>
            </div>
        </form>
    </div>
</div>

@endsection
