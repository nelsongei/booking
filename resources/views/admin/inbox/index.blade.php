@extends('layouts.app')

@section('title', 'Unified Guest Inbox — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>Unified Communications Inbox</h1>
        <p>Omnichannel team communications across Email, SMS, and WhatsApp Business</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 font-weight-bold"><i class="bi bi-chat-dots me-2 text-primary"></i>Active Guest Communications</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Confirmation Code</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th class="text-end">Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->primaryGuest->first_name ?? 'Guest' }} {{ $item->primaryGuest->last_name ?? '' }}</td>
                            <td><code>{{ $item->confirmation_number }}</code></td>
                            <td><span class="badge bg-success"><i class="bi bi-whatsapp me-1"></i>WhatsApp / SMS</span></td>
                            <td><span class="badge bg-light text-dark">Active Journey</span></td>
                            <td class="text-end text-muted">{{ $item->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No active guest messages in inbox.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
