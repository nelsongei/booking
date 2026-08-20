<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete — {{ $reservation->property->name }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f8; padding: 40px 16px; }
        .card-container { max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 24px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
    </style>
</head>
<body>
    <div class="card-container text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-check-lg fs-1"></i>
        </div>

        <h3 class="fw-extrabold text-dark mb-1">You're All Set!</h3>
        <p class="text-muted small mb-4">Pre-arrival digital check-in card for <strong>{{ $reservation->primaryGuest->first_name }}</strong> has been verified.</p>

        <div class="p-3 bg-light rounded-4 border text-start mb-4">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Confirmation Code</span>
                <span class="fw-bold">{{ $reservation->confirmation_number }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Check-in Date</span>
                <span class="fw-bold">{{ $reservation->check_in_date }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted small">Assigned Key Token</span>
                <span class="badge bg-dark text-warning">KEY-BLE-{{ substr($reservation->ulid, -6) }}</span>
            </div>
        </div>

        <a href="{{ route('guest.request.portal', ['token' => $reservation->ulid]) }}" class="btn btn-dark w-100 py-3 rounded-pill fw-bold">
            <i class="bi bi-chat-dots me-2"></i>Open Guest Request Portal
        </a>
    </div>
</body>
</html>
