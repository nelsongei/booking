<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pre-Arrival Information</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0;">
        <h2 style="color: #d97706; margin-top: 0;">We Look Forward to Your Arrival!</h2>
        <p>Dear {{ $guest->first_name ?? 'Guest' }},</p>
        <p>We are excited to welcome you to <strong>{{ $property->name }}</strong> on <strong>{{ $reservation->check_in }}</strong>.</p>

        <div style="background: #fffbe8; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #fef08a;">
            <p style="margin: 5px 0;"><strong>Confirmation Code:</strong> {{ $reservation->confirmation_number }}</p>
            <p style="margin: 5px 0;"><strong>Check-In Time:</strong> From {{ $property->getCheckInTime() }}</p>
            <p style="margin: 5px 0;"><strong>Check-Out Time:</strong> Until {{ $property->getCheckOutTime() }}</p>
            <p style="margin: 5px 0;"><strong>Location Address:</strong> {{ $property->address_line1 }}, {{ $property->city }}</p>
        </div>

        <p>If you require special assistance or airport transfer, please reply directly to this email or call our reception desk.</p>
        <p>Safe travels!</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 12px; color: #64748b;">{{ $property->name }} | Phone: {{ $property->phone ?? 'N/A' }}</p>
    </div>
</body>
</html>
