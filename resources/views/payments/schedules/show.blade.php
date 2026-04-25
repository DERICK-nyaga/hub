@extends('layouts.app')

@section('title', 'Schedule Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Payment Schedule Details</h1>
        <div>
            <a href="{{ route('payments.schedules.edit', $schedule->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('payments.schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Schedule ID</th>
                            <td>{{ $schedule->id }}</td>
                        </tr>
                        <tr>
                            <th>Station</th>
                            <td>{{ $schedule->station->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Payment Type</th>
                            <td>{{ ucfirst($schedule->payment_type) }}</td>
                        </tr>
                        <tr>
                            <th>Scheduled Amount</th>
                            <td>KES {{ number_format($schedule->scheduled_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Scheduled Date</th>
                            <td>{{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Frequency</th>
                            <td>{{ ucfirst($schedule->frequency) }}</td>
                        </tr>
                        <tr>
                            <th>Is Recurring</th>
                            <td>{{ $schedule->is_recurring ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <th>Auto Pay</th>
                            <td>{{ $schedule->auto_pay ? 'Enabled' : 'Disabled' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $schedule->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $schedule->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($schedule->description)
            <div class="row mt-3">
                <div class="col-12">
                    <h6>Description</h6>
                    <p>{{ $schedule->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection