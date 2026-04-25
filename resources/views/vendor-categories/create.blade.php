@extends('layouts.app')
@section('content')
@php
    $isEdit = isset($vendorCategory);
    $action = $isEdit ? route('vendor-categories.update', $vendorCategory) : route('vendor-categories.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @method($method)

    <div class="mb-3">
        <label for="name" class="form-label">Name*</label>
        <input type="text" class="form-control" id="name" name="name"
               value="{{ old('name', $vendorCategory->name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $vendorCategory->description ?? '') }}</textarea>
    </div>

    @if($isEdit)
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
               {{ old('is_active', $vendorCategory->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active Category</label>
    </div>
    @endif

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Update' : 'Create' }} Category
        </button>
        <a href="{{ route('vendor-categories.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
