@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-receipt"></i> Payment Receipt
                    </h4>
                </div>
                <div class="card-body" id="receipt-content">
                    <div class="text-center mb-4">
                        <h2>SALARY PAYMENT RECEIPT</h2>
                        <p class="text-muted">Transaction Reference: <strong>{{ $payment->transaction_reference }}</strong></p>
                        <p>Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y H:i:s') }}</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Employee Information</h5>
                            <p>
                                <strong>Name:</strong> {{ $employee->name }}<br>
                                <strong>Position:</strong> {{ $employee->position ?? 'N/A' }}<br>
                                <strong>Station:</strong> {{ $employee->station ?? 'Main Office' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Information</h5>
                            <p>
                                <strong>Method:</strong> {{ strtoupper($payment->payment_method) }}<br>
                                <strong>Type:</strong> {{ ucfirst($payment->type) }}<br>
                                <strong>Status:</strong> <span class="badge bg-success">PAID</span>
                            </p>
                        </div>
                    </div>
                    
                    <table class="table table-bordered mt-3">
                        <tr>
                            <th width="60%">Gross Salary:</th>
                            <td class="text-end">KES {{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        <tr class="text-danger">
                            <th>Total Deductions:</th>
                            <td class="text-end">- KES {{ number_format($payment->deductions_total, 2) }}</td>
                        </tr>
                        @if($deduction && $deduction->number_of_installments > 1)
                        <tr class="bg-light">
                            <td colspan="2" class="text-center">
                                <small>Payment Plan: {{ $deduction->number_of_installments }} monthly installments of KES {{ number_format($deduction->installment_amount, 2) }}</small>
                            </td>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <th class="h5">NET AMOUNT PAID:</th>
                            <td class="text-end">
                                <strong class="h3 text-success">KES {{ number_format($payment->net_amount, 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                    
                    @if($payment->notes)
                    <div class="alert alert-info">
                        <strong>Notes:</strong> {{ $payment->notes }}
                    </div>
                    @endif
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">This is a computer-generated receipt. No signature required.</p>
                        <p>Thank you for your payment!</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('salary.payments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.print();
</script>
@endpush
@endsection