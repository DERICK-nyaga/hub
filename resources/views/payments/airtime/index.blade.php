@extends('layouts.app')

@section('title', 'Airtime Payments')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Airtime Payments</h2>
                </div>

                <div class="col-md-6 text-end">
                    <a href="{{ route('payments.airtime.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> New Airtime Payment
                    </a>
                </div>
            </div>
        </div>


                <div class="col-md-6 text-end">
                    <button class="btn btn-warning" onclick="checkExpiries()" title="Check for expiring items">
                        <i class="fas fa-bell me-1"></i> Check Expiries
                    </button>
                </div>
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('payments.airtime.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="station_id" class="form-label">Station</label>
                                <select class="form-select" id="station_id" name="station_id">
                                    <option value="">All Stations</option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->station_id }}" {{ request('station_id') == $station->station_id ? 'selected' : '' }}>
                                            {{ $station->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="network_provider" class="form-label">Network</label>
                                <select class="form-select" id="network_provider" name="network_provider">
                                    <option value="">All Networks</option>
                                    <option value="Safaricom" {{ request('network_provider') == 'Safaricom' ? 'selected' : '' }}>Safaricom</option>
                                    <option value="Airtel" {{ request('network_provider') == 'Airtel' ? 'selected' : '' }}>Airtel</option>
                                    <option value="Telkom" {{ request('network_provider') == 'Telkom' ? 'selected' : '' }}>Telkom</option>
                                    <option value="Faiba" {{ request('network_provider') == 'Faiba' ? 'selected' : '' }}>Faiba</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="pending_topup" {{ request('status') == 'pending_topup' ? 'selected' : '' }}>Pending Topup</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="month" class="form-label">Month</label>
                                <input type="month" class="form-control" id="month" name="month" value="{{ request('month') }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('payments.airtime.index') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Mobile Number</th>
                                <th>Network</th>
                                <th>Amount</th>
                                <th>Topup Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            @php
                                $expiryDate = \Carbon\Carbon::parse($payment->expected_expiry);
                                $today = \Carbon\Carbon::today();
                                $daysRemaining = $today->diffInDays($expiryDate, false);

                                if ($today->gt($expiryDate) && $payment->status != 'expired') {
                                    $payment->status = 'expired';
                                    $payment->save();
                                }
                            @endphp
                            <tr>
                                <td>{{ $payment->station->name }}</td>
                                <td>
                                    <code>{{ $payment->mobile_number }}</code>
                                    @if($payment->transaction_id)
                                    <br><small class="text-muted">TXN: {{ $payment->transaction_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge
                                        @if($payment->network_provider == 'Safaricom') bg-success
                                        @elseif($payment->network_provider == 'Airtel') bg-danger
                                        @elseif($payment->network_provider == 'Telkom') bg-info
                                        @else bg-secondary @endif">
                                        {{ $payment->network_provider }}
                                    </span>
                                </td>
                                <td>
                                    <strong>KES {{ number_format((float)$payment->amount, 2) }}</strong>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($payment->topup_date)->format('d/m/Y') }}
                                    @if($payment->last_topup_date && $payment->last_topup_date != $payment->topup_date)
                                    <br><small class="text-muted">Last: {{ \Carbon\Carbon::parse($payment->last_topup_date)->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}
                                    @if($daysRemaining < 0)
                                        <br><small class="text-danger">Expired {{ abs($daysRemaining) }} day(s) ago</small>
                                    @elseif($daysRemaining <= 3)
                                        <br><small class="text-warning">Expires in {{ $daysRemaining }} day(s)</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $isExpired = $today->gt($expiryDate);
                                    @endphp

                                    <span class="badge
                                        @if($payment->status == 'active') bg-success
                                        @elseif($payment->status == 'expired') bg-danger
                                        @elseif($payment->status == 'pending_topup') bg-warning
                                        @else bg-secondary @endif">
                                        @if($payment->status == 'active' && $daysRemaining <= 3)
                                            Expiring Soon
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                                        @endif
                                    </span>

                                    @if($payment->status == 'active' && $daysRemaining <= 3)
                                        <br><small class="text-warning">Expires in {{ $daysRemaining }} day(s)</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info"
                                                onclick="viewPaymentDetails({{ $payment->id }})" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($payment->status == 'expired' || $payment->status == 'active')
                                        <a href="{{ route('payments.airtime.renew', $payment->id) }}"
                                           class="btn btn-outline-success" title="Renew/Topup">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                        @endif

                                        @if($payment->status == 'active' && $daysRemaining <= 3)
                                        <button type="button" class="btn btn-outline-warning" title="Send Reminder">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                        @endif

                                        @if($payment->status == 'expired')
                                        <form action="{{ route('payments.airtime.delete', $payment->id) }}"
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"
                                                    onclick="return confirm('Delete this expired payment?')"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    <i class="fas fa-phone-alt fa-2x mb-2"></i><br>
                                    No airtime payments found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                    </div>
                    <div>
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Airtime Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function viewPaymentDetails(paymentId) {
            fetch(`/payments/airtime/${paymentId}/details`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    document.getElementById('paymentDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load payment details');
                });
        }
    </script>
    <script>
function checkExpiries() {
    fetch('{{ route("notifications.check-expiries") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
</script>
    @endpush
@endsection
