@extends('layouts.app')

@section('title', 'Airtime Payment Details')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Airtime Payment Details</h2>
                    <p class="text-muted mb-0">View airtime topup information for {{ $payment->station->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('payments.airtime.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                    <a href="{{ route('payments.airtime.renew', $payment->id) }}" class="btn btn-primary">
                        <i class="fas fa-redo me-1"></i> Renew
                    </a>
                    @if($payment->status == 'expired')
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $payment->id }})">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Alert -->
        @if($payment->status == 'expired')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Expired!</strong> This airtime payment expired on {{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @elseif($payment->expected_expiry && \Carbon\Carbon::parse($payment->expected_expiry)->isToday())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-clock me-2"></i>
                <strong>Expires Today!</strong> This airtime expires today.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @elseif($payment->expected_expiry && \Carbon\Carbon::parse($payment->expected_expiry)->diffInDays(now()) <= 3)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-hourglass-half me-2"></i>
                <strong>Expiring Soon!</strong> This airtime will expire in {{ \Carbon\Carbon::parse($payment->expected_expiry)->diffInDays(now()) }} days.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Details Card -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Station</label>
                                    <h5 class="mb-2">
                                        <i class="fas fa-building me-2 text-primary"></i>
                                        {{ $payment->station->name ?? 'N/A' }}
                                    </h5>
                                    <p class="mb-1 text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $payment->station->location ?? 'No location' }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-hashtag me-1"></i>
                                        Code: {{ $payment->station->code ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Mobile Number</label>
                                    <h5 class="mb-2">
                                        <i class="fas fa-mobile-alt me-2 text-success"></i>
                                        {{ $payment->mobile_number }}
                                    </h5>
                                    <p class="mb-0">
                                        <i class="fas fa-signal me-1"></i>
                                        Network: <strong>{{ $payment->network_provider }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Amount</label>
                                    <h3 class="text-success mb-0">
                                        KES {{ number_format($payment->amount, 2) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Topup Date</label>
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2 text-info"></i>
                                        {{ \Carbon\Carbon::parse($payment->topup_date)->format('d/m/Y') }}
                                    </h5>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($payment->topup_date)->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Expected Expiry</label>
                                    <h5 class="mb-0">
                                        <i class="fas fa-hourglass-end me-2 text-warning"></i>
                                        {{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}
                                    </h5>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($payment->expected_expiry)->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Transaction ID</label>
                                    <div class="input-group">
                                        <code class="form-control-plaintext">
                                            <i class="fas fa-receipt me-2"></i>
                                            {{ $payment->transaction_id ?? 'N/A' }}
                                        </code>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="copyToClipboard('{{ $payment->transaction_id }}')"
                                                title="Copy Transaction ID">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Payment Method</label>
                                    <h6 class="mb-0">
                                        <i class="fas fa-credit-card me-2 text-primary"></i>
                                        {{ $payment->payment_method ?? 'Not specified' }}
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Status</label>
                                    <div>
                                        @php
                                            $statusBadge = [
                                                'active' => 'bg-success',
                                                'expired' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                'cancelled' => 'bg-secondary'
                                            ][$payment->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusBadge }} p-2">
                                            <i class="fas fa-{{ $payment->status == 'active' ? 'check-circle' : ($payment->status == 'expired' ? 'times-circle' : 'clock') }} me-1"></i>
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Auto Renew</label>
                                    <h6 class="mb-0">
                                        @if($payment->auto_renew)
                                            <span class="badge bg-info">
                                                <i class="fas fa-check me-1"></i> Enabled
                                            </span>
                                            <small class="text-muted d-block mt-1">
                                                Will auto-renew monthly
                                            </small>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-ban me-1"></i> Disabled
                                            </span>
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>

                        @if($payment->notes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Notes</label>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-sticky-note me-2 text-muted"></i>
                                            {{ $payment->notes }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase">Timestamps</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-plus-circle me-1"></i> Created:
                                                {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i:s') }}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-edit me-1"></i> Last Updated:
                                                {{ \Carbon\Carbon::parse($payment->updated_at)->format('d/m/Y H:i:s') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Station Contact Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-address-card me-2"></i> Station Contact Info</h6>
                    </div>
                    <div class="card-body">
                        @if($payment->station)
                            <div class="text-center mb-3">
                                <div class="avatar-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 60px; height: 60px; font-size: 24px;">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h6>{{ $payment->station->name }}</h6>
                                <p class="text-muted small">{{ $payment->station->location ?? 'No location' }}</p>
                            </div>
                            <hr>
                            <div class="contact-details">
                                <p class="mb-2">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    <strong>Contact Person:</strong><br>
                                    {{ $payment->station->contact_person ?? 'N/A' }}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-phone me-2 text-success"></i>
                                    <strong>Phone:</strong><br>
                                    {{ $payment->station->contact_phone ?? 'N/A' }}
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-envelope me-2 text-info"></i>
                                    <strong>Email:</strong><br>
                                    {{ $payment->station->contact_email ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('payments.station.details', $payment->station->station_id) }}" 
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-eye me-1"></i> View Full Station Details
                                </a>
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">Station information not available</p>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('payments.airtime.renew', $payment->id) }}" class="btn btn-primary">
                                <i class="fas fa-redo me-1"></i> Renew This Topup
                            </a>
                            <button type="button" class="btn btn-info" onclick="printPayment()">
                                <i class="fas fa-print me-1"></i> Print Receipt
                            </button>
                            @if($payment->status == 'active')
                            <button type="button" class="btn btn-warning" onclick="sendReminder({{ $payment->id }})">
                                <i class="fas fa-bell me-1"></i> Send Expiry Reminder
                            </button>
                            @endif
                            @if($payment->status == 'expired')
                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $payment->id }})">
                                <i class="fas fa-trash me-1"></i> Delete Record
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Similar Payments -->
                @if($similarPayments = \App\Models\AirtimePayment::where('station_id', $payment->station_id)
                    ->where('id', '!=', $payment->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get())
                    @if($similarPayments->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-history me-2"></i> Previous Topups for this Station</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($similarPayments as $prev)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>KES {{ number_format($prev->amount, 2) }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ \Carbon\Carbon::parse($prev->topup_date)->format('d/m/Y') }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge {{ $prev->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($prev->status) }}
                                            </span>
                                            <br>
                                            <a href="{{ route('payments.airtime.details', $prev->id) }}" 
                                               class="small text-primary text-decoration-none">
                                                View <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this airtime payment record?</p>
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Permanently</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .info-group {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .info-group:hover {
            background: #e9ecef;
            transform: translateX(2px);
        }
        .avatar-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d6efd, #0099ff);
        }
        @media print {
            .btn, .navbar, .dashboard-header .col-md-6:last-child, .card-header .btn, .quick-actions, .modal {
                display: none !important;
            }
            .info-group {
                background: none !important;
                border: 1px solid #ddd;
            }
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function copyToClipboard(text) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                // Optional: Show toast notification
                alert('Copied to clipboard: ' + text);
            });
        }

        function printPayment() {
            window.print();
        }

        function sendReminder(paymentId) {
            if (confirm('Send expiry reminder to station contact?')) {
                fetch(`/payments/airtime/${paymentId}/remind`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          alert('Reminder sent successfully!');
                      } else {
                          alert('Failed to send reminder: ' + (data.message || 'Unknown error'));
                      }
                  }).catch(error => {
                      alert('Error sending reminder');
                  });
            }
        }

        function confirmDelete(paymentId) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/payments/airtime/${paymentId}`;
            modal.show();
        }
    </script>
    @endpush
@endsection