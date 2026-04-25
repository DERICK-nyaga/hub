@extends('layouts.app')

@section('title', 'Upcoming Payments')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Upcoming Payments & Bills</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Payment
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Station</th>
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->title }}</td>
                            <td>{{ $payment->station->name }}</td>
                            <td>{{ $payment->vendor?->name ?? 'N/A' }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                {{ $payment->due_date->format('M d, Y') }}
                                @if($payment->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $payment->statusBadgeColor() }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>{{ ucfirst($payment->type) }}</td>
                            <td>
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
