@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Manage Reports</h1>
    <div>
        <button class="btn btn-outline-primary">Bulk Actions</button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" id="select-all">
                </th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Submitted By</th>
                <th>Submitted At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
                <tr>
                    <td><input type="checkbox" name="reports[]" value="{{ $report->id }}"></td>
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
                        @if($report->status === 'Pending')
                            <button class="btn btn-sm btn-success">Approve</button>
                            <button class="btn btn-sm btn-danger">Reject</button>
                        @endif
                        <a href="{{ route('reports.download', $report->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $reports->links() }}
@endsection
