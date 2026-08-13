<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Direct — {{ $property->name }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr Range Calendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            --brand-primary: {{ $property->getPrimaryColor() }};
            --brand-dark: {{ $property->getDarkColor() }};
            --brand-accent: {{ $property->getAccentColor() }};
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: transparent;
            margin: 0;
            padding: 12px;
        }

        .widget-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 20px;
        }

        .btn-widget {
            background: var(--brand-primary);
            color: #ffffff;
            font-weight: 700;
            border: none;
            border-radius: 50rem;
            padding: 12px 28px;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 0.88rem;
        }

        .btn-widget:hover {
            background: var(--brand-dark);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }
    </style>
</head>
<body>

<div class="widget-card">
    <form action="{{ route('booking.search', ['slug' => $property->slug]) }}" method="GET" target="_parent">
        <div class="row g-3 align-items-center">
            <!-- Property Header / Logo -->
            <div class="col-md-3 col-sm-12 d-flex align-items-center gap-3">
                <img src="{{ $property->getLogoUrl('dark') }}" alt="{{ $property->name }}" style="max-height: 40px; width: auto; object-fit: contain;">
                <div class="d-none d-md-block">
                    <div class="fw-bold text-dark small" style="line-height: 1.2;">{{ $property->name }}</div>
                    <small class="text-muted" style="font-size: 0.72rem;"><i class="fa-solid fa-shield-cat text-success me-1"></i> Best Rate Guarantee</small>
                </div>
            </div>

            <!-- Date Range -->
            <div class="col-md-4 col-sm-6">
                <label class="form-label text-muted fw-bold small mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Travel Dates</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-calendar-days text-secondary"></i></span>
                    <input type="text" id="widgetDateRange" class="form-control border-start-0 ps-0 bg-white" placeholder="Select check-in & out" readonly>
                </div>
                <input type="hidden" name="check_in" id="wCheckIn" value="{{ $checkIn }}">
                <input type="hidden" name="check_out" id="wCheckOut" value="{{ $checkOut }}">
            </div>

            <!-- Occupancy -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label text-muted fw-bold small mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Guests</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-group text-secondary"></i></span>
                    <select class="form-select border-start-0 ps-0" name="adults">
                        <option value="1" {{ $adults == 1 ? 'selected' : '' }}>1 Adult</option>
                        <option value="2" {{ $adults == 2 ? 'selected' : '' }}>2 Adults</option>
                        <option value="3" {{ $adults == 3 ? 'selected' : '' }}>3 Adults</option>
                        <option value="4" {{ $adults == 4 ? 'selected' : '' }}>4 Adults</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="col-md-2 col-sm-12 text-end">
                <button type="submit" class="btn btn-widget w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Book Now
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#widgetDateRange", {
                mode: "range",
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: ["{{ $checkIn }}", "{{ $checkOut }}"],
                altInput: true,
                altFormat: "j M Y",
                altInputClass: "form-control border-start-0 ps-0 bg-white fw-bold text-dark",
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates && selectedDates.length === 2) {
                        document.getElementById('wCheckIn').value = instance.formatDate(selectedDates[0], "Y-m-d");
                        document.getElementById('wCheckOut').value = instance.formatDate(selectedDates[1], "Y-m-d");
                    }
                }
            });
        }
    });
</script>
</body>
</html>
