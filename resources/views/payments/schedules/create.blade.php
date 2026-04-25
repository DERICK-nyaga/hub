@extends('layouts.app')

@section('title', 'Create Payment Schedule')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Create Payment Schedule</h1>
            <p class="text-muted">Set up recurring or one-time payment schedules for stations.</p>
        </div>
        <a href="{{ route('payments.schedules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Schedules
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('payments.schedules.store') }}" method="POST" id="scheduleForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="station_id" class="form-label required">Station *</label>
                        <select name="station_id" id="station_id" class="form-select @error('station_id') is-invalid @enderror" required>
                            <option value="">Select Station</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->station_id ?? $station->id }}" {{ old('station_id') == ($station->station_id ?? $station->id) ? 'selected' : '' }}>
                                    {{ $station->name }} ({{ $station->code ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('station_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_type" class="form-label required">Payment Type *</label>
                        <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                            <option value="">Select Payment Type</option>
                            <option value="internet" {{ old('payment_type') == 'internet' ? 'selected' : '' }}>Internet Payment</option>
                            <option value="airtime" {{ old('payment_type') == 'airtime' ? 'selected' : '' }}>Airtime Payment</option>
                        </select>
                        @error('payment_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="scheduled_date" class="form-label required">Scheduled Date *</label>
                        <input type="date" name="scheduled_date" id="scheduled_date" 
                               class="form-control @error('scheduled_date') is-invalid @enderror" 
                               value="{{ old('scheduled_date', now()->addDays(7)->format('Y-m-d')) }}" required>
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Date when this payment should be processed.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="scheduled_amount" class="form-label required">Scheduled Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <input type="number" step="0.01" name="scheduled_amount" id="scheduled_amount" 
                                   class="form-control @error('scheduled_amount') is-invalid @enderror" 
                                   value="{{ old('scheduled_amount') }}" placeholder="0.00" required>
                        </div>
                        @error('scheduled_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="frequency" class="form-label required">Frequency *</label>
                        <select name="frequency" id="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
                            <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly (Every 3 months)</option>
                            <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('frequency') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="customIntervalDiv" style="display: none;">
                        <label for="custom_interval" class="form-label">Custom Interval (Days)</label>
                        <input type="number" name="custom_interval" id="custom_interval" 
                               class="form-control" placeholder="e.g., 45" min="1">
                        <small class="form-text text-muted">Number of days between payments.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_recurring" id="is_recurring" 
                                   class="form-check-input @error('is_recurring') is-invalid @enderror" 
                                   value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recurring">
                                Recurring Schedule
                            </label>
                            @error('is_recurring')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">If checked, this schedule will repeat based on frequency.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="auto_pay" id="auto_pay" 
                                   class="form-check-input @error('auto_pay') is-invalid @enderror" 
                                   value="1" {{ old('auto_pay') ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_pay">
                                Auto Pay (Automatic Processing)
                            </label>
                            @error('auto_pay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">If enabled, payment will be processed automatically.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" 
                              class="form-control @error('description') is-invalid @enderror" 
                              placeholder="Optional description or notes about this schedule">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> For PostgreSQL, ensure all dates are in YYYY-MM-DD format. 
                    Recurring schedules will automatically generate payment records based on the frequency.
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Schedule
                    </button>
                    <a href="{{ route('payments.schedules.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('frequency').addEventListener('change', function() {
    const customDiv = document.getElementById('customIntervalDiv');
    if (this.value === 'custom') {
        customDiv.style.display = 'block';
        document.getElementById('custom_interval').required = true;
    } else {
        customDiv.style.display = 'none';
        document.getElementById('custom_interval').required = false;
    }
});

// Trigger on page load if custom is selected
if (document.getElementById('frequency').value === 'custom') {
    document.getElementById('customIntervalDiv').style.display = 'block';
    document.getElementById('custom_interval').required = true;
}

// Client-side validation for PostgreSQL date format
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    const dateInput = document.getElementById('scheduled_date');
    const dateValue = dateInput.value;
    
    // Validate date format (YYYY-MM-DD)
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(dateValue)) {
        e.preventDefault();
        alert('Please enter date in YYYY-MM-DD format');
        return false;
    }
    
    // Validate amount is positive
    const amount = parseFloat(document.getElementById('scheduled_amount').value);
    if (isNaN(amount) || amount <= 0) {
        e.preventDefault();
        alert('Please enter a valid positive amount');
        return false;
    }
    
    // Validate custom interval if applicable
    const frequency = document.getElementById('frequency').value;
    if (frequency === 'custom') {
        const interval = parseInt(document.getElementById('custom_interval').value);
        if (isNaN(interval) || interval < 1) {
            e.preventDefault();
            alert('Please enter a valid custom interval (minimum 1 day)');
            return false;
        }
    }
});
</script>
@endpush
@endsection