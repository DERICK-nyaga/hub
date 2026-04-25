@extends('layouts.app')

@section('content')
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0" id="new-employee"><i class="fas fa-user-plus me-2"></i>Add New Employee</h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('employees.store') }}" method="POST" id="employeeForm">
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

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label">Employee ID</label>
                                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror"
                                        id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="station_id" class="form-label">Station</label>
                                    <select class="form-select @error('station_id') is-invalid @enderror"
                                            id="station_id" name="station_id" required>
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

                                <div class="col-md-6">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="text" class="form-control @error('position') is-invalid @enderror"
                                           id="position" name="position" value="{{ old('position') }}" required>
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="salary" class="form-label">Salary (KES)</label>
                                    <input type="number" step="0.01" class="form-control @error('salary') is-invalid @enderror"
                                           id="salary" name="salary" value="{{ old('salary') }}" required>
                                    @error('salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="deductions" class="form-label">Deductions (KES)</label>
                                    <input type="number" step="0.01" class="form-control @error('deduction_balance') is-invalid @enderror"
                                           id="deduction_balance" name="deduction_balance" value="{{ old('deduction_balance') }}" required>
                                    @error('deduction_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="hire_date" class="form-label">Hire Date</label>
                                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                           id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status" name="status" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="leaveSection" class="conditional-section hidden-section col-12 mt-3">
                                    <div class="card conditional-card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-calendar-alt me-2"></i>Leave Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="leave_start" class="form-label">Leave Start Date</label>
                                                    <input type="date" class="form-control @error('leave_start') is-invalid @enderror"
                                                           id="leave_start" name="leave_start" value="{{ old('leave_start') }}">
                                                    @error('leave_start')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="leave_end" class="form-label">Leave End Date</label>
                                                    <input type="date" class="form-control @error('leave_end') is-invalid @enderror"
                                                           id="leave_end" name="leave_end" value="{{ old('leave_end') }}">
                                                    @error('leave_end')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="terminationSection" class="conditional-section hidden-section col-12 mt-3">
                                    <div class="card conditional-card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-calendar-times me-2"></i>Termination Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="termination_date" class="form-label">Termination Date</label>
                                                    <input type="date" class="form-control @error('termination_date') is-invalid @enderror"
                                                           id="termination_date" name="termination_date" value="{{ old('termination_date') }}">
                                                    @error('termination_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="last_working_day" class="form-label">Last Working Day</label>
                                                    <input type="date" class="form-control @error('last_working_day') is-invalid @enderror"
                                                           id="last_working_day" name="last_working_day" value="{{ old('last_working_day') }}">
                                                    @error('last_working_day')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save me-2"></i>Save Employee
                                    </button>
                                    <a href="{{ route('employees.index') }}" class="btn btn-secondary px-4">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@vite('resources/js/employee-statuses.js')

@endsection

