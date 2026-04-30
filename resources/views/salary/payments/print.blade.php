<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .receipt { max-width: 400px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 20px; }
        .total { font-size: 24px; font-weight: bold; color: #059669; margin: 20px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>Payment Receipt</h2>
            <p>Ref: {{ $payment->transaction_reference }}</p>
        </div>
        <p><strong>Employee:</strong> {{ $payment->employee->name }}</p>
        <p><strong>Date:</strong> {{ $payment->payment_date->format('F d, Y') }}</p>
        <p><strong>Type:</strong> {{ ucfirst($payment->type) }}</p>
        <p><strong>Method:</strong> {{ strtoupper($payment->payment_method) }}</p>
        <hr>
        <p><strong>Gross Amount:</strong> KES {{ number_format($payment->amount, 2) }}</p>
        <p><strong>Deductions:</strong> KES {{ number_format($payment->deductions_total, 2) }}</p>
        <div class="total">Net Paid: KES {{ number_format($payment->net_amount, 2) }}</div>
        <button class="no-print" onclick="window.print()">Print</button>
    </div>
</body>
</html>