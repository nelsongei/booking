@extends('layouts.app')

@section('title', 'Folio Ledger — ' . ($folio->reservation?->confirmation_number ?: $folio->ulid))
@section('page-title', 'Folio Ledger Statement')
@section('breadcrumb', 'Folios › ' . ($folio->reservation?->confirmation_number ?: $folio->ulid))

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Folio Statement</h1>
        <p class="text-secondary small mb-0">Guest: <strong>{{ $folio->reservation?->primaryGuest?->fullName ?: 'Master Folio' }}</strong> &bull; Currency: <code>{{ $folio->currency }}</code></p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#postPaymentModal">
            <i class="bi bi-cash-stack me-1"></i>Post Payment
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#postChargeModal">
            <i class="bi bi-plus-circle me-1"></i>Post Charge
        </button>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-printer me-1"></i>Print Statement
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-9">
        <!-- Folio Ledger Statement Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <ul class="nav nav-pills card-header-pills">
                    @foreach($folio->windows as $win)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" href="#window{{ $win->id }}" data-bs-toggle="tab">
                                <i class="bi bi-folder2-open me-1"></i>Window {{ $win->window_number }} ({{ $win->name }})
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div>
                    @php $bal = $folio->balance_minor; @endphp
                    <span class="text-secondary small me-2">Net Balance:</span>
                    <span class="badge {{ $bal > 0 ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-2 rounded-pill">
                        {{ number_format($bal / 100, 2) }} {{ $folio->currency }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Type</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Posted By</th>
                                <th class="text-end">Charge (+)</th>
                                <th class="text-end pe-4">Payment (-)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($folio->transactions as $tx)
                                <tr class="{{ $tx->type === 'reversal' ? 'table-warning' : '' }}">
                                    <td class="ps-4 text-secondary small">{{ $tx->posted_at->format('M d, H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $tx->type === 'charge' ? 'bg-danger text-white' : ($tx->type === 'payment' ? 'bg-success text-white' : 'bg-warning text-dark') }}">
                                            {{ ucfirst($tx->type) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold"><code>{{ $tx->chargeCode?->code ?: 'PMT' }}</code></td>
                                    <td>
                                        {{ $tx->description }}
                                        @if($tx->reversal_reason)
                                            <div class="small text-danger">Reversal Reason: {{ $tx->reversal_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="text-secondary small">{{ $tx->postedBy?->name ?: 'System' }}</td>

                                    <!-- Charge Column -->
                                    <td class="text-end fw-bold text-danger">
                                        @if($tx->amount_minor > 0)
                                            +{{ number_format($tx->amount_minor / 100, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <!-- Payment Column -->
                                    <td class="text-end pe-4 fw-bold text-success">
                                        @if($tx->amount_minor < 0)
                                            {{ number_format($tx->amount_minor / 100, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="text-end pe-3">
                                        @if($tx->type !== 'reversal')
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" data-bs-toggle="modal" data-bs-target="#reverseModal{{ $tx->id }}" title="Reverse Item">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Reversal Modal -->
                                <div class="modal fade" id="reverseModal{{ $tx->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.folios.reverse', $tx) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i>Reverse Transaction</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-secondary small">This action will append an exact inverse transaction ({{ number_format(-$tx->amount_minor / 100, 2) }} {{ $tx->currency }}) to correct the folio ledger balance without mutating history.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Reversal Reason <span class="text-danger">*</span></label>
                                                        <input type="text" name="reason" class="form-control" placeholder="e.g. Billing error, discount applied, duplicate post" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger"><i class="bi bi-check-circle me-1"></i>Confirm Reversal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-5 text-center text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                                        No ledger transactions posted to this folio account yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Summary -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <h6 class="fw-bold mb-3">Account Summary</h6>
            <div class="d-flex justify-content-between py-2 border-bottom small">
                <span class="text-muted">Total Charges</span>
                <span class="fw-bold text-danger">{{ number_format($folio->transactions->where('amount_minor', '>', 0)->sum('amount_minor') / 100, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom small">
                <span class="text-muted">Total Payments</span>
                <span class="fw-bold text-success">{{ number_format(abs($folio->transactions->where('amount_minor', '<', 0)->sum('amount_minor')) / 100, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-3">
                <span class="fw-bold">Net Balance</span>
                <span class="fw-bold fs-5 {{ $bal > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($bal / 100, 2) }} {{ $folio->currency }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Post Charge Modal -->
<div class="modal fade" id="postChargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.folios.charge', $folio) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Post Charge to Folio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Charge Category <span class="text-danger">*</span></label>
                        <select name="charge_code_id" class="form-select" required>
                            @foreach($chargeCodes as $cc)
                                <option value="{{ $cc->id }}">{{ $cc->code }} — {{ $cc->name }} ({{ ucfirst($cc->category) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount ({{ $folio->currency }}) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" placeholder="0.00" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Restaurant bill #402, Spa package, Laundry" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Post Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Post Payment Modal -->
<div class="modal fade" id="postPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.folios.payment', $folio) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="bi bi-cash-stack me-2"></i>Post Payment to Folio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Amount ({{ $folio->currency }}) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" value="{{ number_format(max(0, $folio->balance_minor) / 100, 2, '.', '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="provider" class="form-select">
                            <option value="cash">Cash Payment</option>
                            <option value="pos_terminal">POS Card Terminal</option>
                            <option value="stripe">Stripe Online</option>
                            <option value="bank_transfer">Bank Wire</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Reference / Receipt #</label>
                        <input type="text" name="reference" class="form-control" placeholder="e.g. POS-9981">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Post Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
