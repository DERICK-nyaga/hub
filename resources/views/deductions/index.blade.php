@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Deduction Transactions</h1>
            <small class="text-muted">Manage employee deductions and salary advances</small>
        </div>
        <a href="{{ route('deductions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> New Transaction
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('deductions.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Employee</label>
                        <select class="form-control form-control-sm" name="employee_name">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->first_name }}" {{ request('employee_name') == $emp->first_name ? 'selected' : '' }}>
                                    {{ $emp->first_name }} ({{ $emp->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Type</label>
                        <select class="form-control form-control-sm" name="type">
                            <option value="">All Types</option>
                            <option value="initial" {{ request('type') == 'initial' ? 'selected' : '' }}>Initial</option>
                            <option value="additional" {{ request('type') == 'additional' ? 'selected' : '' }}>Additional</option>
                            <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                            <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Reason</label>
                        <select class="form-control form-control-sm" name="reason">
                            <option value="">All Reasons</option>
                            <option value="salary advance" {{ request('reason') == 'salary advance' ? 'selected' : '' }}>Salary Advance</option>
                            <option value="loan" {{ request('reason') == 'loan' ? 'selected' : '' }}>Loan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">From Date</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">To Date</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100 mb-1">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('deductions.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $transactions->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Deductions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalDeductions, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-left-danger shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Salary Advances</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($salaryAdvances, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPayments, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-8 col-sm-12">
            <div class="card border-left-{{ $outstandingBalance > 0 ? 'dark' : 'info' }} shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $outstandingBalance > 0 ? 'dark' : 'info' }} text-uppercase mb-1">
                                Outstanding Balance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format(abs($outstandingBalance), 2) }}</div>
                            <div class="text-xs mt-1">
                                @if($outstandingBalance > 0)
                                    <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Amount Owed</span>
                                @elseif($outstandingBalance < 0)
                                    <span class="text-info"><i class="fas fa-info-circle"></i> Overpayment</span>
                                @else
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Settled</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-scale-balanced fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats & Breakdown -->
    <div class="row g-3 mb-4">
        <!-- Stats -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2 mb-2">
                                <div class="text-sm text-muted">Salary Advances</div>
                                <div class="h5 font-weight-bold text-danger">{{ $salaryAdvanceCount }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 mb-2">
                                <div class="text-sm text-muted">Deductions</div>
                                <div class="h5 font-weight-bold text-warning">{{ $regularDeductionCount }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 mb-2">
                                <div class="text-sm text-muted">Payments</div>
                                <div class="h5 font-weight-bold text-success">{{ $paymentCount }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Avg. Salary Advance: <strong>{{ number_format($salaryAdvanceCount > 0 ? $salaryAdvances / $salaryAdvanceCount : 0, 2) }}</strong></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">Initial Deductions:<small class="text-muted">(ID)</small></td>
                                    <td class="text-right">{{ number_format($initialDeductions, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Additional Deductions:<small class="text-muted">(AD)</small></td>
                                    <td class="text-right">{{ number_format($additionalDeductions, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Refunds:<small class="text-muted">(R)</small></td>
                                    <td class="text-right">{{ number_format($refund, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Deductions:<small class="text-muted">(TD)=(ID+AD-R)</small></strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalDeductions, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr class="text-danger">
                                    <td><strong>Salary Advances:<small class="text-muted">(SA)</small></strong></td>
                                    <td class="text-right"><strong>+{{ number_format($salaryAdvances, 2) }}</strong></td>
                                </tr>
                                <tr class="border-top text-success">
                                    <td><strong>Payments Made:<small class="text-muted">(PM)</small></strong></td>
                                    <td class="text-right"><strong>-{{ number_format($totalPayments, 2) }}</strong></td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Outstanding:(OS)=(TD+SA-PM)</strong></td>
                                    <td class="text-right font-weight-bold {{ $outstandingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($outstandingBalance, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Table Transactions  -->
    <div class="card shadow-sm">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transaction Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th width="80">ID</th>
                            <th width="100">Date</th>
                            <th>Employee</th>
                            <th width="100">Type</th>
                            <th width="120">Category</th>
                            <th width="120">Amount</th>
                            <th>Reason</th>
                            <th width="100">Order #</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td><span class="badge bg-secondary">#{{ $transaction->id }}</span></td>
                            <td><small>{{ $transaction->transaction_date->format('M d, Y') }}</small></td>
                            <td>
                                @php
                                    $employeeName = $transaction->employee ?
                                        $transaction->employee->first_name . ' ' . $transaction->employee->last_name :
                                        $transaction->employee_name;

                                    $employeeId = $transaction->employee ?
                                        $transaction->employee->employee_id :
                                        $transaction->employee_id;

                                    $employeePhone = $transaction->employee ? $transaction->employee->phone : null;
                                    $employeeMobile = $transaction->employee ? $transaction->employee->mobile : null;
                                @endphp

                                <div class="fw-bold">{{ $employeeName }}</div>
                                <small class="text-muted">ID# {{ $employeeId }}</small>

                                <div class="mt-1">
                                    @if($employeePhone)
                                        <div>
                                            <a href="tel:{{ $employeePhone }}" class="text-decoration-none text-primary" title="Call {{ $employeePhone }}">
                                                <i class="fas fa-phone me-1"></i>
                                                <small>{{ $employeePhone }}</small>
                                            </a>
                                        </div>
                                    @endif

                                    @if($employeeMobile && $employeeMobile != $employeePhone)
                                        <div class="mt-1">
                                            <a href="tel:{{ $employeeMobile }}" class="text-decoration-none text-success" title="Call mobile {{ $employeeMobile }}">
                                                <i class="fas fa-mobile-alt me-1"></i>
                                                <small>{{ $employeeMobile }}</small>
                                            </a>
                                        </div>
                                    @endif

                                    @if(!$employeePhone && !$employeeMobile)
                                        <small class="text-muted">No contact</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-sm
                                    @if($transaction->type == 'payment') bg-success
                                    @elseif($transaction->type == 'initial') bg-primary
                                    @elseif($transaction->type == 'refund') bg-warning
                                    @elseif($transaction->type == 'additional') bg-info
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->reason == 'salary advance')
                                    <span class="badge bg-danger badge-sm">
                                        <i class="fas fa-money-bill-wave"></i> Advance
                                    </span>
                                @elseif($transaction->type == 'payment')
                                    <span class="badge bg-success badge-sm">
                                        <i class="fas fa-arrow-down"></i> Payment
                                    </span>
                                @else
                                    <span class="badge bg-warning badge-sm">
                                        <i class="fas fa-arrow-up"></i> Deduction
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold
                                    @if($transaction->type == 'payment') text-success
                                    @elseif($transaction->reason == 'salary advance') text-danger
                                    @else text-warning
                                    @endif">
                                    @if($transaction->type == 'payment')
                                        -{{ number_format($transaction->amount, 2) }}
                                    @else
                                        {{ number_format($transaction->amount, 2) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $transaction->reason }}">
                                    {{ $transaction->reason }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $transaction->order_number ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('deductions.show',$transaction->id) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- <a href="{{ route('deductions.edit', $transaction->id) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a> --}}
                                    <form action="{{ route('deductions.destroy', $transaction->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                                onclick="return confirm('Are you sure?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No transactions found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

{{-- pagination --}}

            @if($transactions->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                </div>
                {{ $transactions->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
