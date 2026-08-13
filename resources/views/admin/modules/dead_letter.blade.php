@extends('layouts.app')

@section('title', 'Dead-Letter Queue')
@section('page-title', 'Dead-Letter Queue & Error Recovery')
@section('breadcrumb', 'Integrations › Dead-Letter Queue')

@section('content')

<div class="page-header flex-wrap gap-3">
    <div>
        <h1>Dead-Letter Error Queue</h1>
        <p>Inspect, replay, and manage failed inbound webhooks or job processing payloads</p>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }}" href="{{ route('admin.dead-letter.index', ['status' => 'all']) }}">
            All Items
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}" href="{{ route('admin.dead-letter.index', ['status' => 'pending']) }}">
            Pending Errors
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statusFilter === 'resolved' ? 'active' : '' }}" href="{{ route('admin.dead-letter.index', ['status' => 'resolved']) }}">
            Resolved / Replayed
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statusFilter === 'discarded' ? 'active' : '' }}" href="{{ route('admin.dead-letter.index', ['status' => 'discarded']) }}">
            Discarded
        </a>
    </li>
</ul>

<!-- Dead Letter Items Roster -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="fw-bold mb-0"><i class="bi bi-bug me-2 text-danger"></i>Failed Event Queue</h6>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Source</th>
                    <th>Failure Reason</th>
                    <th>Attempts</th>
                    <th>Status</th>
                    <th>Logged At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deadLetterItems as $item)
                    <tr>
                        <td class="fw-bold"><code>#{{ $item->id }}</code></td>
                        <td><span class="badge bg-light text-dark border">{{ $item->source }}</span></td>
                        <td class="text-danger small fw-medium">{{ Str::limit($item->reason, 45) }}</td>
                        <td><span class="badge bg-secondary">{{ $item->attempts }}</span></td>
                        <td>
                            @if($item->status === 'pending')
                                <span class="badge bg-danger">Pending</span>
                            @elseif($item->status === 'resolved' || $item->status === 'replayed')
                                <span class="badge bg-success">Resolved</span>
                            @else
                                <span class="badge bg-secondary">Discarded</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <!-- Inspect Payload Button -->
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#payloadModal_{{ $item->id }}">
                                    <i class="bi bi-code-slash"></i> Payload
                                </button>

                                @if($item->status === 'pending')
                                    <!-- Replay Form -->
                                    <form method="POST" action="{{ route('admin.dead-letter.replay', $item) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-arrow-repeat me-1"></i> Replay
                                        </button>
                                    </form>

                                    <!-- Discard Form -->
                                    <form method="POST" action="{{ route('admin.dead-letter.discard', $item) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Discard this item?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Payload Inspection Modal -->
                    <div class="modal fade" id="payloadModal_{{ $item->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-code-square me-2 text-primary"></i>Inspect Payload #{{ $item->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Failure Reason:</label>
                                        <div class="alert alert-danger p-2 small mb-0">{{ $item->reason }}</div>
                                    </div>

                                    <div>
                                        <label class="form-label fw-bold">JSON Payload Data:</label>
                                        <pre class="bg-dark text-white p-3 rounded small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($item->payload, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    @if($item->status === 'pending')
                                        <form method="POST" action="{{ route('admin.dead-letter.replay', $item) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success"><i class="bi bi-arrow-repeat me-1"></i> Replay Payload</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No dead-letter error items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($deadLetterItems->hasPages())
        <div class="card-footer bg-light">
            {{ $deadLetterItems->links() }}
        </div>
    @endif
</div>

@endsection
