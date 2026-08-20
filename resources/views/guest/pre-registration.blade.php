<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Pre-Arrival Registration — {{ $reservation->property->name }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f6f8;
            color: #1a202c;
            padding-bottom: 40px;
        }

        .mobile-card-container {
            max-width: 520px;
            margin: 0 auto;
        }

        .brand-header-box {
            background: linear-gradient(135deg, #151b16 0%, #2d382e 100%);
            color: #ffffff;
            border-radius: 0 0 24px 24px;
            padding: 32px 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .guest-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            padding: 24px;
            margin-top: 20px;
        }

        .signature-canvas {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            background: #fafafa;
            width: 100%;
            height: 140px;
            touch-action: none;
        }

        .upsell-pill-box {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .upsell-pill-box:hover {
            border-color: #151b16;
            background: #f8faf8;
        }

        .btn-guest-submit {
            background: #d6f843;
            color: #151b16;
            font-weight: 800;
            border: none;
            border-radius: 50rem;
            padding: 14px;
            font-size: 1rem;
            width: 100%;
            box-shadow: 0 4px 14px rgba(214, 248, 67, 0.4);
        }
    </style>
</head>
<body>

<div class="mobile-card-container">
    <!-- Brand Header -->
    <div class="brand-header-box text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-white text-dark rounded-circle mb-2" style="width: 44px; height: 44px;">
            <i class="bi bi-buildings-fill fs-5"></i>
        </div>
        <h4 class="fw-extrabold m-0 text-white">{{ $reservation->property->name }}</h4>
        <div class="text-white-50 small mt-1">Pre-Arrival Digital Check-In Card</div>

        <!-- Language Selector -->
        <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="?lang=en" class="badge {{ $lang === 'en' ? 'bg-warning text-dark' : 'bg-secondary text-white' }} text-decoration-none">EN</a>
            <a href="?lang=sw" class="badge {{ $lang === 'sw' ? 'bg-warning text-dark' : 'bg-secondary text-white' }} text-decoration-none">SW (Swahili)</a>
            <a href="?lang=fr" class="badge {{ $lang === 'fr' ? 'bg-warning text-dark' : 'bg-secondary text-white' }} text-decoration-none">FR</a>
            <a href="?lang=de" class="badge {{ $lang === 'de' ? 'bg-warning text-dark' : 'bg-secondary text-white' }} text-decoration-none">DE</a>
        </div>
    </div>

    <!-- Booking Summary Badge -->
    <div class="guest-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small fw-bold">CONFIRMATION #</span>
            <span class="badge bg-light text-dark border fw-extrabold">{{ $reservation->confirmation_number }}</span>
        </div>
        <h5 class="fw-extrabold m-0 text-dark">{{ $reservation->primaryGuest->first_name }} {{ $reservation->primaryGuest->last_name }}</h5>
        <div class="text-muted small mt-1">
            <i class="bi bi-calendar-check me-1"></i>Check-in: {{ $reservation->check_in_date }} &bull; {{ $reservation->rooms->first()->roomType->name ?? 'Suite' }}
        </div>
    </div>

    <!-- Registration Form -->
    <form action="{{ route('guest.pre-registration.store', ['token' => $reservation->ulid]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Identity & Arrival Info Card -->
        <div class="guest-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-badge text-primary me-2"></i>Passport & Arrival Details</h6>
            
            <div class="mb-3">
                <label class="form-label small fw-bold">Passport / National ID Number</label>
                <input type="text" name="passport_number" class="form-control" placeholder="e.g. A12345678" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Nationality</label>
                <input type="text" name="nationality" class="form-control" value="Kenyan" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Estimated Time of Arrival (ETA)</label>
                <select name="expected_arrival_time" class="form-select" required>
                    <option value="14:00 - 16:00">14:00 - 16:00 (Standard Check-in)</option>
                    <option value="12:00 - 14:00">12:00 - 14:00 (Early Arrival)</option>
                    <option value="16:00 - 18:00">16:00 - 18:00</option>
                    <option value="18:00 - 22:00">18:00 - 22:00 (Late Check-in)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Upload Passport / ID Document (Secure Encrypted Storage)</label>
                <input type="file" name="passport_document" class="form-control" accept="image/*,.pdf">
                <div class="text-muted mt-1" style="font-size: 0.72rem;"><i class="bi bi-shield-lock text-success me-1"></i>Encrypted according to privacy retention rules (Auto-purged 30 days post checkout).</div>
            </div>
        </div>

        <!-- Enhance Stay Upsells Card -->
        <div class="guest-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-stars text-warning me-2"></i>Enhance Your Stay (Optional Upsells)</h6>

            <div class="mb-2">
                <label class="upsell-pill-box d-flex justify-content-between align-items-center w-100">
                    <div>
                        <div class="fw-bold text-dark small">Early Check-in (12:00 PM)</div>
                        <div class="text-muted small">Guaranteed early room access</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark fw-bold">$30.00</span>
                        <input type="checkbox" name="upsells[early_checkin]" value="3000" class="form-check-input">
                    </div>
                </label>
            </div>

            <div class="mb-2">
                <label class="upsell-pill-box d-flex justify-content-between align-items-center w-100">
                    <div>
                        <div class="fw-bold text-dark small">VIP Airport Transfer</div>
                        <div class="text-muted small">Chauffeur pick-up from Airport</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark fw-bold">$50.00</span>
                        <input type="checkbox" name="upsells[airport_transfer]" value="5000" class="form-check-input">
                    </div>
                </label>
            </div>

            <div class="mb-2">
                <label class="upsell-pill-box d-flex justify-content-between align-items-center w-100">
                    <div>
                        <div class="fw-bold text-dark small">Buffet Breakfast Package</div>
                        <div class="text-muted small">Daily international buffet</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark fw-bold">$25.00</span>
                        <input type="checkbox" name="upsells[buffet_breakfast]" value="2500" class="form-check-input">
                    </div>
                </label>
            </div>
        </div>

        <!-- Terms & Digital Signature Card -->
        <div class="guest-card mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-pen text-primary me-2"></i>Terms Consent & Digital Signature</h6>

            <div class="mb-3">
                <label class="form-label small fw-bold">Sign Below using Finger or Touch Screen</label>
                <canvas id="signatureCanvas" class="signature-canvas"></canvas>
                <input type="hidden" name="digital_signature" id="signatureInput">
                <div class="text-end mt-1">
                    <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0" onclick="clearSignature()">Clear Signature</button>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" name="terms_consented" id="termsCheck" class="form-check-input" required>
                <label class="form-check-label small text-muted" for="termsCheck">
                    I agree to the property terms, guest house rules, and privacy data processing policy.
                </label>
            </div>
        </div>

        <button type="submit" class="btn-guest-submit" onclick="prepareSubmit(event)">
            <i class="bi bi-check-circle-fill me-2"></i>Complete Pre-Registration
        </button>
    </form>
</div>

<script>
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    ctx.strokeStyle = '#151b16';
    ctx.lineWidth = 2.5;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
            y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
        };
    }

    canvas.addEventListener('mousedown', e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove', e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup', () => drawing = false);
    canvas.addEventListener('touchstart', e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); e.preventDefault(); });
    canvas.addEventListener('touchmove', e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); e.preventDefault(); });
    canvas.addEventListener('touchend', () => drawing = false);

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function prepareSubmit(e) {
        document.getElementById('signatureInput').value = canvas.toDataURL();
    }
</script>
</body>
</html>
