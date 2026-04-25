@extends('layouts.app')

@section('title', 'Station Details - ' . $station->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0" id="airtime-payment">
                        <i class="fas fa-gas-pump"></i>
                        Station Details: {{ $station->name }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Station ID:</th>
                                    <td>
                                        <span class="badge bg-primary">#{{ $station->station_id }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Station Name:</th>
                                    <td>{{ $station->name }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        {{ $station->location }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Monthly Loss:</th>
                                    <td>
                                        <span class="text-danger fw-bold">
                                            Ksh {{ number_format($station->monthly_loss, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Deductions:</th>
                                    <td>
                                        <span class="text-info fw-bold">
                                            Ksh {{ number_format($station->deductions, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created Date:</th>
                                    <td>{{ $station->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $station->employees_count ?? 0 }}</h4>
                                    <small>Total Employees</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                            @if($station->employees_count > 0)
                            <div class="mt-2">
                                <small>
                                    Active: {{ $station->employees()->where('status', 'active')->count() }} |
                                    Inactive: {{ $station->employees()->where('status', 'inactive')->count() }}
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $station->orders_count ?? 0 }}</h4>
                                    <small>Total Orders</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                            </div>
                            @if($station->orders_count > 0)
                            <div class="mt-2">
                                <small>
                                    @php
                                        $pending = $station->orders()->where('status', 'pending')->count();
                                        $completed = $station->orders()->where('status', 'completed')->count();
                                        $cancelled = $station->orders()->where('status', 'cancelled')->count();
                                    @endphp
                                    Pending: {{ $pending }} |
                                    Completed: {{ $completed }} |
                                    Cancelled: {{ $cancelled }}
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">Ksh {{ number_format($station->payments_sum_amount ?? 0, 2) }}</h4>
                                    <small>Total Payments Amount</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-credit-card fa-2x"></i>
                                </div>
                            </div>
                            @if($station->payments_count > 0)
                            <div class="mt-2">
                                <small>
                                    Count: {{ $station->payments_count }} |
                                    Avg: Ksh {{ number_format(($station->payments_sum_amount ?? 0) / max($station->payments_count, 1), 2) }}
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Action Buttons -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('stations.edit', $station->station_id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Station
                        </a>

                        <form action="{{ route('stations.destroy', $station->station_id) }}" method="POST" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this station? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete Station
                            </button>
                        </form>

                        <a href="{{ route('stations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Stations
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Station Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Last Updated:</small>
                        <div>{{ $station->updated_at->format('M d, Y \a\t h:i A') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Station Status:</small>
                        <div>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="stationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="employees-tab" data-bs-toggle="tab"
                                    data-bs-target="#employees" type="button" role="tab">
                                <i class="fas fa-users"></i> Employees
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="orders-tab" data-bs-toggle="tab"
                                    data-bs-target="#orders" type="button" role="tab">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payments-tab" data-bs-toggle="tab"
                                    data-bs-target="#payments" type="button" role="tab">
                                <i class="fas fa-credit-card"></i> Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="deductions-tab" data-bs-toggle="tab"
                                    data-bs-target="#deductions" type="button" role="tab">
                                <i class="fas fa-money-bill-wave"></i> Deductions
                                @if($station->employees->sum('deductions_sum_amount') > 0)
                                    <span class="badge bg-danger ms-1">
                                        Ksh {{ number_format($station->employees->sum('deductions_sum_amount'), 2) }}
                                    </span>
                                @endif
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="stationTabsContent">
                        <!-- Employees Tab -->
                        <div class="tab-pane fade show active" id="employees" role="tabpanel">
                            @if($station->employees->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Salary</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($station->employees as $employee)
                                            <tr>
                                                <td>#{{ $employee->employee_id }}</td>
                                                <td>{{ $employee->full_name }}</td>
                                                <td>{{ $employee->position ?? 'N/A' }}</td>
                                                <td>Ksh {{ number_format($employee->salary ?? 0, 2) }}</td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No employees assigned to this station.</p>
                                    <a href="#" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Employee
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel">
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Order data will be displayed here.</p>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="payments" role="tabpanel">
                            <div class="text-center py-4">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Payment data will be displayed here.</p>
                            </div>
                        </div>

                        <!-- Deductions Tab -->
<!-- Deductions Tab -->
<div class="tab-pane fade" id="deductions" role="tabpanel">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="airtime-payment">
                    <i class="fas fa-money-bill-wave text-danger me-2"></i>
                    Employee Deductions
                </h5>
                <div>
                    <span class="badge bg-danger">
                        Total Deductions: Ksh {{ number_format($station->employees->sum('deductions_sum_amount'), 2) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($station->employees->where('deductions_count', '>', 0)->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Gross Salary</th>
                                <th>Total Deductions</th>
                                <th>Net Salary</th>
                                <th>No. of Deductions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($station->employees as $employee)
                                @if($employee->deductions_count > 0)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title bg-primary rounded-circle">
                                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong>{{ $employee->full_name }}</strong><br>
                                                    <small class="text-muted">#{{ $employee->employee_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->position ?? 'N/A' }}</td>
                                        <td>
                                            <strong class="text-success">
                                                Ksh {{ number_format($employee->salary ?? 0, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                Ksh {{ number_format($employee->deductions_sum_amount ?? 0, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                @php
                                                    $netSalary = ($employee->salary ?? 0) - ($employee->deductions_sum_amount ?? 0);
                                                @endphp
                                                Ksh {{ number_format($netSalary, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $employee->deductions_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('employees.show', $employee) }}"
                                                   class="btn btn-info"
                                                   title="View Employee">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#"
                                                   class="btn btn-warning"
                                                   title="View Deductions"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#deductionsModal{{ $employee->id }}">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Deductions Details Modal -->
                                    <div class="modal fade" id="deductionsModal{{ $employee->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Deductions for {{ $employee->full_name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @php
                                                        $employeeDeductions = $employee->deductions()->latest()->get();
                                                    @endphp

                                                    @if($employeeDeductions->count() > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date</th>
                                                                        <th>Description</th>
                                                                        <th>Type</th>
                                                                        <th>Amount (KES)</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($employeeDeductions as $deduction)
                                                                        <tr>
                                                                            <td>{{ $deduction->date->format('M d, Y') }}</td>
                                                                            <td>{{ $deduction->description }}</td>
                                                                            <td>
                                                                                <span class="badge bg-info">
                                                                                    {{ ucfirst($deduction->type) }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-danger">
                                                                                Ksh {{ number_format($deduction->amount, 2) }}
                                                                            </td>
                                                                            <td>
                                                                                <span class="badge bg-{{ $deduction->status == 'active' ? 'success' : 'warning' }}">
                                                                                    {{ ucfirst($deduction->status) }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr class="table-active">
                                                                        <th colspan="3" class="text-end">Total:</th>
                                                                        <th class="text-danger">
                                                                            Ksh {{ number_format($employee->deductions_sum_amount, 2) }}
                                                                        </th>
                                                                        <th></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-4">
                                                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                                            <p class="text-muted">No deduction records found for this employee.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="3" class="text-end">Totals:</th>
                                <th class="text-danger">
                                    Ksh {{ number_format($station->employees->sum('deductions_sum_amount'), 2) }}
                                </th>
                                <th class="text-primary">
                                    @php
                                        $totalGross = $station->employees->sum('salary');
                                        $totalDeductions = $station->employees->sum('deductions_sum_amount');
                                        $totalNet = $totalGross - $totalDeductions;
                                    @endphp
                                    Ksh {{ number_format($totalNet, 2) }}
                                </th>
                                <th>
                                    {{ $station->employees->sum('deductions_count') }}
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-4x text-muted mb-4"></i>
                    <h5 class="text-muted">No Deductions Found</h5>
                    <p class="text-muted mb-4">There are no deduction records for employees at this station.</p>

                    <!-- Show all employees even if they have no deductions -->
                    @if($station->employees->count() > 0)
                        <div class="mt-4">
                            <h6>Employees at this Station:</h6>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Position</th>
                                            <th>Salary</th>
                                            <th>Deductions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($station->employees as $employee)
                                            <tr>
                                                <td>{{ $employee->full_name }}</td>
                                                <td>{{ $employee->position ?? 'N/A' }}</td>
                                                <td>Ksh {{ number_format($employee->salary ?? 0, 2) }}</td>
                                                <td class="text-success">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                    Ksh 0.00
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- @section('scripts') --}}
{{-- <script>
    // Initialize Bootstrap tabs
    var triggerTabList = [].slice.call(document.querySelectorAll('#stationTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    });
</script> --}}
{{-- @endsection --}}
