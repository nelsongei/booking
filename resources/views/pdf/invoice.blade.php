<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 30px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: top;
        }
        .property-title {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #d97706;
            text-align: right;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table td {
            width: 50%;
            vertical-align: top;
        }
        .box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 6px;
        }
        .box-title {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            font-size: 12px;
        }
        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 10px;
        }
        .total-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 6px 10px;
        }
        .total-row {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #0f172a;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div class="property-title">{{ $property->name }}</div>
                <div>{{ $property->address_line1 }}, {{ $property->city }}</div>
                <div>Phone: {{ $property->phone ?? 'N/A' }} | Email: {{ $property->email ?? 'N/A' }}</div>
            </td>
            <td>
                <div class="invoice-title">INVOICE</div>
                <div style="text-align: right; color: #64748b;">
                    <strong>#{{ $invoice_number }}</strong><br>
                    Date: {{ $issued_at }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Guest & Stay Details -->
    <table class="details-table">
        <tr>
            <td style="padding-right: 10px;">
                <div class="box">
                    <div class="box-title">Billed To</div>
                    <strong>{{ $guest->first_name ?? 'Guest' }} {{ $guest->last_name ?? '' }}</strong><br>
                    Email: {{ $guest->email ?? 'N/A' }}<br>
                    Phone: {{ $guest->phone ?? 'N/A' }}
                </div>
            </td>
            <td style="padding-left: 10px;">
                <div class="box">
                    <div class="box-title">Reservation Info</div>
                    Confirmation #: <strong>{{ $reservation->confirmation_number }}</strong><br>
                    Check-In: <strong>{{ $reservation->check_in }}</strong><br>
                    Check-Out: <strong>{{ $reservation->check_out }}</strong> ({{ $reservation->nights }} nights)
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount ({{ $reservation->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($line_items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['amount_minor'] / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="total-table">
        <tr>
            <td>Subtotal:</td>
            <td style="text-align: right;">{{ number_format($reservation->subtotal_minor / 100, 2) }} {{ $reservation->currency }}</td>
        </tr>
        <tr>
            <td>Taxes & Fees:</td>
            <td style="text-align: right;">{{ number_format($reservation->tax_minor / 100, 2) }} {{ $reservation->currency }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Due:</td>
            <td style="text-align: right;">{{ number_format($reservation->total_minor / 100, 2) }} {{ $reservation->currency }}</td>
        </tr>
    </table>

    <div class="footer">
        Thank you for choosing {{ $property->name }}. We look forward to welcoming you!
    </div>

</body>
</html>
