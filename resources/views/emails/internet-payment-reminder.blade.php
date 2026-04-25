{{-- <!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; border-radius: 5px; }
        .content { padding: 20px; background: white; border: 1px solid #dee2e6; }
        .alert { padding: 15px; border-radius: 5px; margin: 15px 0; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; }
        .payment-details { background: #e7f5ff; padding: 15px; border-radius: 5px; }
        .mpesa-steps { background: #d1ecf1; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Internet Service Payment Reminder</h2>
        </div>

        <div class="content">
            <p>Dear {{ $payment->station->contact_person }},</p>

            <div class="@if($payment->is_overdue) alert-danger @else alert-warning @endif">
                <h4>
                    @if($payment->is_overdue)
                    ⚠️ OVERDUE PAYMENT
                    @elseif($payment->is_due_soon)
                    ⏰ PAYMENT DUE SOON
                    @else
                    Payment Reminder
                    @endif
                </h4>

                @if($payment->is_overdue)
                <p>This payment is <strong>{{ $payment->days_overdue }} day(s) overdue</strong>.</p>
                @elseif($payment->is_due_soon)
                <p>Payment due in <strong>{{ now()->diffInDays($payment->due_date) }} day(s)</strong>.</p>
                @endif
            </div>

            <div class="payment-details">
                <h4>Payment Details</h4>
                <table>
                    <tr>
                        <td><strong>Provider:</strong></td>
                        <td>{{ $payment->provider->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Account Number:</strong></td>
                        <td>{{ $payment->account_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Station:</strong></td>
                        <td>{{ $payment->station->name }} - {{ $payment->station->location }}</td>
                    </tr>
                    <tr>
                        <td><strong>Billing Month:</strong></td>
                        <td>{{ $payment->billing_month->format('F Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Amount Due:</strong></td>
                        <td><strong style="color: #e74c3c;">KES {{ number_format($payment->total_due, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Due Date:</strong></td>
                        <td>{{ $payment->due_date->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            @if($payment->provider->paybill_number)
            <div class="mpesa-steps">
                <h4>📱 Pay via M-Pesa</h4>
                <ol>
                    <li>Go to <strong>Lipa na M-PESA</strong></li>
                    <li>Select <strong>Pay Bill</strong></li>
                    <li>Business No: <strong>{{ $payment->provider->paybill_number }}</strong></li>
                    <li>Account No: <strong>{{ $payment->account_number }}</strong></li>
                    <li>Amount: <strong>KES {{ number_format($payment->total_due, 2) }}</strong></li>
                </ol>
            </div>
            @endif

            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <h4>📞 Need Help?</h4>
                <p><strong>Contact Support:</strong> {{ $payment->provider->support_contact }}</p>
                @if($payment->provider->billing_email)
                <p><strong>Email:</strong> {{ $payment->provider->billing_email }}</p>
                @endif
            </div>

            <p style="margin-top: 20px;">
                Thank you for your prompt payment.<br>
                <strong>{{ config('app.name') }}</strong>
            </p>
        </div>
    </div>
</body>
</html> --}}

<!DOCTYPE html>
<html>
<head>
    <title>Internet Payment Reminder</title>
</head>
<body>
    <h2>Internet Payment Reminder</h2>
    <p>Dear {{ $contactPerson }},</p>

    <p>This is a reminder that the internet bill for <strong>{{ $stationName }}</strong> is due soon.</p>

    <h3>Payment Details:</h3>
    <ul>
        <li><strong>Provider:</strong> {{ $providerName }}</li>
        <li><strong>Account Number:</strong> {{ $accountNumber }}</li>
        <li><strong>Amount Due:</strong> KES {{ number_format($amount, 2) }}</li>
        <li><strong>Due Date:</strong> {{ $dueDate }}</li>
        <li><strong>Paybill Number:</strong> {{ $paybillNumber }}</li>
    </ul>

    <p>Please ensure payment is made on time to avoid service interruption.</p>

    <p>If you have already made the payment, please ignore this reminder.</p>

    <p>Thank you,<br>Finance Department</p>
</body>
</html>
