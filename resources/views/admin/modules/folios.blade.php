@extends('layouts.app')

@section('title', 'Guest Folios')
@section('page-title', 'Folios & Cashiering')
@section('breadcrumb', 'Operations › Folios')

@section('content')

<div class="page-header">
    <div>
        <h1>Guest Folios</h1>
        <p>Guest accounting, line-item charges, and billing settlement for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Active Folios</h6>
        <span class="badge bg-light text-dark border">Phase 8 Ledger Engine</span>
    </div>

    <div class="card-body text-center py-5">
        <div class="empty-state">
            <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: var(--info);"></i>
            <h5 class="mt-3">Double-Entry Folio Engine</h5>
            <p class="text-secondary" style="max-width: 520px; margin: 0 auto;">
                The append-only folio transaction ledger tables (`folio_accounts`, `folio_windows`, `folio_transactions`, `cashier_shifts`) are fully migrated.
                Live posting, reversals, payments, and PDF invoicing will operate seamlessly here.
            </p>
        </div>
    </div>
</div>

@endsection
