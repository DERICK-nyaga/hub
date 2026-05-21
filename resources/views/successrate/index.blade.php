@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Weekly Station Performance Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Current Year</h5>
                            <h2>{{ $currentYear }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Current Week</h5>
                            <h2>Week {{ $currentWeek }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Stations</h5>
                            <h2>{{ $totalStations }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Weeks Processed</h5>
                            <h2>{{ $currentWeek }}/52</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upload Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Upload Weekly Performance Report</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    <form action="{{ route('successrate.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Year</label>
                                    <select name="year" class="form-control" required>
                                        @for($y = 2024; $y <= $currentYear + 1; $y++)
                                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Upload File (Excel or CSV)</label>
                                    <input type="file" class="form-control @error('document') is-invalid @enderror" 
                                           name="document" accept=".xlsx,.xls,.csv,.txt" required>
                                    @error('document')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">Upload & Process</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Upload weekly file comparing current week vs previous week performance.
                            Format should include: Station Name, Current Week Rate, Previous Week Rate, Current Packages, Previous Packages
                        </small>
                    </form>
                </div>
            </div>
            
            <!-- Latest Data Preview -->
            @if(!empty($latestData))
            <div class="card">
                <div class="card-header">
                    <h4>Latest Week Performance (Week {{ $currentWeek }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Rank</th><th>Station</th><th>Success Rate</th><th>Packages</th><th>Delta</th></tr>
                            </thead>
                            <tbody>
                                @foreach($latestData as $index => $record)
                                <tr class="{{ $record->delta < 0 ? 'table-danger' : ($record->delta > 0 ? 'table-success' : '') }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $record->station_name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $record->success_rate >= 85 ? 'success' : ($record->success_rate >= 70 ? 'warning' : 'danger') }}">
                                            {{ $record->success_rate }}%
                                        </span>
                                    </td>
                                    <td>{{ number_format($record->packages_closed) }}</td>
                                    <td>
                                        @if($record->delta)
                                            <span class="{{ $record->delta > 0 ? 'text-success' : ($record->delta < 0 ? 'text-danger' : 'text-muted') }}">
                                                {{ $record->delta > 0 ? '+' : '' }}{{ $record->delta }}%
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="text-center mt-4">
                <a href="{{ route('successrate.analysis') }}" class="btn btn-lg btn-info">
                    View Full Analysis & Recommendations →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection