@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Record New Loss</h1>

    <form action="{{ route('losses.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                        @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>

            <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="station_id">Station</label>
                    <select class="form-control" id="station_id" name="station_id" required>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="employee_id">Employee Responsible (if applicable)</label>
                    <select class="form-control" id="employee_id" name="employee_id">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="type">Loss Type</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="cash">Cash</option>
                        <option value="inventory">Inventory</option>
                        <option value="equipment">Equipment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="date_occurred">Date Occurred</label>
                    <input type="date" class="form-control" id="date_occurred" name="date_occurred" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Record Loss</button>
        <a href="{{ route('losses.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
