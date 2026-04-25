@extends('layouts.app')

@section('title', 'Approval Reports & Analytics')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Approval Reports & Analytics</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('approvals.export_report', request()->all()) }}"
               class="btn btn-success">
                <i class="bi bi-download"></i> Export Report
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Change Type</label>
                    <select name="change_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($changeTypes as $type)
                            <option value="{{ $type }}" {{ request('change_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <a href="{{ route('approvals.reports') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="display-4">{{ $report['total_changes'] }}</h2>
                    <h6>Total Changes</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2 class="display-4">{{ $report['approved'] }}</h2>
                    <h6>Approved</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h2 class="display-4">{{ $report['pending'] }}</h2>
                    <h6>Pending</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2 class="display-4">{{ $report['avg_approval_time'] }}h</h2>
                    <h6>Avg. Approval Time</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Approvals by Department</h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Approvals by Change Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Analysis -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Approval Timeline Analysis</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Avg. Duration</th>
                            <th>Min Duration</th>
                            <th>Max Duration</th>
                            <th>Bottlenecks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $levels = collect($report['approval_timelines'])->groupBy('level');
                        @endphp
                        @foreach($levels as $level => $timelines)
                        @php
                            $avg = $timelines->avg('duration_hours');
                            $min = $timelines->min('duration_hours');
                            $max = $timelines->max('duration_hours');
                            $bottleneck = $timelines->sortByDesc('duration_hours')->first();
                        @endphp
                        <tr>
                            <td><strong>Level {{ $level }}</strong></td>
                            <td>{{ round($avg, 1) }} hours</td>
                            <td>{{ round($min, 1) }} hours</td>
                            <td>{{ round($max, 1) }} hours</td>
                            <td>
                                @if($max > 48)
                                <span class="badge bg-danger">High: {{ $bottleneck['approver'] }} ({{ round($max, 1) }}h)</span>
                                @elseif($max > 24)
                                <span class="badge bg-warning">Medium</span>
                                @else
                                <span class="badge bg-success">Normal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detailed Approval Timeline</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Change ID</th>
                            <th>Employee</th>
                            <th>Level</th>
                            <th>Approver</th>
                            <th>Duration</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['approval_timelines'] as $timeline)
                        <tr>
                            <td>#{{ $timeline['change_id'] }}</td>
                            <td>{{ $timeline['employee_id'] }}</td>
                            <td>Level {{ $timeline['level'] }}</td>
                            <td>{{ $timeline['approver'] }}</td>
                            <td>
                                <span class="badge bg-{{ $timeline['duration_hours'] > 48 ? 'danger' : ($timeline['duration_hours'] > 24 ? 'warning' : 'success') }}">
                                    {{ round($timeline['duration_hours'], 1) }}h
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($timeline['start_time'])->format('d/m/Y H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($timeline['end_time'])->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Department Chart
new Chart(document.getElementById('departmentChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($report['by_department'])) !!},
        datasets: [{
            label: 'Approvals',
            data: {!! json_encode(array_values($report['by_department'])) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Type Chart
new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode(array_keys($report['by_change_type'])) !!},
        datasets: [{
            data: {!! json_encode(array_values($report['by_change_type'])) !!},
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                '#9966FF', '#FF9F40', '#C9CBCF'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>
@endpush
@endsection
