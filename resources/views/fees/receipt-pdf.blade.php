<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $payment->payment_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px; }
        .school-name { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #0f172a; margin: 0; }
        .school-sub { font-size: 9px; color: #64748b; margin-top: 3px; }
        .badge { display: inline-block; background: #0f172a; color: #fff; padding: 3px 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; margin-top: 8px; border-radius: 3px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .meta-table td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        .student-box { width: 100%; border-collapse: collapse; margin-bottom: 15px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .student-box td { padding: 8px; }
        .amount-box { text-align: center; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .amount-val { font-size: 22px; font-weight: bold; color: #047857; margin-top: 3px; }
        .sig-table { width: 100%; margin-top: 30px; }
        .sig-line { border-top: 1px solid #94a3b8; width: 140px; margin-top: 30px; padding-top: 4px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="school-name">{{ $school->name ?? 'Greenwood International School' }}</h1>
        <div class="school-sub">{{ $school->address ?? 'Knowledge City, Bangalore' }} | Phone: {{ $school->phone ?? '+91 98765 43210' }}</div>
        <div class="badge">Official Fee Receipt</div>
    </div>

    <table class="meta-table">
        <tr>
            <td>
                <strong>Receipt No:</strong> {{ $payment->payment_number }}<br>
                <strong>Invoice No:</strong> {{ $payment->invoice->invoice_number }}
            </td>
            <td style="text-align: right;">
                <strong>Date:</strong> {{ $payment->paid_at->format('d M Y, h:i A') }}<br>
                <strong>Payment Mode:</strong> <span style="text-transform: uppercase;">{{ $payment->payment_method }}</span>
            </td>
        </tr>
    </table>

    <table class="student-box">
        <tr>
            <td width="50%">
                <strong>Student:</strong> {{ $payment->invoice->enrollment->student->full_name }}<br>
                <strong>Admission No:</strong> {{ $payment->invoice->enrollment->student->admission_no }}
            </td>
            <td width="50%">
                <strong>Class:</strong> {{ $payment->invoice->enrollment->section->full_name }}<br>
                <strong>Billing Period:</strong> {{ $payment->invoice->period_key }}
            </td>
        </tr>
    </table>

    <div class="amount-box">
        <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; color: #065f46;">Total Amount Received</div>
        <div class="amount-val">₹{{ number_format($payment->amount, 2) }}</div>
        @if($payment->transaction_reference)
        <div style="font-size: 9px; color: #065f46; margin-top: 2px;">Ref: {{ $payment->transaction_reference }}</div>
        @endif
    </div>

    <table class="sig-table">
        <tr>
            <td>
                Status: <strong>{{ strtoupper($payment->status) }}</strong><br>
                <span style="font-size: 9px; color: #64748b;">Cashier: {{ $payment->collector->name }}</span>
            </td>
            <td style="text-align: right;">
                <div class="sig-line" style="display: inline-block;">
                    Authorized Signature
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
