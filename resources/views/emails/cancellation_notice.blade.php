<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservation Cancelled</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border: 1px solid #ef4444;">
        <h2 style="color: #ef4444; margin-top: 0;">Reservation Cancelled</h2>
        <p>Dear {{ $guest->first_name ?? 'Guest' }},</p>
        <p>This email confirms that your reservation at <strong>{{ $property->name }}</strong> has been cancelled.</p>

        <div style="background: #fef2f2; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #fca5a5;">
            <p style="margin: 5px 0;"><strong>Confirmation Code:</strong> {{ $reservation->confirmation_number }}</p>
            <p style="margin: 5px 0;"><strong>Original Check-In:</strong> {{ $reservation->check_in }}</p>
            @if(!empty($reason))
                <p style="margin: 5px 0;"><strong>Reason:</strong> {{ $reason }}</p>
            @endif
        </div>

        <p>If you have any questions or believe this was done in error, please contact our front desk team.</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 12px; color: #64748b;">{{ $property->name }} | Phone: {{ $property->phone ?? 'N/A' }}</p>
    </div>
</body>
</html>
