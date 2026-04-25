@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Create New Vendor</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('vendors.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3 border-bottom pb-2">Basic Information</h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label required">Vendor Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label required">Category</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                            id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror"
                                           id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="payment_terms" class="form-label required">Payment Terms</label>
                                    <select class="form-select @error('payment_terms') is-invalid @enderror"
                                            id="payment_terms" name="payment_terms" required>
                                        <option value="">Select Payment Terms</option>
                                        <option value="net_15" {{ old('payment_terms') == 'net_15' ? 'selected' : '' }}>Net 15</option>
                                        <option value="net_30" {{ old('payment_terms') == 'net_30' ? 'selected' : '' }}>Net 30</option>
                                        <option value="net_60" {{ old('payment_terms') == 'net_60' ? 'selected' : '' }}>Net 60</option>
                                        <option value="due_on_receipt" {{ old('payment_terms') == 'due_on_receipt' ? 'selected' : '' }}>Due on Receipt</option>
                                    </select>
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                           value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active Vendor</label>
                                    @error('is_active')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3 border-bottom pb-2">Contact Information</h5>

                                <div class="mb-3">
                                    <label for="contact_name" class="form-label">Contact Person</label>
                                    <input type="text" class="form-control @error('contact_name') is-invalid @enderror"
                                           id="contact_name" name="contact_name" value="{{ old('contact_name') }}">
                                    @error('contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror"
                                           id="contact_email" name="contact_email" value="{{ old('contact_email') }}">
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control @error('contact_phone') is-invalid @enderror"
                                           id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contract_file" class="form-label">Contract File</label>
                                    <input type="file" class="form-control @error('contract_file') is-invalid @enderror"
                                           id="contract_file" name="contract_file">
                                    <small class="text-muted">PDF, DOC, DOCX (Max: 5MB)</small>
                                    @error('contract_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5 class="mb-3 border-bottom pb-2">Financial Information</h5>

                                <div class="mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                           id="account_number" name="account_number" value="{{ old('account_number') }}">
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tax_id" class="form-label">Tax ID</label>
                                    <input type="text" class="form-control @error('tax_id') is-invalid @enderror"
                                           id="tax_id" name="tax_id" value="{{ old('tax_id') }}">
                                    @error('tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3 border-bottom pb-2">Additional Information</h5>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('vendors.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Vendor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
