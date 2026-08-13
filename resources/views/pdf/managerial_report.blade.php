<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Managerial Performance Report — {{ $property->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1a2035; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #3b6ff0; padding-bottom: 15px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .title { font-size: 20px; font-weight: bold; color: #0f1623; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .kpi-grid { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; }
        .kpi-val { font-size: 18px; font-weight: bold; color: #3b6ff0; margin-top: 5px; }
        .kpi-lbl { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-left: 3px solid #3b6ff0; padding-left: 8px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background: #f1f5f9; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #cbd5e1; }
        table.data-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .footer { text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 30px; }
    </style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td>
                <div class="title">{{ $property->name }}</div>
                <div class="subtitle">Executive Managerial Performance Report</div>
            </td>
            <td style="text-align: right;">
                <div><strong>Period:</strong> {{ $startDate }} to {{ $endDate }}</div>
                <div class="subtitle">Generated: {{ now()->format('Y-m-d H:i:s T') }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="section-title">Key Performance Indicators (KPIs)</div>
<table class="kpi-grid">
    <tr>
        <td style="width: 25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-lbl">Total Gross Revenue</div>
                <div class="kpi-val">{{ number_format(($metrics['total_gross_revenue_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] }}</div>
            </div>
        </td>
        <td style="width: 25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-lbl">Occupancy Rate</div>
                <div class="kpi-val">{{ $metrics['occupancy_pct'] ?? 0 }}%</div>
            </div>
        </td>
        <td style="width: 25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-lbl">Average Daily Rate (ADR)</div>
                <div class="kpi-val">{{ number_format(($metrics['adr_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] }}</div>
            </div>
        </td>
        <td style="width: 25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-lbl">RevPAR</div>
                <div class="kpi-val">{{ number_format(($metrics['revpar_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] }}</div>
            </div>
        </td>
    </tr>
</table>

<div class="section-title">Revenue & Capacity Operational Summary</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Metric Parameter</th>
            <th>Value / Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total Property Room Inventory</td>
            <td><strong>{{ $metrics['total_rooms'] ?? 0 }} Rooms</strong></td>
        </tr>
        <tr>
            <td>Total Available Room Nights (Period)</td>
            <td>{{ $metrics['total_available_room_nights'] ?? 0 }} Nights</td>
        </tr>
        <tr>
            <td>Occupied Room Nights</td>
            <td>{{ $metrics['occupied_room_nights'] ?? 0 }} Nights</td>
        </tr>
        <tr>
            <td>Net Room Revenue</td>
            <td>{{ number_format(($metrics['total_room_revenue_minor'] ?? 0) / 100, 2) }} {{ $metrics['currency'] }}</td>
        </tr>
        <tr>
            <td>Taxes & Fees Collected</td>
            <td>{{ number_format((($metrics['total_tax_minor'] ?? 0) + ($metrics['total_fee_minor'] ?? 0)) / 100, 2) }} {{ $metrics['currency'] }}</td>
        </tr>
    </tbody>
</table>

<div class="section-title">Booking Source & Distribution Channels</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Channel / Source Name</th>
            <th>Reservations</th>
            <th>Total Revenue</th>
        </tr>
    </thead>
    <tbody>
        @forelse($channels as $chn)
            <tr>
                <td><strong>{{ $chn['name'] }}</strong></td>
                <td>{{ $chn['count'] }}</td>
                <td>{{ number_format($chn['revenue_minor'] / 100, 2) }} {{ $metrics['currency'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #94a3b8;">No channel breakdown available.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    End of Report — {{ $property->name }} Management Platform
</div>

</body>
</html>
