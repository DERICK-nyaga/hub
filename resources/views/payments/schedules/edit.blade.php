@extends('layouts.app')

@section('title', 'Edit Payment Schedule')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Edit Payment Schedule</h1>
            <p class="text-muted">Update schedule #{{ $schedule->id }}</p>
        </div>
        <a href="{{ route('payments.schedules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Schedules
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('payments.schedules.update', $schedule->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="station_id" class="form-label required">Station *</label>
                        <select name="station_id" id="station_id" class="form-select @error('station_id') is-invalid @enderror" required>
                            <option value="">Select Station</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->station_id ?? $station->id }}" 
                                    {{ old('station_id', $schedule->station_id) == ($station->station_id ?? $station->id) ? 'selected' : '' }}>
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
                            <option value="internet" {{ old('payment_type', $schedule->payment_type) == 'internet' ? 'selected' : '' }}>Internet Payment</option>
                            <option value="airtime" {{ old('payment_type', $schedule->payment_type) == 'airtime' ? 'selected' : '' }}>Airtime Payment</option>
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
                               value="{{ old('scheduled_date', $schedule->scheduled_date) }}" required>
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="scheduled_amount" class="form-label required">Scheduled Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <input type="number" step="0.01" name="scheduled_amount" id="scheduled_amount" 
                                   class="form-control @error('scheduled_amount') is-invalid @enderror" 
                                   value="{{ old('scheduled_amount', $schedule->scheduled_amount) }}" required>
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
                            <option value="monthly" {{ old('frequency', $schedule->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency', $schedule->frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('frequency', $schedule->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('frequency', $schedule->frequency) == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_recurring" id="is_recurring" 
                                   class="form-check-input" value="1" 
                                   {{ old('is_recurring', $schedule->is_recurring) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recurring">
                                Recurring Schedule
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="auto_pay" id="auto_pay" 
                                   class="form-check-input" value="1" 
                                   {{ old('auto_pay', $schedule->auto_pay) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_pay">
                                Auto Pay
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" 
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $schedule->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Schedule
                    </button>
                    <a href="{{ route('payments.schedules.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection