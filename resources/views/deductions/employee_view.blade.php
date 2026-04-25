@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-user-tag"></i>
                Deduction History: {{ $employee->name }}
                <small class="text-muted">ID: {{ $employee->employee_id }}</small>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('deductions.create', ['employee_id' => $employee->id]) }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Deduction
            </a>
            <a href="{{ route('deductions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to All Transactions
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="employee-photo">
                        @if($employee->photo)
                            <img src="{{ asset('storage/'.$employee->photo) }}"
                                 alt="{{ $employee->name }}" class="img-thumbnail">
                        @else
                            <div class="no-photo">
                                <i class="fas fa-user-circle fa-5x"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Department</h5>
                            <p>{{ $employee->department ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h5>Position</h5>
                            <p>{{ $employee->position ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h5>Hire Date</h5>
                            <p>{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Deductions</h6>
                                    <h4 class="text-danger">{{ number_format($totalDebits, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Payments</h6>
                                    <h4 class="text-success">{{ number_format(abs($totalCredits), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card {{ $netBalance >= 0 ? 'bg-warning' : 'bg-success' }}">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Current Balance</h6>
                                    <h4>{{ number_format(abs($netBalance), 2) }}</h4>
                                    <small>({{ $netBalance >= 0 ? 'Owed' : 'Credit' }})</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaction History</h3>
            <div class="card-tools">
                <form method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Order #</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge
                                    @if($transaction->type == 'payment') badge-success
                                    @elseif($transaction->type == 'initial') badge-primary
                                    @elseif($transaction->type == 'adjustment') badge-warning
                                    @else badge-info
                                    @endif">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="{{ $transaction->amount < 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>{{ $transaction->reason }}</td>
                            <td>{{ $transaction->order_number ?? 'N/A' }}</td>
                            <td>
                                @if($transaction->notes)
                                <a href="#" data-toggle="tooltip" title="{{ $transaction->notes }}">
                                    {{ Str::limit($transaction->notes, 20) }}
                                </a>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('deductions.show', [$employee, $transaction]) }}"
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('deductions.edit', [$employee, $transaction]) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('deductions.destroy', [$employee, $transaction]) }}"
                                          method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                title="Delete" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip();

    $('#print-btn').click(function() {
        window.print();
    });
});
</script>
@endsection

@section('styles')
<style>
    .employee-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto;
    }
    .employee-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .no-photo {
        text-align: center;
        color: #6c757d;
    }
    .badge {
        font-size: 0.85em;
        font-weight: 500;
    }
    .table td {
        vertical-align: middle;
    }
    .card-title {
        margin-bottom: 0;
    }
    @media print {
        .no-print {
            display: none;
        }
    }
</style>
@endsection
