@extends('layouts.app')

@section('title', 'Overdue Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Overdue Payments</h1>
            <p class="text-muted">Payments that are past due or expired.</p>
        </div>
        <a href="{{ route('payments.airtime.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Airtime List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Overdue Internet Payments</h5>
                </div>
                <div class="card-body">
                    @if($overdueInternet->isEmpty())
                        <div class="alert alert-info mb-0">
                            No overdue internet payments found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Station</th>
                                        <th>Provider</th>
                                        <th>Account</th>
                                        <th>Amount Due</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueInternet as $payment)
                                        <tr>
                                            <td>{{ $payment->station->name }}</td>
                                            <td>{{ $payment->provider->name }}</td>
                                            <td><code>{{ $payment->account_number }}</code></td>
                                            <td>KES {{ number_format($payment->total_due, 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\today()->diffInDays(\Carbon\parse($payment->due_date)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Overdue Airtime Payments</h5>
                </div>
                <div class="card-body">
                    @if($overdueAirtime->isEmpty())
                        <div class="alert alert-info mb-0">
                            No overdue airtime payments found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Station</th>
                                        <th>Network</th>
                                        <th>Mobile</th>
                                        <th>Amount</th>
                                        <th>Expiry Date</th>
                                        <th>Days Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueAirtime as $payment)
                                        <tr>
                                            <td>{{ $payment->station->name }}</td>
                                            <td>{{ $payment->network_provider }}</td>
                                            <td><code>{{ $payment->mobile_number }}</code></td>
                                            <td>KES {{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->expected_expiry)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\today()->diffInDays(\Carbon\parse($payment->expected_expiry)) }}</td>
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
@endsection
