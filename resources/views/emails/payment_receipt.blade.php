<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0;">
        <h2 style="color: #10b981; margin-top: 0;">Payment Received</h2>
        <p>Dear {{ $guest->first_name ?? 'Guest' }},</p>
        <p>We have successfully received your payment for your reservation at <strong>{{ $property->name }}</strong>.</p>

        <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Confirmation Code:</strong> {{ $reservation->confirmation_number }}</p>
            <p style="margin: 5px 0;"><strong>Amount Paid:</strong> {{ number_format($payment->amount_minor / 100, 2) }} {{ $payment->currency }}</p>
            <p style="margin: 5px 0;"><strong>Payment Method:</strong> {{ strtoupper($payment->provider) }}</p>
            <p style="margin: 5px 0;"><strong>Remaining Balance:</strong> {{ number_format($reservation->balance_minor / 100, 2) }} {{ $reservation->currency }}</p>
        </div>

        <p>Your updated payment receipt is attached to this email.</p>
        <p>Thank you!</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 12px; color: #64748b;">{{ $property->name }} | {{ $property->address_line1 }}</p>
    </div>
</body>
</html>
