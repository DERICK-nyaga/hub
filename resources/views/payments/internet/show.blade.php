@extends('layouts.app')

@section('title', 'Internet Payment Details')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Internet Payment Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('payments.internet.edit', $payment->id) }}" class="btn btn-sm btn-primary me-2">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('payments.internet.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Main Payment Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Station:</label>
                            <p class="mb-0">{{ $payment->station->name }}</p>
                            <small class="text-muted">Code: {{ $payment->station->code ?? 'N/A' }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Provider:</label>
                            <p class="mb-0">{{ $payment->provider->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Account Number:</label>
                            <p class="mb-0"><code>{{ $payment->account_number }}</code></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Billing Month:</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($payment->billing_month)->format('F Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Due Date:</label>
                            <p class="mb-0 @if($payment->is_overdue) text-danger fw-bold @endif">
                                {{ $payment->due_date->format('d/m/Y') }}
                                @if($payment->is_overdue)
                                    <span class="badge bg-danger ms-2">Overdue by {{ $payment->days_overdue }} days</span>
                                @elseif($payment->is_due_soon)
                                    <span class="badge bg-warning ms-2">Due in {{ now()->diffInDays($payment->due_date) }} days</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status:</label>
                            <p class="mb-0">
                                <span class="badge @if($payment->status == 'paid') bg-success @elseif($payment->status == 'overdue') bg-danger @elseif($payment->status == 'pending') bg-warning @else bg-secondary @endif" style="font-size: 0.9rem;">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Amount Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Current Amount:</label>
                            <p class="mb-0 h4">KES {{ number_format($payment->amount, 2) }}</p>
                        </div>
                        @if($payment->previous_balance > 0)
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Previous Balance:</label>
                            <p class="mb-0 h5 text-danger">KES {{ number_format($payment->previous_balance, 2) }}</p>
                        </div>
                        @endif
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Total Due:</label>
                            <p class="mb-0 h3 @if($payment->total_due > 0) text-primary @endif">
                                KES {{ number_format($payment->total_due, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information (if paid) -->
            @if($payment->status == 'paid' && $payment->payment_date)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Payment Date:</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Payment Method:</label>
                            <p class="mb-0">{{ $payment->payment_method ?? 'M-Pesa' }}</p>
                        </div>
                        @if($payment->mpesa_receipt)
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">M-Pesa Receipt:</label>
                            <p class="mb-0"><code>{{ $payment->mpesa_receipt }}</code></p>
                        </div>
                        @endif
                        @if($payment->transaction_id)
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Transaction ID:</label>
                            <p class="mb-0"><code>{{ $payment->transaction_id }}</code></p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($payment->invoice_notes)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $payment->invoice_notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($payment->status != 'paid')
                        <button type="button" class="btn btn-success" onclick="markAsPaid({{ $payment->id }})">
                            <i class="bi bi-check-circle"></i> Mark as Paid
                        </button>
                        @endif

                        <button type="button" class="btn btn-info" onclick="sendReminder({{ $payment->id }})">
                            <i class="bi bi-envelope"></i> Send Reminder
                        </button>

                        <a href="{{ route('payments.internet.edit', $payment->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Payment
                        </a>

                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Created:</td>
                            <td class="text-end">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Last Updated:</td>
                            <td class="text-end">{{ $payment->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Payment ID:</td>
                            <td class="text-end"><code>#{{ $payment->id }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Payment as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Payment Method Selection -->
                <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="M-Pesa">M-Pesa</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <!-- M-Pesa Phone Input (hidden by default) -->
                <div id="mpesaPhoneSection" style="display: none;">
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">M-Pesa Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">+254</span>
                            <input type="text" class="form-control" id="phone_number" name="phone_number"
                                   placeholder="712345678" pattern="[0-9]{9}" maxlength="9">
                        </div>
                        <small class="text-muted">Enter phone number without the leading 0 or +254</small>
                    </div>
                </div>

                <!-- Manual Payment Fields (for non-M-Pesa methods) -->
                <div id="manualPaymentFields">
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label for="mpesa_receipt" class="form-label">Receipt/Reference (optional)</label>
                        <input type="text" class="form-control" id="mpesa_receipt" name="mpesa_receipt">
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID (optional)</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="mpesaLoading" style="display: none;" class="text-center py-3">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0">Processing M-Pesa payment...</p>
                    <small class="text-muted">Please check your phone to complete the transaction</small>
                </div>

                <!-- Success Message -->
                <div id="mpesaSuccess" style="display: none;" class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Payment initiated successfully! Please complete the transaction on your phone.
                </div>

                <!-- Error Message -->
                <div id="mpesaError" style="display: none;" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> <span id="errorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="processPaymentBtn" onclick="processPayment()">
                    Process Payment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPaymentId = null;
    let mpesaCheckInterval = null;

    // Show/hide M-Pesa phone input based on payment method selection
    document.getElementById('payment_method').addEventListener('change', function() {
        const mpesaSection = document.getElementById('mpesaPhoneSection');
        const manualFields = document.getElementById('manualPaymentFields');

        if (this.value === 'M-Pesa') {
            mpesaSection.style.display = 'block';
            manualFields.style.display = 'none';
            document.getElementById('payment_date').value = ''; // Clear manual fields
            document.getElementById('mpesa_receipt').value = '';
            document.getElementById('transaction_id').value = '';
        } else {
            mpesaSection.style.display = 'none';
            manualFields.style.display = 'block';
            document.getElementById('payment_date').value = '{{ date('Y-m-d') }}'; // Reset to today
        }
    });

    function markAsPaid(paymentId) {
        currentPaymentId = paymentId;
        const modal = new bootstrap.Modal(document.getElementById('markPaidModal'));

        // Reset modal state
        document.getElementById('mpesaPhoneSection').style.display = 'none';
        document.getElementById('manualPaymentFields').style.display = 'block';
        document.getElementById('mpesaLoading').style.display = 'none';
        document.getElementById('mpesaSuccess').style.display = 'none';
        document.getElementById('mpesaError').style.display = 'none';
        document.getElementById('payment_method').value = 'M-Pesa'; // Default to M-Pesa

        modal.show();
    }

    function processPayment() {
        const paymentMethod = document.getElementById('payment_method').value;

        if (paymentMethod === 'M-Pesa') {
            processMpesaPayment();
        } else {
            processManualPayment();
        }
    }

    function processMpesaPayment() {
        const phoneNumber = document.getElementById('phone_number').value;

        // Validate phone number
        if (!phoneNumber || phoneNumber.length !== 9 || !/^\d+$/.test(phoneNumber)) {
            showError('Please enter a valid phone number (9 digits)');
            return;
        }

        // Get payment amount from the page
        const amountElement = document.querySelector('.h3.text-primary');
        let amount = 0;

        if (amountElement) {
            const amountText = amountElement.textContent;
            const match = amountText.match(/[\d,]+\.\d{2}/);
            if (match) {
                amount = parseFloat(match[0].replace(/,/g, ''));
            }
        }

        // Show loading
        document.getElementById('mpesaLoading').style.display = 'block';
        document.getElementById('processPaymentBtn').disabled = true;
        document.getElementById('mpesaError').style.display = 'none';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showError('CSRF token not found');
            document.getElementById('mpesaLoading').style.display = 'none';
            document.getElementById('processPaymentBtn').disabled = false;
            return;
        }

        // Initiate STK Push
        fetch(`/payments/internet/${currentPaymentId}/mpesa-payment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                phone_number: phoneNumber,
                amount: amount
            })
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // If not JSON, get the text response
                const text = await response.text();
                throw new Error(`Expected JSON but got HTML. Status: ${response.status}. First 200 chars: ${text.substring(0, 200)}`);
            }

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                document.getElementById('mpesaLoading').style.display = 'none';
                document.getElementById('mpesaSuccess').style.display = 'block';

                // Start checking transaction status
                startTransactionCheck(data.transaction_id);
            } else {
                throw new Error(data.message || 'Failed to initiate payment');
            }
        })
        .catch(error => {
            console.error('M-Pesa Error:', error);
            document.getElementById('mpesaLoading').style.display = 'none';
            document.getElementById('processPaymentBtn').disabled = false;
            showError(error.message);
        });
    }

    function startTransactionCheck(transactionId) {
        let attempts = 0;
        const maxAttempts = 30; // Check for 30 times (every 5 seconds = 2.5 minutes)

        mpesaCheckInterval = setInterval(() => {
            attempts++;

            fetch(`/payments/internet/check-transaction/${transactionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        // Transaction successful - close modal and refresh
                        clearInterval(mpesaCheckInterval);
                        showSuccessAndRefresh(data);
                    } else if (data.status === 'failed' || attempts >= maxAttempts) {
                        // Transaction failed or timeout
                        clearInterval(mpesaCheckInterval);
                        document.getElementById('mpesaSuccess').style.display = 'none';
                        showError('Transaction timeout or failed. Please try again.');
                        document.getElementById('processPaymentBtn').disabled = false;
                    }
                    // If still pending, continue checking
                })
                .catch(error => {
                    console.error('Error checking transaction:', error);
                });
        }, 5000); // Check every 5 seconds
    }

    function processManualPayment() {
        const form = document.getElementById('markPaidForm');
        const formData = new FormData(form);

        fetch(`/payments/internet/${currentPaymentId}/mark-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and refresh page
                bootstrap.Modal.getInstance(document.getElementById('markPaidModal')).hide();
                location.reload();
            } else {
                showError(data.message || 'Failed to update payment');
            }
        })
        .catch(error => {
            showError('Failed to process payment');
        });
    }

    function showSuccessAndRefresh(data) {
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('markPaidModal')).hide();

        // Show success message
        alert('Payment successful! Transaction ID: ' + data.transaction_id);

        // Refresh page to show updated data
        location.reload();
    }

    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('mpesaError').style.display = 'block';
    }

    // Clean up interval when modal is hidden
    document.getElementById('markPaidModal').addEventListener('hidden.bs.modal', function () {
        if (mpesaCheckInterval) {
            clearInterval(mpesaCheckInterval);
        }
        document.getElementById('processPaymentBtn').disabled = false;
    });
</script>
@endpush
