@extends('layouts.app')
@section('title', 'Create New Payment')
@section('content')

@php
    $isEdit = isset($payment);
    $action = $isEdit ? route('payments.update', $payment) : route('payments.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method($method)

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="title" class="form-label">Title*</label>
                <input type="text" class="form-control" id="title" name="title"
                       value="{{ old('title', $payment->title ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label for="station_id" class="form-label">Station*</label>
                <select class="form-select" id="station_id" name="station_id" required>
                    <option value="">Select Station</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}"
                            {{ old('station_id', $payment->station_id ?? '') == $station->id ? 'selected' : '' }}>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="vendor_id" class="form-label">Vendor</label>
                <select class="form-select" id="vendor_id" name="vendor_id">
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}"
                            {{ old('vendor_id', $payment->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Type*</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Select Type</option>
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}"
                            {{ old('type', $payment->type ?? '') == $key ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="amount" class="form-label">Amount*</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                       value="{{ old('amount', $payment->amount ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date*</label>
                <input type="date"
                    name="due_date"
                    id="due_date"
                    value="{{ old('due_date', $default_due_date) }}"
                    class="form-control @error('due_date') is-invalid @enderror"
                    required>
                <div for="status" class="form-label">Status</div>
                <select name="status" id="status" class="form-control">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="attachment" class="form-label">Attachment</label>
                <input type="file" class="form-control" id="attachment" name="attachment">
                @if($isEdit && $payment->attachment_path)
                    <div class="mt-2">
                        <a href="{{ Storage::url($payment->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file"></i> View Current Attachment
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $payment->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_recurring" name="is_recurring"
                       {{ old('is_recurring', $payment->is_recurring ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_recurring">Recurring Payment</label>
            </div>

            <div class="mb-3" id="recurrence_fields" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <label for="recurrence" class="form-label">Recurrence*</label>
                        <select class="form-select" id="recurrence" name="recurrence">
                            <option value="">Select Frequency</option>
                            <option value="weekly" {{ old('recurrence', $payment->recurrence ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('recurrence', $payment->recurrence ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ old('recurrence', $payment->recurrence ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="recurrence_ends_at" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="recurrence_ends_at" name="recurrence_ends_at"
                               value="{{ old('recurrence_ends_at', isset($payment) && $payment->recurrence_ends_at ? $payment->recurrence_ends_at->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Update' : 'Create' }} Payment
        </button>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

@endsection
{{-- @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurrenceFields = document.getElementById('recurrence_fields');

    function toggleRecurrenceFields() {
        recurrenceFields.style.display = isRecurringCheckbox.checked ? 'block' : 'none';
        document.getElementById('recurrence').required = isRecurringCheckbox.checked;
    }

    toggleRecurrenceFields();

    isRecurringCheckbox.addEventListener('change', toggleRecurrenceFields);
});
</script>
@endpush --}}


