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
        <h6>Payment Details</h6>
        <table class="table table-sm">
            <tr>
                <th>Mobile Number:</th>
                <td><code>{{ $payment->mobile_number }}</code></td>
            </tr>
            <tr>
                <th>Network Provider:</th>
                <td>
                    <span class="badge
                        @if($payment->network_provider == 'Safaricom') bg-success
                        @elseif($payment->network_provider == 'Airtel') bg-danger
                        @elseif($payment->network_provider == 'Telkom') bg-info
                        @else bg-secondary @endif">
                        {{ $payment->network_provider }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Amount:</th>
                <td><strong>KES {{ number_format((float)$payment->amount, 2) }}</strong></td>
            </tr>
            <tr>
                <th>Topup Date:</th>
                <td>{{ \Carbon\Carbon::parse($payment->topup_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Expected Expiry:</th>
                <td>{{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge
                        @if($payment->status == 'active') bg-success
                        @elseif($payment->status == 'expired') bg-danger
                        @elseif($payment->status == 'pending_topup') bg-warning
                        @else bg-secondary @endif">
                        {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                    </span>
                </td>
            </tr>
            @if($payment->transaction_id)
            <tr>
                <th>Transaction ID:</th>
                <td><code>{{ $payment->transaction_id }}</code></td>
            </tr>
            @endif
        </table>
    </div>
</div>

@if($payment->notes)
<div class="row mt-3">
    <div class="col-12">
        <h6>Notes</h6>
        <div class="alert alert-light">
            {{ $payment->notes }}
        </div>
    </div>
</div>
@endif

@php
    $expiryDate = \Carbon\Carbon::parse($payment->expected_expiry);
    $today = \Carbon\Carbon::today();
    $daysRemaining = $today->diffInDays($expiryDate, false);
@endphp

<div class="row mt-3">
    <div class="col-12">
        @if($daysRemaining < 0)
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                This airtime expired {{ abs($daysRemaining) }} day(s) ago.
            </div>
        @elseif($daysRemaining <= 3)
            <div class="alert alert-warning">
                <i class="fas fa-clock me-2"></i>
                This airtime expires in {{ $daysRemaining }} day(s).
            </div>
        @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                This airtime is active and expires in {{ $daysRemaining }} day(s).
            </div>
        @endif
    </div>
</div>
