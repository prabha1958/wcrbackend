<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Subscription Receipt</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
        }

        .section {
            margin-bottom: 15px;
        }

        .box {
            border: 1px solid #000;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>CSI Centenary Wesley Church</h2>
        <p>Subscription Payment Receipt</p>
    </div>

    {{-- RECEIPT INFO --}}
    <div class="section box">
        <strong>Receipt No:</strong> {{ $receipt_no }}<br>
        <strong>Date:</strong> {{ $date }}<br>
        <strong>Financial Year:</strong> {{ $financial_year }}
    </div>

    {{-- MEMBER INFO --}}
    <div class="section box">
        <strong>Member Name:</strong> {{ $member->first_name }} {{ $member->last_name }}<br>
        <strong>Member ID:</strong> {{ $member->id }}
    </div>

    {{-- PAYMENT DETAILS --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $month)
                    <tr>
                        <td>{{ ucfirst($month) }}</td>
                        <td>Paid</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- AMOUNT SUMMARY --}}
    <div class="section">
        <table>
            <tr>
                <th>Total Amount Paid</th>
                <th class="right">₹ {{ number_format($amount, 2) }}</th>
            </tr>
        </table>
    </div>

    {{-- RAZORPAY DETAILS --}}
    <div class="section box">
        <strong>Razorpay Order ID:</strong><br>
        {{ $razorpay_order_id }}<br><br>

        <strong>Razorpay Payment ID:</strong><br>
        {{ $razorpay_payment_id }}
    </div>
    <div class="section box">
        <strong>Payment Mode:</strong> {{ strtoupper($payment_mode ?? 'ONLINE') }}<br>

        @if (!empty($reference_no))
            <strong>Reference No:</strong> {{ $reference_no }}
        @endif
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        This is a system-generated receipt. No signature is required.
    </div>

</body>

</html>
