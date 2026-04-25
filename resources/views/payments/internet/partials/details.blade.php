<div class="row">
    <div class="col-md-6">
        <h6>Station Information</h6>
        <table class="table table-sm">
            <tr>
                <th>Station Name:</th>
                <td>{{ $payment->station->name }}</td>
            </tr>
            <tr>
                <th>Location:</th>
                <td>{{ $payment->station->location }}</td>
            </tr>
            <tr>
                <th>Contact Person:</th>
                <td>{{ $payment->station->contact_person }}</td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td>{{ $payment->station->contact_phone }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $payment->station->contact_email }}</td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6>Provider Information</h6>
        <table class="table table-sm">
            <tr>
                <th>Provider:</th>
                <td>{{ $payment->provider->name }}</td>
            </tr>
            <tr>
                <th>Paybill:</th>
                <td><code>{{ $payment->provider->paybill_number ?: 'N/A' }}</code></td>
            </tr>
            <tr>
                <th>Contact:</th>
                <td>{{ $payment->provider->support_contact }}</td>
            </tr>
            <tr>
                <th>Account No:</th>
                <td><code>{{ $payment->account_number }}</code></td>
            </tr>
        </table>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h6>Payment Details</h6>
        <table class="table table-sm">
            <tr>
                <th>Billing Month:</th>
                <td>{{ $payment->billing_month->format('F Y') }}</td>
            </tr>
            <tr>
                <th>Amount:</th>
                <td>KES {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if($payment->previous_balance > 0)
            <tr>
                <th>Previous Balance:</th>
                <td>KES {{ number_format($payment->previous_balance, 2) }}</td>
            </tr>
            <tr>
                <th>Total Due:</th>
                <td><strong>KES {{ number_format($payment->total_due, 2) }}</strong></td>
            </tr>
            @endif
            <tr>
                <th>Due Date:</th>
                <td>{{ $payment->due_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge
                        @if($payment->status == 'paid') bg-success
                        @elseif($payment->status == 'overdue') bg-danger
                        @elseif($payment->status == 'pending') bg-warning
                        @else bg-secondary @endif">
                        {{ ucfirst($payment->status) }}
                    </span>
                    @if($payment->is_overdue)
                    <br><small class="text-danger">{{ $payment->days_overdue }} day(s) overdue</small>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6>Payment Information</h6>
        <table class="table table-sm">
            <tr>
                <th>Payment Date:</th>
                <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'Not paid' }}</td>
            </tr>
            <tr>
                <th>Payment Method:</th>
                <td>{{ $payment->payment_method ?: 'N/A' }}</td>
            </tr>
            @if($payment->mpesa_receipt)
            <tr>
                <th>M-Pesa Receipt:</th>
                <td><code>{{ $payment->mpesa_receipt }}</code></td>
            </tr>
            @endif
            @if($payment->transaction_id)
            <tr>
                <th>Transaction ID:</th>
                <td><code>{{ $payment->transaction_id }}</code></td>
            </tr>
            @endif
        </table>
    </div>
</div>

@if($payment->invoice_notes)
<div class="row">
    <div class="col-12">
        <h6>Notes</h6>
        <div class="alert alert-light">
            {{ $payment->invoice_notes }}
        </div>
    </div>
</div>
@endif

<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <button class="btn btn-primary" onclick="printInvoice({{ $payment->id }})">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
            <a href="{{ route('payments.internet.edit', $payment->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>
</div>

<script>
function printInvoice(paymentId) {
    window.open(`/payments/internet/${paymentId}/invoice`, '_blank');
}
</script>
