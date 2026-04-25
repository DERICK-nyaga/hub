@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Loss Records</h1>

    <div class="mb-3">
        <a href="{{ route('losses.create') }}" class="btn btn-primary">
            Record New Loss
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Station</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($losses as $loss)
                    <tr>
                        <td>{{ $loss->date_occurred->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('stations.losses', $loss->station) }}">
                                {{ $loss->station->name }}
                            </a>
                        </td>
                        <td>{{ ucfirst($loss->type) }}</td>
                        <td>{{ number_format($loss->amount, 2) }}</td>
                        <td>{{ Str::limit($loss->description, 50) }}</td>
                        <td>{{ $loss->employee ? $loss->employee->full_name : 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $loss->resolved ? 'bg-success' : 'bg-warning' }}">
                                {{ $loss->resolved ? 'Resolved' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $losses->links() }}
        </div>
    </div>
</div>
@endsection
