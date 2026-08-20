@extends('layouts.app')

@section('title', 'Digital Guest Journey Roster — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>Digital Guest Journey Roster</h1>
        <p>Monitor pre-arrival digital registration card completion, ID document retention expirations, and staff interventions</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 font-weight-bold"><i class="bi bi-person-vcard me-2 text-primary"></i>Pre-Arrival Registration Records</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Confirmation Code</th>
                        <th>Passport / ID #</th>
                        <th>Expected Arrival (ETA)</th>
                        <th>Terms & Signature</th>
                        <th>ID Retention Until</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td class="fw-bold">{{ $reg->first_name }} {{ $reg->last_name }}</td>
                            <td><code>{{ $reg->confirmation_number }}</code></td>
                            <td>{{ $reg->passport_number ?? 'N/A' }} ({{ $reg->nationality ?? 'Kenyan' }})</td>
                            <td><span class="badge bg-light text-dark"><i class="bi bi-clock me-1"></i>{{ $reg->expected_arrival_time ?? '14:00' }}</span></td>
                            <td>
                                @if($reg->terms_consented_at)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Signed & Consented</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border" title="Automatic Privacy Retention Rule">
                                    <i class="bi bi-shield-lock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($reg->id_retention_until)->format('d M Y') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-success px-3 py-2 rounded-pill fw-bold">Pre-Checked In</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No digital pre-registration records submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection
