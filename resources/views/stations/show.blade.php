@extends('layouts.app')

@section('title', 'Station Details - ' . $station->name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">{{ $station->name }}</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-map-marker-alt me-1"></i> {{ $station->address ?? 'No address provided' }}
                @if($station->code)
                <span class="mx-2">|</span>
                <i class="fas fa-hashtag me-1"></i> Code: {{ $station->code }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('payments.internet.create') }}?station_id={{ $station->station_id }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Payment
            </a>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Employees</h6>
                            <h2 class="mb-0">{{ $stats['total_employees'] ?? 0 }}</h2>
                            <small>Active: {{ $stats['active_employees'] ?? 0 }}</small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Paid</h6>
                            <h2 class="mb-0">KES {{ number_format(($stats['total_internet_paid'] ?? 0) + ($stats['total_airtime_paid'] ?? 0), 2) }}</h2>
                            <small>Internet: {{ number_format($stats['total_internet_paid'] ?? 0, 2) }}</small>
                        </div>
                        <i class="fas fa-credit-card fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pending Payments</h6>
                            <h2 class="mb-0">KES {{ number_format($stats['pending_payments'] ?? 0, 2) }}</h2>
                            <small>Due Soon: {{ $stats['due_soon_count'] ?? 0 }}</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Recent Activity</h6>
                            <h2 class="mb-0">{{ $stats['recent_payments_count'] ?? 0 }}</h2>
                            <small>Last 10 payments</small>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="stationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                        <i class="fas fa-info-circle me-1"></i> Station Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button" role="tab">
                        <i class="fas fa-users me-1"></i> Employees 
                        <span class="badge bg-secondary">{{ $station->employees->count() ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="internet-tab" data-bs-toggle="tab" data-bs-target="#internet" type="button" role="tab">
                        <i class="fas fa-globe me-1"></i> Internet Payments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="airtime-tab" data-bs-toggle="tab" data-bs-target="#airtime" type="button" role="tab">
                        <i class="fas fa-phone me-1"></i> Airtime Payments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="providers-tab" data-bs-toggle="tab" data-bs-target="#providers" type="button" role="tab">
                        <i class="fas fa-building me-1"></i> Service Providers
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedules-tab" data-bs-toggle="tab" data-bs-target="#schedules" type="button" role="tab">
                        <i class="fas fa-calendar-alt me-1"></i> Schedules
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="stationTabsContent">
                
                <!-- Station Information Tab -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Contact Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">Contact Person</th>
                                    <td>{{ $station->contact_person ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $station->contact_phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $station->contact_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge {{ ($station->status ?? 'active') == 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($station->status ?? 'Active') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Station Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">Station Code</th>
                                    <td>{{ $station->code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Opening Date</th>
                                    <td>
                                        {{-- FIXED: Added null check for opening_date --}}
                                        @if($station->opening_date)
                                            {{ \Carbon\Carbon::parse($station->opening_date)->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Region</th>
                                    <td>{{ $station->region ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Notes</th>
                                    <td>{{ $station->notes ?? 'No notes' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if($station->address)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Address</h5>
                            <p>{{ $station->address }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Employees Tab -->
                <div class="tab-pane fade" id="employees" role="tabpanel">
                    @if($station->employees->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No employees found for this station.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover" id="employeesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Hire Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($station->employees as $employee)
                                    <tr>
                                        <td>{{ $employee->employee_number ?? $employee->id }}</td>
                                        <td>
                                            <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                                        </td>
                                        <td>{{ $employee->position ?? 'N/A' }}</td>
                                        <td>{{ $employee->department ?? 'N/A' }}</td>
                                        <td>{{ $employee->phone ?? 'N/A' }}</td>
                                        <td>{{ $employee->email ?? 'N/A' }}</td>
                                        <td>
                                            {{-- FIXED: Added null check for hire_date --}}
                                            @if($employee->hire_date)
                                                {{ \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ ($employee->status ?? 'active') == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($employee->status ?? 'Active') }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewEmployee({{ $employee->employee_id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Internet Payments Tab -->
                <div class="tab-pane fade" id="internet" role="tabpanel">
                    @if($station->internetPayments->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No internet payments recorded.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Provider</th>
                                        <th>Account Number</th>
                                        <th>Amount</th>
                                        <th>Billing Month</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($station->internetPayments as $payment)
                                    <tr class="@if($payment->due_date < now() && $payment->status != 'paid') table-danger @endif">
                                        <td>{{ $payment->provider->name ?? 'N/A' }}</td>
                                        <td><code>{{ $payment->account_number }}</code></td>
                                        <td><strong>KES {{ number_format($payment->amount, 2) }}</strong></td>
                                        <td>
                                            {{-- FIXED: Added null check for billing_month --}}
                                            @if($payment->billing_month)
                                                {{ \Carbon\Carbon::parse($payment->billing_month)->format('M Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{-- FIXED: Added null check for due_date --}}
                                            @if($payment->due_date)
                                                {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status == 'paid' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- FIXED: Added null check for payment_date --}}
                                            @if($payment->payment_date)
                                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                            @else
                                                Not paid
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('payments.internet.show', $payment->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('payments.internet.index') }}?station_id={{ $station->station_id }}" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i> View All Internet Payments
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Airtime Payments Tab -->
                <div class="tab-pane fade" id="airtime" role="tabpanel">
                    @if($station->airtimePayments->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No airtime payments recorded.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mobile Number</th>
                                        <th>Network</th>
                                        <th>Amount</th>
                                        <th>Top-up Date</th>
                                        <th>Expected Expiry</th>
                                        <th>Status</th>
                                        <th>Transaction ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($station->airtimePayments as $payment)
                                    <tr class="@if($payment->expected_expiry < now() && $payment->status == 'active') table-warning @endif">
                                        <td><code>{{ $payment->mobile_number }}</code></td>
                                        <td>{{ $payment->network_provider }}</td>
                                        <td>KES {{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            {{-- FIXED: Added null check for topup_date --}}
                                            @if($payment->topup_date)
                                                {{ \Carbon\Carbon::parse($payment->topup_date)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{-- FIXED: Added null check for expected_expiry --}}
                                            @if($payment->expected_expiry)
                                                {{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </tr>
                                        <td>
                                            <span class="badge bg-{{ $payment->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td><small>{{ $payment->transaction_id ?? 'N/A' }}</small></td>
                                        <td>
                                            <a href="{{ route('payments.airtime.show', $payment->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('payments.airtime.index') }}?station_id={{ $station->station_id }}" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i> View All Airtime Payments
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Service Providers Tab -->
                <div class="tab-pane fade" id="providers" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Internet Service Providers</h5>
                            @if($station->serviceProviders->isEmpty())
                                <div class="alert alert-info">No service providers assigned.</div>
                            @else
                                <div class="list-group">
                                    @foreach($station->serviceProviders as $provider)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $provider->name }}</h6>
                                                <small class="text-muted">
                                                    Contract: {{ $provider->pivot->contract_number ?? 'N/A' }}<br>
                                                    Period: 
                                                    {{-- FIXED: Added null checks for dates --}}
                                                    @if($provider->pivot->start_date)
                                                        {{ \Carbon\Carbon::parse($provider->pivot->start_date)->format('d/m/Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                    - 
                                                    @if($provider->pivot->end_date)
                                                        {{ \Carbon\Carbon::parse($provider->pivot->end_date)->format('d/m/Y') }}
                                                    @else
                                                        Ongoing
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="badge bg-success">{{ $provider->pivot->status ?? 'active' }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>General Vendors</h5>
                            @if($station->vendors->isEmpty())
                                <div class="alert alert-info">No vendors assigned.</div>
                            @else
                                <div class="list-group">
                                    @foreach($station->vendors as $vendor)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $vendor->name }}</h6>
                                                <small class="text-muted">
                                                    Service: {{ $vendor->pivot->service_type ?? 'N/A' }}<br>
                                                    Contract Date: 
                                                    {{-- FIXED: Added null check --}}
                                                    @if($vendor->pivot->contract_date)
                                                        {{ \Carbon\Carbon::parse($vendor->pivot->contract_date)->format('d/m/Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="badge bg-info">{{ $vendor->pivot->status ?? 'active' }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Schedules Tab -->
                <div class="tab-pane fade" id="schedules" role="tabpanel">
                    @if($station->paymentSchedules->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No payment schedules configured.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Type</th>
                                        <th>Scheduled Amount</th>
                                        <th>Scheduled Date</th>
                                        <th>Frequency</th>
                                        <th>Recurring</th>
                                        <th>Auto Pay</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($station->paymentSchedules as $schedule)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $schedule->payment_type == 'internet' ? 'bg-primary' : 'bg-success' }}">
                                                {{ ucfirst($schedule->payment_type) }}
                                            </span>
                                        </td>
                                        <td>KES {{ number_format($schedule->scheduled_amount, 2) }}</td>
                                        <td>
                                            {{-- FIXED: Added null check for scheduled_date --}}
                                            @if($schedule->scheduled_date)
                                                {{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($schedule->frequency) }}</td>
                                        <td>{{ $schedule->is_recurring ? 'Yes' : 'No' }}</td>
                                        <td>{{ $schedule->auto_pay ? 'Yes' : 'No' }}</td>
                                        <td>
                                            @php
                                                $status = 'upcoming';
                                                if($schedule->scheduled_date) {
                                                    $scheduleDate = \Carbon\Carbon::parse($schedule->scheduled_date);
                                                    $status = $scheduleDate->isPast() ? 'overdue' : ($scheduleDate->isToday() ? 'due today' : 'upcoming');
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $status == 'overdue' ? 'danger' : ($status == 'due today' ? 'warning' : 'success') }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewSchedule({{ $schedule->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewEmployee(employeeId) {
    // Employee view function
    alert('View employee: ' + employeeId);
}

function viewSchedule(scheduleId) {
    // Schedule view function
    alert('View schedule: ' + scheduleId);
}
</script>
@endpush
@endsection