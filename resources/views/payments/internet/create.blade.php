@extends('layouts.app')

@section('title', 'New Internet Payment')

@section('content')
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-wifi me-2"></i> New Internet Payment</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.internet.index') }}">Internet Payments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">New Payment</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('payments.internet.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Internet Payment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.internet.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="station_id" class="form-label">Station *</label>
                                <select class="form-select @error('station_id') is-invalid @enderror" id="station_id" name="station_id" required>
                                    <option value="">Select Station</option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->station_id }}" {{ old('station_id') == $station->station_id ? 'selected' : '' }}>
                                            {{ $station->name }} - {{ $station->location }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('station_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vendor_id" class="form-label">Internet Provider *</label>
                                <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id" required>
                                    <option value="">Select Provider</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->vendor_id }}" {{ old('vendor_id') == $provider->vendor_id ? 'selected' : '' }}
                                            data-paybill="{{ $provider->paybill_number }}"
                                            data-contact="{{ $provider->support_contact }}"
                                            data-amount="{{ $provider->standard_amount }}">
                                            {{ $provider->name }} ({{ $provider->category }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="account_number" class="form-label">Account Number *</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number"
                                       value="{{ old('account_number') }}"
                                       placeholder="e.g., ISP-001-1234" required>
                                <small class="text-muted">Account number as per provider billing</small>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="amount" class="form-label">Amount (KES) *</label>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                       id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="previous_balance" class="form-label">Previous Balance (KES)</label>
                                <input type="number" step="0.01" class="form-control @error('previous_balance') is-invalid @enderror"
                                       id="previous_balance" name="previous_balance" value="{{ old('previous_balance', 0) }}">
                                @error('previous_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="billing_month" class="form-label">Billing Month *</label>
                                <input type="month" class="form-control @error('billing_month') is-invalid @enderror"
                                       id="billing_month" name="billing_month"
                                       value="{{ old('billing_month', date('Y-m')) }}" required>
                                @error('billing_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="due_date" class="form-label">Due Date *</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="payment_date" class="form-label">Payment Date (if paid)</label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                       id="payment_date" name="payment_date" value="{{ old('payment_date') }}">
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                    <option value="">Select Method</option>
                                    <option value="M-Pesa" {{ old('payment_method') == 'M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
                                    <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Payment Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="mpesa_receipt" class="form-label">M-Pesa Receipt No</label>
                                <input type="text" class="form-control @error('mpesa_receipt') is-invalid @enderror"
                                       id="mpesa_receipt" name="mpesa_receipt" value="{{ old('mpesa_receipt') }}"
                                       placeholder="e.g., RCPT12345678">
                                @error('mpesa_receipt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="provider-info" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading mb-3"><i class="fas fa-info-circle me-2"></i>Provider Information</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-receipt me-1"></i> Paybill Number:</strong><br>
                                            <span id="display-paybill" class="text-primary fw-bold">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-phone me-1"></i> Contact Number:</strong><br>
                                            <span id="display-contact" class="text-primary fw-bold">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-money-bill-wave me-1"></i> Standard Amount:</strong><br>
                                            <span id="display-amount" class="text-primary fw-bold">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="invoice_notes" class="form-label">Invoice Notes</label>
                                <textarea class="form-control @error('invoice_notes') is-invalid @enderror"
                                          id="invoice_notes" name="invoice_notes" rows="3"
                                          placeholder="Enter any notes about this payment, e.g., 'Service activated', 'Speed upgraded', etc.">{{ old('invoice_notes') }}</textarea>
                                @error('invoice_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="create_schedule" id="create_schedule" value="1"
                                           {{ old('create_schedule') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="create_schedule">
                                        <i class="fas fa-calendar-plus me-1"></i> Create recurring monthly schedule for this payment
                                    </label>
                                    <small class="text-muted d-block">This will automatically create a schedule for next month's payment</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('payments.internet.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Payment Record
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const vendorSelect = $('#vendor_id');
        const amountInput = $('#amount');
        const dueDateInput = $('#due_date');
        const providerInfo = $('#provider-info');
        const displayPaybill = $('#display-paybill');
        const displayContact = $('#display-contact');
        const displayAmount = $('#display-amount');

        if (!dueDateInput.val()) {
            const today = new Date();
            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            dueDateInput.val(nextMonth.toISOString().split('T')[0]);
        }

        vendorSelect.on('change', function() {
            const selectedOption = $(this).find(':selected');
            const paybill = selectedOption.data('paybill');
            const contact = selectedOption.data('contact');
            const standardAmount = selectedOption.data('amount');

            if (this.value) {
                providerInfo.show();
                displayPaybill.text(paybill || 'Not set');
                displayContact.text(contact || 'Not set');
                displayAmount.text(standardAmount ? 'KES ' + parseFloat(standardAmount).toLocaleString('en-KE') : 'Not set');

                if (standardAmount && !amountInput.val()) {
                    amountInput.val(standardAmount);
                }
            } else {
                providerInfo.hide();
            }
        });

        if (vendorSelect.val()) {
            vendorSelect.trigger('change');
        }

        const previousBalanceInput = $('#previous_balance');

        function updateAmountLabel() {
            const amount = parseFloat(amountInput.val()) || 0;
            const previousBalance = parseFloat(previousBalanceInput.val()) || 0;
            const total = amount + previousBalance;

            const amountLabel = $('label[for="amount"]');
            const originalText = 'Amount (KES) *';

            if (previousBalance > 0) {
                amountLabel.html(`${originalText} <small class="text-muted">(Total due: KES ${total.toLocaleString('en-KE', {minimumFractionDigits: 2})})</small>`);
            } else {
                amountLabel.html(originalText);
            }
        }

        amountInput.on('input', updateAmountLabel);
        previousBalanceInput.on('input', updateAmountLabel);

        const stationSelect = $('#station_id');
        const accountNumberInput = $('#account_number');

        function generateAccountNumber() {
            if (stationSelect.val() && vendorSelect.val()) {
                const selectedOption = vendorSelect.find(':selected');
                const stationId = stationSelect.val();
                const providerName = selectedOption.text().split(' (')[0];
                const accountPrefix = providerName.substring(0, 3).toUpperCase();
                accountNumberInput.val(`${accountPrefix}-${stationId}`);
            }
        }

        stationSelect.on('change', generateAccountNumber);
        vendorSelect.on('change', generateAccountNumber);
    });
</script>
@endpush
