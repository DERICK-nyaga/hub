@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="new-station">Manage Stations</h5>
            <a href="{{ route('stations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Station
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Station Name</th>
                            <th>Location</th>
                            <th>Mobile_Number</th>
                            <th>Employees</th>
                            <th>Monthly P/L</th>
                            <th>Deductions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stations as $station)
                        <tr>
                            <td>PP-{{ str_pad($station->station_id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $station->name }}</td>
                            <td>{{ $station->location }}</td>
                            <td>{{ $station->mobile_number }}</td>
                            <td>{{ $station->employees_count }}</td>
                            <td class="{{ $station->monthly_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                KES {{ number_format($station->monthly_loss, 2) }}
                            </td>
                            <td>KES {{ number_format($station->deductions, 2) }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $station->monthly_loss >= 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $station->monthly_loss >= 0 ? 'Profitable' : 'Loss' }}
                                </span>
                            </td>

                            <td class="td">
                                <a href="{{ route('stations.show', $station->station_id) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('stations.edit', $station->station_id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('stations.destroy', $station->station_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $stations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
