@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Payment Summary - Payable Amount</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Employee Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $employee->name }}</td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td>{{ $employee->position }}</td>
                                </tr>
                                <tr>
                                    <th>Station:</th>
                                    <td>{{ $employee->station }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Reference:</th>
                                    <td>{{ $payment->transaction_reference }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ strtoupper($payment->payment_method) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Salary Breakdown</h5>
                            <table class="table table-striped">
                                <tr>
                                    <td><strong>Gross Salary:</strong></td>
                                    <td class="text-end">KES {{ number_format($gross_amount, 2) }}</td>
                                </tr>
                                <tr class="text-danger">
                                    <td><strong>Deduction Amount:</strong></td>
                                    <td class="text-end">- KES {{ number_format($deduction_amount, 2) }}</td>
                                </tr>
                                @if($installment_info)
                                <tr>
                                    <td colspan="2" class="bg-info text-white">
                                        <small>Installment Plan: {{ $installment_info['total_installments'] }} months @ KES {{ number_format($installment_info['installment_amount'], 2) }}/month</small>
                                        <br>
                                        <small>Completed: {{ $installment_info['completed_installments'] }} of {{ $installment_info['total_installments'] }}</small>
                                    </td>
                                </tr>
                                @endif
                                <tr class="table-success">
                                    <td><strong>NET PAYABLE AMOUNT:</strong></td>
                                    <td class="text-end"><strong class="h4">KES {{ number_format($net_amount, 2) }}</strong></td>
                                </tr>
                            </table>
                            
                            @if($deduction && $deduction->message)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> {{ $deduction->message }}
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <form action="{{ route('salary.payments.confirm', $payment->id) }}" method="POST" id="confirmForm">
                                @csrf
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="confirmation" name="confirmation" required value="1">
                                    <label class="form-check-label" for="confirmation">
                                        I confirm that the above net amount of <strong>KES {{ number_format($net_amount, 2) }}</strong> is correct and approve this payment.
                                    </label>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('salary.payments.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check-circle"></i> Proceed with Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('confirmForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to proceed with this payment? This action cannot be undone.')) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection