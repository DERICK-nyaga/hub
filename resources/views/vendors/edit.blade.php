@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($vendor);
    $action = $isEdit ? route('vendors.update', $vendor) : route('vendors.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method($method)

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="name" class="form-label">Name*</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="{{ old('name', $vendor->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category*</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $vendor->category_id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="contact_name" class="form-label">Contact Person</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name"
                       value="{{ old('contact_name', $vendor->contact_name ?? '') }}">
            </div>

            <div class="mb-3">
                <label for="contact_email" class="form-label">Contact Email</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email"
                       value="{{ old('contact_email', $vendor->contact_email ?? '') }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="payment_terms" class="form-label">Payment Terms*</label>
                <select class="form-select" id="payment_terms" name="payment_terms" required>
                    <option value="">Select Payment Terms</option>
                    <option value="net_15" {{ old('payment_terms', $vendor->payment_terms ?? '') == 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                    <option value="net_30" {{ old('payment_terms', $vendor->payment_terms ?? '') == 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                    <option value="net_60" {{ old('payment_terms', $vendor->payment_terms ?? '') == 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                    <option value="due_on_receipt" {{ old('payment_terms', $vendor->payment_terms ?? '') == 'due_on_receipt' ? 'selected' : '' }}>Due on Receipt</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="contract_file" class="form-label">Contract</label>
                <input type="file" class="form-control" id="contract_file" name="contract_file">
                @if($isEdit && $vendor->contract_path)
                    <div class="mt-2">
                        <a href="{{ route('vendors.contract.download', $vendor) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file"></i> View Current Contract
                        </a>
                    </div>
                @endif
            </div>

            @if($isEdit)
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                           {{ old('is_active', $vendor->is_active ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active Vendor</label>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $vendor->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Update' : 'Create' }} Vendor
        </button>
        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
