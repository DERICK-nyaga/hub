@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $report->title }}</h1>
        <div>
            <a href="{{ route('reports.edit', $report->id) }}" class="btn btn-primary">Edit</a>
            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-3">
                <span class="badge bg-{{ $report->status === 'Published' ? 'success' : ($report->status === 'Draft' ? 'warning' : 'secondary') }}">
                    {{ $report->status }}
                </span>
                <span class="badge bg-info ms-2">{{ $report->category }}</span>
            </div>

            <p class="card-text">{{ $report->description }}</p>

            @if($report->file_path)
                <div class="mt-3">
                    <a href="{{ route('reports.download', $report->id) }}" class="btn btn-outline-primary">
                        Download Report File
                    </a>
                </div>
            @endif

            <div class="mt-4 text-muted">
                <small>
                    Created by: {{ $report->user->name }} on {{ $report->created_at->format('M d, Y') }}
                    <br>
                    Last updated: {{ $report->updated_at->format('M d, Y') }}
                </small>
            </div>
        </div>
    </div>

    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
@endsection
