@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Payment Details</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.edit', $payment) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Title:</strong> {{ $payment->title }}</p>
                            <p><strong>Station:</strong> {{ $payment->station->name }}</p>
                            <p><strong>Vendor:</strong> {{ $payment->vendor?->name ?? 'N/A' }}</p>
                            <p><strong>Type:</strong> {{ ucfirst($payment->type) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Amount:</strong> {{ number_format($payment->amount, 2) }}</p>
                            <p><strong>Due Date:</strong> {{ $payment->due_date->format('M d, Y') }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $payment->statusBadgeColor() }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </p>
                            @if($payment->is_recurring)
                                <p><strong>Recurrence:</strong>
                                    {{ ucfirst($payment->recurrence) }}
                                    @if($payment->recurrence_ends_at)
                                        (ends {{ $payment->recurrence_ends_at->format('M d, Y') }})
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p><strong>Description:</strong></p>
                            <p>{{ $payment->description ?? 'No description provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if($payment->status == 'pending')
                        <form action="{{ route('payments.approve', $payment) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Approve Payment
                            </button>
                        </form>
                    @endif

                    @if($payment->status == 'approved')
                        <form action="{{ route('payments.markAsPaid', $payment) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check-circle"></i> Mark as Paid
                            </button>
                        </form>
                    @endif

                    @if($payment->attachment_path)
                        <a href="{{ Storage::url($payment->attachment_path) }}" target="_blank" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-file-download"></i> Download Attachment
                        </a>
                    @endif

                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title">Audit Log</h6>
                            <p class="small mb-1"><strong>Created:</strong>
                                {{ $payment->created_at->format('M d, Y H:i') }} by {{ $payment->creator->name }}
                            </p>
                            @if($payment->approved_at)
                                <p class="small mb-1"><strong>Approved:</strong>
                                    {{ $payment->approved_at->format('M d, Y H:i') }} by {{ $payment->approver->name }}
                                </p>
                            @endif
                            @if($payment->paid_at)
                                <p class="small mb-1"><strong>Paid:</strong>
                                    {{ $payment->paid_at->format('M d, Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
