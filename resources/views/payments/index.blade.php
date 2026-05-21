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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->title }}</td>
                            <td>{{ $payment->station->name ?? 'N/A' }}</td>
                            <td>{{ $payment->vendor?->name ?? 'N/A' }}</td>
                            <td>KES {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @php
                                    $dueDate = $payment->due_date instanceof \Carbon\Carbon ? $payment->due_date : \Carbon\Carbon::parse($payment->due_date);
                                @endphp
                                {{ $dueDate->format('M d, Y') }}
                                @if($payment->status !== 'paid' && $dueDate->isPast())
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
                                <div class="btn-group" role="group">
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
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No payments found.</p>
                                <a href="{{ route('payments.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-1"></i> Create First Payment
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection