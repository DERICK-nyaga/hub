@extends('layouts.app')

@section('title', 'Edit Internet Payment')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Internet Payment</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('payments.internet.show', $payment->id) }}" class="btn btn-sm btn-info me-2">
                <i class="bi bi-eye"></i> View Details
            </a>
            <a href="{{ route('payments.internet.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payments.internet.update', $payment->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="station_id" class="form-label">Station <span class="text-danger">*</span></label>
                                <select class="form-select @error('station_id') is-invalid @enderror" id="station_id" name="station_id" required>
                                    <option value="">Select Station</option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->station_id }}" {{ old('station_id', $payment->station_id) == $station->station_id ? 'selected' : '' }}>
                                            {{ $station->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('station_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vendor_id" class="form-label">Provider <span class="text-danger">*</span></label>
                                <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id" required>
                                    <option value="">Select Provider</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->vendor_id }}" {{ old('vendor_id', $payment->vendor_id) == $provider->vendor_id ? 'selected' : '' }}>
                                            {{ $provider->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number"
                                       value="{{ old('account_number', $payment->account_number) }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="billing_month" class="form-label">Billing Month <span class="text-danger">*</span></label>
                                <input type="month" class="form-control @error('billing_month') is-invalid @enderror"
                                       id="billing_month" name="billing_month"
                                       value="{{ old('billing_month', \Carbon\Carbon::parse($payment->billing_month)->format('Y-m')) }}" required>
                                @error('billing_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date"
                                       value="{{ old('due_date', $payment->due_date->format('Y-m-d')) }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('status', $payment->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ old('status', $payment->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="cancelled" {{ old('status', $payment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="previous_balance" class="form-label">Previous Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('previous_balance') is-invalid @enderror"
                                           id="previous_balance" name="previous_balance" value="{{ old('previous_balance', $payment->previous_balance ?? 0) }}">
                                </div>
                                @error('previous_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Due</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="text" class="form-control" id="total_due" readonly
                                           value="{{ number_format($payment->total_due, 2) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details Section (shown only if status is paid) -->
                        <div id="paymentDetailsSection" style="{{ old('status', $payment->status) == 'paid' ? '' : 'display: none;' }}">
                            <hr>
                            <h6 class="mb-3">Payment Details</h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                           id="payment_date" name="payment_date"
                                           value="{{ old('payment_date', $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : '') }}">
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                        <option value="">Select Method</option>
                                        <option value="M-Pesa" {{ old('payment_method', $payment->payment_method) == 'M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
                                        <option value="Bank Transfer" {{ old('payment_method', $payment->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="Cash" {{ old('payment_method', $payment->payment_method) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="Cheque" {{ old('payment_method', $payment->payment_method) == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="mpesa_receipt" class="form-label">M-Pesa Receipt</label>
                                    <input type="text" class="form-control @error('mpesa_receipt') is-invalid @enderror"
                                           id="mpesa_receipt" name="mpesa_receipt"
                                           value="{{ old('mpesa_receipt', $payment->mpesa_receipt) }}">
                                    @error('mpesa_receipt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="transaction_id" class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control @error('transaction_id') is-invalid @enderror"
                                           id="transaction_id" name="transaction_id"
                                           value="{{ old('transaction_id', $payment->transaction_id) }}">
                                    @error('transaction_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="invoice_notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('invoice_notes') is-invalid @enderror"
                                      id="invoice_notes" name="invoice_notes" rows="3">{{ old('invoice_notes', $payment->invoice_notes) }}</textarea>
                            @error('invoice_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Payment
                            </button>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Help & Information</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-info-circle"></i> Fields marked with <span class="text-danger">*</span> are required.</p>
                    <p><i class="bi bi-calendar"></i> The billing month determines the period this payment is for.</p>
                    <p><i class="bi bi-exclamation-triangle text-warning"></i> Previous balance will be added to the total amount due.</p>
                    <p><i class="bi bi-credit-card"></i> Payment details only appear when status is set to "Paid".</p>
                </div>
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
                    <p>Are you sure you want to delete this internet payment?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('payments.internet.destroy', $payment->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Toggle payment details section based on status
    document.getElementById('status').addEventListener('change', function() {
        const paymentSection = document.getElementById('paymentDetailsSection');
        if (this.value === 'paid') {
            paymentSection.style.display = 'block';
        } else {
            paymentSection.style.display = 'none';
        }
    });

    // Calculate total due
    function calculateTotalDue() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const previousBalance = parseFloat(document.getElementById('previous_balance').value) || 0;
        const total = amount + previousBalance;
        document.getElementById('total_due').value = total.toFixed(2);
    }

    document.getElementById('amount').addEventListener('input', calculateTotalDue);
    document.getElementById('previous_balance').addEventListener('input', calculateTotalDue);

    function confirmDelete() {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
