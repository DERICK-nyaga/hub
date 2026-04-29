@extends('layouts.app')

@section('title', 'Station Details - ' . ($station->name ?? 'N/A'))

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">{{ $station->name ?? 'N/A' }}</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-map-marker-alt me-1"></i> 
                {{ $station->address ?? $station->location ?? 'No address provided' }}
                @if($station->code)
                    <span class="mx-2">|</span>
                    <i class="fas fa-hashtag me-1"></i> Code: {{ $station->code }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('payments.airtime.create') }}?station_id={{ $station->station_id }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Airtime
            </a>
            <a href="{{ route('payments.internet.create') }}?station_id={{ $station->station_id }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Add Internet Payment
            </a>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white h-100">
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
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white h-100">
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
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pending Payments</h6>
                            <h2 class="mb-0">KES {{ number_format($stats['pending_payments'] ?? 0, 2) }}</h2>
                            <small>Due Soon: {{ $station->internetPayments->where('due_date', '<', now()->addDays(7))->where('status', 'pending')->count() }}</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white h-100">
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
                <li class="nav-item">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                        <i class="fas fa-info-circle me-1"></i> Station Info
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button" role="tab">
                        <i class="fas fa-users me-1"></i> Employees 
                        <span class="badge bg-secondary ms-1">{{ $station->employees->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="internet-tab" data-bs-toggle="tab" data-bs-target="#internet" type="button" role="tab">
                        <i class="fas fa-globe me-1"></i> Internet Payments
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="airtime-tab" data-bs-toggle="tab" data-bs-target="#airtime" type="button" role="tab">
                        <i class="fas fa-phone me-1"></i> Airtime Payments
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="providers-tab" data-bs-toggle="tab" data-bs-target="#providers" type="button" role="tab">
                        <i class="fas fa-building me-1"></i> Service Providers
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="schedules-tab" data-bs-toggle="tab" data-bs-target="#schedules" type="button" role="tab">
                        <i class="fas fa-calendar-alt me-1"></i> Schedules
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="stationTabsContent">
                
                <!-- Station Information Tab - FIXED STYLING -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="row">
                        <!-- Contact Information Column -->
                        <!-- <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i> Contact Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 40%; background-color: #f8f9fa;">Contact Person</th>
                                                <td>{{ $station->contact_person ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Phone</th>
                                                <td>{{ $station->contact_phone ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Email</th>
                                                <td>{{ $station->contact_email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Status</th>
                                                <td>
                                                    <span class="badge {{ ($station->status ?? 'active') == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ ucfirst($station->status ?? 'Active') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> -->
                        <!-- Contact Information Column -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i> Contact Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 40%; background-color: #f8f9fa;">Contact Person</th>
                                                <td>
                                                    <strong>{{ $station->contact_person_name }}</strong>
                                                    @if($station->manager && $station->manager->job_title)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-briefcase me-1"></i>
                                                            {{ $station->manager->job_title }}
                                                        </small>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Phone</th>
                                                <td>
                                                    <i class="fas fa-phone me-1 text-success"></i>
                                                    {{ $station->contact_phone_number }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Email</th>
                                                <td>
                                                    <i class="fas fa-envelope me-1 text-info"></i>
                                                    <a href="mailto:{{ $station->contact_email_address }}" class="text-decoration-none">
                                                        {{ $station->contact_email_address }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Location</th>
                                                <td>
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                    {{ $station->station_location }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Status</th>
                                                <td>
                                                    <span class="badge {{ ($station->status ?? 'active') == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                        <i class="fas fa-{{ ($station->status ?? 'active') == 'active' ? 'check-circle' : 'times-circle' }} me-1"></i>
                                                        {{ ucfirst($station->status ?? 'Active') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Station Details Column -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-building me-2"></i> Station Details</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 40%; background-color: #f8f9fa;">Station Code</th>
                                                <td><code>{{ $station->code ?? 'N/A' }}</code></td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Opening Date</th>
                                                <td>{{ $station->opening_date ? \Carbon\Carbon::parse($station->opening_date)->format('d/m/Y') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Region</th>
                                                <td>{{ $station->region ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #f8f9fa;">Location</th>
                                                <td>{{ $station->location ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Row - FIXED STYLING -->
                    @if(($station->address ?? null) || ($station->notes ?? null))
                    <div class="row">
                        @if($station->address)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Address</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $station->address }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($station->notes)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i> Notes</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $station->notes }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
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
                            <table class="table table-hover table-striped" id="employeesTable">
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
                                        <td><strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong></td>
                                        <td>{{ $employee->position ?? 'N/A' }}</td>
                                        <td>{{ $employee->department ?? 'N/A' }}</td>
                                        <td>{{ $employee->phone ?? 'N/A' }}</td>
                                        <td>{{ $employee->email ?? 'N/A' }}</td>
                                        <td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') : 'N/A' }}</td>
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
                            <table class="table table-hover table-striped">
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
                                        <td>{{ \Carbon\Carbon::parse($payment->billing_month)->format('M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status == 'paid' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : 'Not paid' }}</td>
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
                            <table class="table table-hover table-striped">
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
                                    <tr>
                                        <td><code>{{ $payment->mobile_number }}</code></td>
                                        <td>{{ $payment->network_provider }}</td>
                                        <td>KES {{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($payment->topup_date)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td><small>{{ $payment->transaction_id }}</small></td>
                                        <td>
                                            <a href="{{ route('payments.airtime.details', $payment->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Service Providers Tab -->
                <div class="tab-pane fade" id="providers" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-globe me-2"></i> Internet Service Providers</h6>
                                </div>
                                <div class="card-body">
                                    @if($station->serviceProviders->isEmpty())
                                        <div class="alert alert-info mb-0">No service providers assigned.</div>
                                    @else
                                        <div class="list-group">
                                            @foreach($station->serviceProviders as $provider)
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">{{ $provider->name }}</h6>
                                                        <small class="text-muted">
                                                            Contract: {{ $provider->pivot->contract_number ?? 'N/A' }}<br>
                                                            Period: {{ $provider->pivot->start_date ? \Carbon\Carbon::parse($provider->pivot->start_date)->format('d/m/Y') : 'N/A' }} 
                                                            - {{ $provider->pivot->end_date ? \Carbon\Carbon::parse($provider->pivot->end_date)->format('d/m/Y') : 'Ongoing' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-success">{{ $provider->pivot->status ?? 'Active' }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-truck me-2"></i> General Vendors</h6>
                                </div>
                                <div class="card-body">
                                    @if($station->vendors->isEmpty())
                                        <div class="alert alert-info mb-0">No vendors assigned.</div>
                                    @else
                                        <div class="list-group">
                                            @foreach($station->vendors as $vendor)
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">{{ $vendor->name }}</h6>
                                                        <small class="text-muted">
                                                            Service: {{ $vendor->pivot->service_type ?? 'N/A' }}<br>
                                                            Contract Date: {{ $vendor->pivot->contract_date ? \Carbon\Carbon::parse($vendor->pivot->contract_date)->format('d/m/Y') : 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-info">{{ $vendor->pivot->status ?? 'Active' }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
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
                            <table class="table table-hover table-striped">
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
                                        <td>{{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m/Y') }}</td>
                                        <td>{{ ucfirst($schedule->frequency) }}</td>
                                        <td>{{ $schedule->is_recurring ? 'Yes' : 'No' }}</td>
                                        <td>{{ $schedule->auto_pay ? 'Yes' : 'No' }}</td>
                                        <td>
                                            @php
                                                $scheduleDate = \Carbon\Carbon::parse($schedule->scheduled_date);
                                                $status = $scheduleDate->isPast() ? 'overdue' : ($scheduleDate->isToday() ? 'due today' : 'upcoming');
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

<!-- Employee Details Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .nav-tabs .nav-link {
        color: #495057;
    }
    .nav-tabs .nav-link.active {
        font-weight: 600;
        color: #0d6efd;
        border-bottom-color: #0d6efd;
    }
    .stat-card {
        transition: transform 0.2s ease;
        cursor: default;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
function viewSchedule(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const modalBody = document.getElementById('scheduleModalBody');
    
    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    modal.show();
    
    fetch(`/payments/schedules/${scheduleId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const schedule = data.data;
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Schedule Information</h6>
                        <table class="table table-sm">
                            <tr><th style="width: 40%;">ID:</th><td>${schedule.id}</td></tr>
                            <tr><th>Payment Type:</th><td>${schedule.payment_type}</td></tr>
                            <tr><th>Amount:</th><td>KES ${parseFloat(schedule.scheduled_amount).toLocaleString()}</td></tr>
                            <tr><th>Scheduled Date:</th><td>${schedule.scheduled_date}</td></tr>
                            <tr><th>Frequency:</th><td>${schedule.frequency}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Settings</h6>
                        <table class="table table-sm">
                            <tr><th style="width: 40%;">Recurring:</th><td>${schedule.is_recurring ? 'Yes' : 'No'}</td></tr>
                            <tr><th>Auto Pay:</th><td>${schedule.auto_pay ? 'Enabled' : 'Disabled'}</td></tr>
                            <tr><th>Created:</th><td>${new Date(schedule.created_at).toLocaleString()}</td></tr>
                            <tr><th>Description:</th><td>${schedule.description || 'No description'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">${data.message || 'Error loading schedule details'}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading schedule details</div>';
    });
}

function viewEmployee(employeeId) {
    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    const modalBody = document.getElementById('employeeModalBody');
    
    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    modal.show();
    
    fetch(`/payments/employee/${employeeId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const emp = data.data;
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                        <table class="table table-sm">
                            <tr><th style="width: 40%;">Full Name:</th><td>${emp.first_name} ${emp.last_name}</td></tr>
                            <tr><th>ID Number:</th><td>${emp.id_number || 'N/A'}</td></tr>
                            <tr><th>Position:</th><td>${emp.position || 'N/A'}</td></tr>
                            <tr><th>Department:</th><td>${emp.department || 'N/A'}</td></tr>
                            <tr><th>Hire Date:</th><td>${emp.hire_date || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        <table class="table table-sm">
                            <tr><th style="width: 40%;">Phone:</th><td>${emp.phone || 'N/A'}</td></tr>
                            <tr><th>Email:</th><td>${emp.email || 'N/A'}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-${emp.status == 'active' ? 'success' : 'secondary'}">${emp.status}</span></td></tr>
                        </table>
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">${data.message || 'Error loading employee details'}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading employee details</div>';
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush
@endsection
