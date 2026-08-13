<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0;">
        <h2 style="color: #0f172a; margin-top: 0;">Reservation Confirmed!</h2>
        <p>Dear {{ $guest->first_name ?? 'Guest' }},</p>
        <p>Thank you for choosing <strong>{{ $property->name }}</strong>. Your reservation has been successfully confirmed.</p>

        <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Confirmation Code:</strong> <span style="font-family: monospace; font-size: 16px; color: #d97706;">{{ $reservation->confirmation_number }}</span></p>
            <p style="margin: 5px 0;"><strong>Check-In Date:</strong> {{ $reservation->check_in }}</p>
            <p style="margin: 5px 0;"><strong>Check-Out Date:</strong> {{ $reservation->check_out }}</p>
            <p style="margin: 5px 0;"><strong>Nights:</strong> {{ $reservation->nights }}</p>
            <p style="margin: 5px 0;"><strong>Total Charges:</strong> {{ number_format($reservation->total_minor / 100, 2) }} {{ $reservation->currency }}</p>
        </div>

        <p>Please find your official PDF invoice attached to this email.</p>
        <p>We look forward to hosting you!</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 12px; color: #64748b;">{{ $property->name }} | {{ $property->address_line1 }}, {{ $property->city }}</p>
    </div>
</body>
</html>
