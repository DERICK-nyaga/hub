@extends('layouts.app')

@section('title', 'Due Soon Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Payments Due Soon (Next 7 Days)</h1>
        <button class="btn btn-primary" onclick="sendAllReminders()">
            <i class="bi bi-envelope"></i> Send All Reminders
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            @if($dueSoon->isEmpty())
            <div class="alert alert-info">
                No payments due in the next 7 days.
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Provider</th>
                            <th>Account</th>
                            <th>Amount Due</th>
                            <th>Due Date</th>
                            <th>Contact</th>
                            <th>Paybill</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dueSoon as $payment)
                        <tr class="@if($payment['is_due_today']) table-warning @endif">
                            <td>{{ $payment['station'] }}</td>
                            <td>{{ $payment['provider'] }}</td>
                            <td><code>{{ $payment['account_number'] }}</code></td>
                            <td><strong>KES {{ number_format($payment['amount_due'], 2) }}</strong></td>
                            <td>
                                {{ $payment['formatted_due_date'] }}
                                @if($payment['is_due_today'])
                                <span class="badge bg-warning">Today</span>
                                @elseif($payment['days_until_due'] <= 3)
                                <span class="badge bg-info">{{ $payment['days_until_due'] }} days</span>
                                @endif
                            </td>
                            <td>
                                {{ $payment['contact_person'] }}<br>
                                <small>{{ $payment['contact_phone'] }}</small><br>
                                <small>{{ $payment['contact_email'] }}</small>
                            </td>
                            <td>
                                @if($payment['paybill_number'])
                                <code>{{ $payment['paybill_number'] }}</code>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="sendReminder({{ $payment['id'] }})"
                                        data-bs-toggle="tooltip" title="Send Reminder">
                                    <i class="bi bi-envelope"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info"
                                        onclick="viewDetails({{ json_encode($payment) }})"
                                        data-bs-toggle="tooltip" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success"
                                        onclick="markAsPaid({{ $payment['id'] }})"
                                        data-bs-toggle="tooltip" title="Mark as Paid">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewDetails(payment) {
        const modalBody = document.getElementById('modalBody');
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Station Information</h6>
                    <p><strong>Name:</strong> ${payment.station}</p>
                    <p><strong>Contact:</strong> ${payment.contact_person}</p>
                    <p><strong>Phone:</strong> ${payment.contact_phone}</p>
                    <p><strong>Email:</strong> ${payment.contact_email}</p>
                </div>
                <div class="col-md-6">
                    <h6>Payment Information</h6>
                    <p><strong>Provider:</strong> ${payment.provider}</p>
                    <p><strong>Account:</strong> ${payment.account_number}</p>
                    <p><strong>Amount Due:</strong> KES ${parseFloat(payment.amount_due).toLocaleString('en-KE', {minimumFractionDigits: 2})}</p>
                    <p><strong>Due Date:</strong> ${payment.formatted_due_date}</p>
                    <p><strong>Paybill:</strong> ${payment.paybill_number || 'N/A'}</p>
                    <p><strong>Support Contact:</strong> ${payment.support_contact}</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6>Reminder Message Preview</h6>
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;">${payment.reminder_message}</pre>
                </div>
            </div>
        `;
        modalBody.innerHTML = content;
        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }

    function sendReminder(paymentId) {
        if (confirm('Send reminder for this payment?')) {
            fetch(`/payments/${paymentId}/send-reminder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert('Reminder sent successfully!');
            })
            .catch(error => {
                alert('Error sending reminder');
            });
        }
    }

    function sendAllReminders() {
        if (confirm('Send reminders for all due soon payments?')) {
            // Implement bulk reminder sending
            alert('Sending all reminders...');
        }
    }

    function markAsPaid(paymentId) {
        if (confirm('Mark this payment as paid?')) {
            // Implement mark as paid functionality
            alert('Marking as paid...');
        }
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
@endsection
