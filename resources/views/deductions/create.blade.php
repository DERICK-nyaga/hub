@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Deduction Transaction</h1>

    <form action="{{ route('deductions.store') }}" method="POST">
        @csrf

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee *</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}|{{ $employee->first_name }}|{{ $employee->last_name }}" data-station-id="{{ $employee->station_id }}">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="station_id" id="station_id" value="{{ old('station_id') }}">
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Transaction Type *</label>
            <select name="type" id="type" class="form-select" required>
                <option value="">Select Type</option>
                @foreach($types as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="amount">Amount</label>
            <input type="number" step="0.01" class="form-control"
                   id="amount" name="amount" value="{{ old('amount') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Balance</label>
            <div id="current-balance-display" class="form-control-plaintext border rounded p-2 ">
                <span class="text-muted">Select an employee to see current balance</span>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="reason">Reason</label>
            <input type="text" class="form-control"
                   id="reason" name="reason" value="{{ old('reason') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>

        <div class="form-group mb-3" id="order_number_group">
            <label for="order_number">Order Number</label>
            <input type="text" class="form-control"
                   id="order_number" name="order_number" value="{{ old('order_number') }}" >
        </div>

        <div class="form-group mb-3">
            <label for="transaction_date">Transaction Date</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date" required
                   value="{{ old('transaction_date', now()->format('Y-m-d')) }}">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@vite('resources/js/deductions.js')
@endsection
