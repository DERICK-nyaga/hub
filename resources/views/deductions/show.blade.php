@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Deduction Transaction Details</h1>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Employee Information</h5>
                </div>
                <div class="col-md-6">
                    <h5>Transaction Details</h5>
                    <p><strong>Transaction Date:</strong> {{ \Carbon\Carbon::parse($deduction->transaction_date)->format('M d, Y') }}</p>
                    <p><strong>Type:</strong> {{ $types[$deduction->type] ?? $deduction->type }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Amount:</strong> ${{ number_format($deduction->amount, 2) }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Reason:</strong> {{ $deduction->reason }}</p>
                </div>
            </div>

            @if($deduction->notes)
            <div class="row mb-3">
                <div class="col-12">
                    <p><strong>Notes:</strong></p>
                    <div class="border p-2">
                        {{ $deduction->notes }}
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <a href="{{ route('deductions.index') }}" class="btn btn-secondary">Back to List</a>
                    @can('edit-deduction')
                    <a href="{{ route('deductions.edit', $deduction->id) }}" class="btn btn-primary">Edit</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
