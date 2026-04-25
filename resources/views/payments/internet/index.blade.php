@extends('layouts.app')

@section('title', 'Internet Payments')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Internet Payments</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('payments.internet.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> New Internet Payment
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('payments.internet.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="vendor_id" class="form-label">Provider</label>
                            <select class="form-select" id="vendor_id" name="vendor_id">
                                <option value="">All Providers</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->vendor_id }}" {{ request('vendor_id') == $provider->vendor_id ? 'selected' : '' }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                            <label for="month" class="form-label">Billing Month</label>
                            <input type="month" class="form-control" id="month" name="month" value="{{ request('month') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('payments.internet.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Station</th>
                                    <th>Provider</th>
                                    <th>Account No</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->station->name }}</td>
                                    <td>{{ $payment->provider->name }}</td>
                                    <td><code>{{ $payment->account_number }}</code></td>
                                    <td>
                                        <strong>KES {{ number_format($payment->amount, 2) }}</strong>
                                        @if($payment->previous_balance > 0)
                                        <br><small class="text-muted">+ KES {{ number_format($payment->previous_balance, 2) }} previous</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $payment->due_date->format('d/m/Y') }}
                                        @if($payment->is_overdue)
                                        <br><small class="text-danger">{{ $payment->days_overdue }} day(s) overdue</small>
                                        @elseif($payment->is_due_soon)
                                        <br><small class="text-warning">Due in {{ now()->diffInDays($payment->due_date) }} day(s)</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge
                                            @if($payment->status == 'paid') bg-success
                                            @elseif($payment->status == 'overdue') bg-danger
                                            @elseif($payment->status == 'pending') bg-warning
                                            @else bg-secondary @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" style="gap: 5px;">
                                            <a href="{{ route('payments.internet.edit', $payment->id) }}"
                                            class="btn btn-primary"
                                            title="Edit"
                                            style="min-width: 36px;">
                                                <i class="fa fa-pencil"></i>
                                            </a>

                                            {{-- <button type="button"
                                                    class="btn btn-info"
                                                    onclick="viewPaymentDetails({{ $payment->id }})"
                                                    title="View"
                                                    style="min-width: 36px;">
                                                <i class="fa fa-eye"></i>
                                            </button> --}}

                                            <a href="{{ route('payments.internet.show', $payment->id) }}"
                                                class="btn btn-primary"
                                                title="Edit"
                                                style="min-width: 36px;">
                                                    <i class="fa fa-eye"></i>
                                            </a>

                                            <button type="button"
                                                    class="btn btn-success"
                                                    onclick="sendReminder{{ $payment->id }})"
                                                    title="Send Reminder"
                                                    style="min-width: 36px;">
                                                <i class="fa fa-envelope"></i>
                                            </button>

                                            <form action="{{ route('payments.internet.destroy', $payment->id) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-danger"
                                                        onclick="return confirm('Delete this payment?')"
                                                        title="Delete"
                                                        style="min-width: 36px;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No internet payments found.</td>
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
    </div>

    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function viewPaymentDetails(paymentId) {
        fetch(`/payments/internet/${paymentId}/details`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('paymentDetailsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load payment details');
            });
    }

    function sendReminder(paymentId) {
        if (confirm('Send payment reminder?')) {
            fetch(`/payments/internet/${paymentId}/send-reminder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message || 'Reminder sent successfully!');
            })
            .catch(error => {
                alert('Failed to send reminder');
            });
        }
    }
</script>
@endpush
