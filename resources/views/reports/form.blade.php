@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $report->title ?? '') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $report->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Sales" @selected(old('category', $report->category ?? '') === 'Sales')>Sales</option>
                    <option value="Finance" @selected(old('category', $report->category ?? '') === 'Finance')>Finance</option>
                    <option value="Operations" @selected(old('category', $report->category ?? '') === 'Operations')>Operations</option>
                    <option value="HR" @selected(old('category', $report->category ?? '') === 'HR')>HR</option>
                    <option value="Other" @selected(old('category', $report->category ?? '') === 'Other')>Other</option>
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="Draft" @selected(old('status', $report->status ?? '') === 'Draft')>Draft</option>
                    <option value="Published" @selected(old('status', $report->status ?? '') === 'Published')>Published</option>
                    <option value="Archived" @selected(old('status', $report->status ?? '') === 'Archived')>Archived</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="report_file" class="form-label">Report File</label>
                <input type="file" class="form-control @error('report_file') is-invalid @enderror" id="report_file" name="report_file">
                @error('report_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if(isset($report) && $report->file_path)
                    <div class="mt-2">
                        Current file: {{ basename($report->file_path) }}
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

@endsection
