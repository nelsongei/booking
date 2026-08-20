<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Request Portal — {{ $reservation->property->name }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f8; padding: 24px 16px; }
        .portal-container { max-width: 500px; margin: 0 auto; }
        .card-custom { background: #ffffff; border-radius: 20px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="portal-container">
    <div class="card-custom text-center">
        <h4 class="fw-extrabold m-0">{{ $reservation->property->name }}</h4>
        <div class="text-muted small mt-1">In-Stay Guest Request Portal &bull; Room {{ $reservation->rooms->first()->room_number ?? 'Suite' }}</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-pill px-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Request Form -->
    <div class="card-custom">
        <h6 class="fw-bold mb-3"><i class="bi bi-send text-primary me-2"></i>Submit New Request</h6>
        <form action="{{ route('guest.request.store', ['token' => $reservation->ulid]) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold">Request Category</label>
                <select name="category" class="form-select" required>
                    <option value="housekeeping">Housekeeping (Towels, Pillows, Toiletries)</option>
                    <option value="maintenance">Maintenance (AC Repair, TV Help, Plumbing)</option>
                    <option value="room_service">Room Service & Dining</option>
                    <option value="concierge">Concierge & Travel Assistance</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Request Details</label>
                <textarea name="details" class="form-control" rows="3" placeholder="Please specify your request..." required></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold">
                <i class="bi bi-send me-2"></i>Send Request to Front Desk
            </button>
        </form>
    </div>

    <!-- Active Requests History -->
    <div class="card-custom">
        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Your Service Requests History</h6>
        @forelse($requests as $req)
            <div class="p-3 bg-light rounded-3 mb-2 d-flex justify-content-between align-items-center border">
                <div>
                    <div class="fw-bold text-capitalize small">{{ str_replace('_', ' ', $req->category) }}</div>
                    <div class="text-muted small">{{ $req->details }}</div>
                </div>
                <span class="badge {{ $req->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }} text-capitalize">
                    {{ $req->status }}
                </span>
            </div>
        @empty
            <div class="text-center py-4 text-muted small">No service requests submitted yet.</div>
        @endforelse
    </div>
</div>

</body>
</html>
