<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #fff;
        }

        .header {
            background: #1a1400;
            color: #C9A84C;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
        }

        .brand {
            font-size: 28px;
            letter-spacing: 4px;
        }

        .brand-sub {
            font-size: 10px;
            letter-spacing: 2px;
            color: #999;
            margin-top: 4px;
        }

        .invoice-meta {
            text-align: right;
            color: #C9A84C;
        }

        .invoice-meta .inv-num {
            font-size: 16px;
            font-weight: bold;
        }

        .invoice-meta .inv-date {
            font-size: 11px;
            color: #aaa;
            margin-top: 4px;
        }

        .body {
            padding: 40px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #999;
            margin-bottom: 8px;
        }

        .two-col {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .col {
            width: 48%;
        }

        .col p {
            font-size: 12px;
            color: #444;
            line-height: 1.7;
        }

        .col strong {
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        thead tr {
            background: #1a1400;
            color: #C9A84C;
        }

        thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            letter-spacing: 1px;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        tbody td {
            padding: 12px 14px;
            font-size: 12px;
            color: #333;
        }

        .totals {
            margin-left: auto;
            width: 280px;
        }

        .totals table {
            margin-bottom: 0;
        }

        .totals td {
            padding: 6px 10px;
            font-size: 12px;
        }

        .totals .total-row td {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #C9A84C;
            color: #1a1400;
            padding-top: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 10px;
            color: #aaa;
        }

        .paid-badge {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 8px;
        }

    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="brand">LUMIÈRE.</div>
            <div class="brand-sub">SALON MANAGEMENT PLATFORM</div>
        </div>
        <div class="invoice-meta">
            <div class="inv-num">{{ $invoiceNumber }}</div>
            <div class="inv-date">Date: {{ now()->format('d M Y') }}</div>
            <div class="inv-date">Booking Ref: #LMR-{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <div class="body">

        <div class="two-col">
            <div class="col">
                <div class="section-title">Billed From</div>
                <p>
                    <strong>{{ $tenant->name }}</strong><br>
                    {{ $tenant->address ?? 'Address not provided' }}<br>
                    {{ $tenant->email }}<br>
                    {{ $tenant->phone }}
                </p>
            </div>
            <div class="col">
                <div class="section-title">Billed To</div>
                <p>
                    <strong>{{ $customer->name }}</strong><br>
                    {{ $customer->email }}<br>
                    {{ $customer->phone ?? '' }}
                </p>
            </div>
        </div>

        <div class="section-title">Appointment Details</div>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Professional</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $appointment->service->name }}</td>
                    <td>{{ $appointment->staff->user->name ?? 'Any Stylist' }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</td>
                    <td>₹{{ number_format($baseAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Base Amount</td>
                    <td style="text-align:right;">₹{{ number_format($baseAmount, 2) }}</td>
                </tr>
                <tr>
                    <td>GST ({{ $gstRate }}%)</td>
                    <td style="text-align:right;">₹{{ number_format($gstAmount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Paid</td>
                    <td style="text-align:right;">₹{{ number_format($appointment->amount, 2) }}</td>
                </tr>
            </table>
            <div style="text-align:right; margin-top:8px;">
                <span class="paid-badge">✓ PAID</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing {{ $tenant->name }} · Powered by LUMIÈRE</p>
            <p style="margin-top:4px;">This is a computer generated invoice and does not require a signature.</p>
        </div>

    </div>
</body>
</html>
