<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --obsidian: #0b0f17;
            --card-bg: #151c28;
            --gold-accent: #ccf235;
        }
        body {
            background-color: var(--obsidian);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .error-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            max-width: 580px;
            width: 100%;
        }
        .font-serif {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold-accent) 0%, #a3e635 100%);
            color: #0b0f17;
            font-weight: 800;
            border-radius: 99px;
            padding: 0.8rem 2rem;
            border: none;
            transition: all 0.25s ease;
            box-shadow: 0 8px 20px rgba(204, 242, 53, 0.3);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(204, 242, 53, 0.45);
            color: #0b0f17;
        }
        .btn-outline-custom {
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #cbd5e1;
            border-radius: 99px;
            padding: 0.8rem 2rem;
            font-weight: 700;
            transition: all 0.25s ease;
        }
        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="error-card p-4 p-md-5 text-center">
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-dark text-danger rounded-circle p-4 mb-3 border border-secondary border-opacity-25" style="width: 100px; height: 100px; background: rgba(239, 68, 68, 0.1) !important;">
                <i class="fa-solid fa-user-lock fs-1 text-danger"></i>
            </div>
            <h1 class="display-3 fw-extrabold text-white font-serif mb-0" style="color: #ef4444 !important;">403</h1>
            <h2 class="h4 fw-bold text-white mb-3">Access Denied</h2>
            <p class="text-secondary small mb-4">
                You do not have administrative permissions to view this property page or perform this operation.
            </p>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="javascript:history.back()" class="btn btn-outline-custom">
                <i class="fa-solid fa-arrow-left me-2"></i> Go Back
            </a>
            <a href="/admin/dashboard" class="btn btn-gold">
                <i class="fa-solid fa-gauge me-2"></i> Return to Admin Dashboard
            </a>
        </div>
    </div>
</body>
</html>
