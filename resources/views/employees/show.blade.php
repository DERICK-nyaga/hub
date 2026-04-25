@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="new-employee">Employee Details: {{ $employee->full_name }}</h5>
                    <div>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Full Name:</span>
                                    <strong>{{ $employee->full_name }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Email:</span>
                                    <strong>{{ $employee->email }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Phone:</span>
                                    <strong>{{ $employee->phone }}</strong>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h6>Employment Details</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Position:</span>
                                    <strong>{{ $employee->position }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Station:</span>
                                    <strong>{{ $employee->station->name ?? 'N/A' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Status:</span>
                                    <strong>
                                        <span class="badge bg-{{
                                            $employee->status == 'active' ? 'success' :
                                            ($employee->status == 'on_leave' ? 'warning' : 'danger')
                                        }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Financial Information</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Salary:</span>
                                    <strong>KES {{ number_format($employee->salary, 2) }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Hire Date:</span>
                                    <strong>{{ $employee->hire_date->format('M d, Y') }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
