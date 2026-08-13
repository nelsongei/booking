@extends('layouts.app')

@section('title', 'Guest Folios')
@section('page-title', 'Guest & Master Folios')
@section('breadcrumb', 'Cashiering › Folios')

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Folio Accounts</h1>
        <p class="text-secondary small mb-0">Guest and master accounts ledger for <strong>{{ $property?->name ?: 'All Properties' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.folios.shifts.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-person-workspace me-1"></i> Cashier Shifts
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Folio ULID</th>
                        <th>Primary Guest</th>
                        <th>Confirmation #</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Net Balance</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folios as $fol)
                        @php $res = $fol->reservation; @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.folios.show', $fol) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ Str::limit($fol->ulid, 12) }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $res?->primaryGuest?->fullName ?: 'Guest Account' }}</div>
                                <div class="text-secondary small">{{ $res?->primaryGuest?->email ?: 'No email' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $res?->confirmation_number ?: 'Master' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info text-white text-uppercase">{{ $fol->type }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $fol->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($fol->status) }}</span>
                            </td>
                            <td>
                                @php $bal = $fol->balance_minor; @endphp
                                @if($bal > 0)
                                    <span class="badge bg-danger fs-6">{{ number_format($bal / 100, 2) }} {{ $fol->currency }}</span>
                                @elseif($bal < 0)
                                    <span class="badge bg-success fs-6">{{ number_format($bal / 100, 2) }} {{ $fol->currency }} (Credit)</span>
                                @else
                                    <span class="badge bg-secondary fs-6">0.00 {{ $fol->currency }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.folios.show', $fol) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i>View Ledger
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-5 text-center text-muted">
                                <i class="bi bi-wallet2 fs-1 d-block mb-2 text-secondary"></i>
                                No active folio accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
