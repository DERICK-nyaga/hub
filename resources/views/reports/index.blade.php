@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Reports</h1>
        <a href="{{ route('reports.create') }}" class="btn btn-primary">Create New Report</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->title }}</td>
                        <td>{{ $report->category }}</td>
                        <td>
                            <span class="badge bg-{{ $report->status === 'Published' ? 'success' : ($report->status === 'Draft' ? 'warning' : 'secondary') }}">
                                {{ $report->status }}
                            </span>
                        </td>
                        <td>{{ $report->user->name }}</td>
                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('reports.show', $report->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('reports.edit', $report->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @if($report->file_path)
                                <a href="{{ route('reports.download', $report->id) }}" class="btn btn-sm btn-success">Download</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $reports->links() }}
@endsection
