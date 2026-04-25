@extends('layouts.app')

@section('title', 'Add Internet Provider')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Internet Provider</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('internet-providers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('internet-providers.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Provider Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Service Category *</label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="fiber" {{ old('category') == 'fiber' ? 'selected' : '' }}>Fiber</option>
                                <option value="wireless" {{ old('category') == 'wireless' ? 'selected' : '' }}>Wireless</option>
                                <option value="satellite" {{ old('category') == 'satellite' ? 'selected' : '' }}>Satellite</option>
                                <option value="cable" {{ old('category') == 'cable' ? 'selected' : '' }}>Cable</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="paybill_number" class="form-label">Paybill Number</label>
                            <input type="text" class="form-control @error('paybill_number') is-invalid @enderror"
                                   id="paybill_number" name="paybill_number" value="{{ old('paybill_number') }}"
                                   placeholder="e.g., 123456">
                            @error('paybill_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="account_prefix" class="form-label">Account Prefix *</label>
                            <input type="text" class="form-control @error('account_prefix') is-invalid @enderror"
                                   id="account_prefix" name="account_prefix" value="{{ old('account_prefix') }}"
                                   placeholder="e.g., ISP-001" required>
                            <small class="text-muted">Unique prefix for account numbers</small>
                            @error('account_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="support_contact" class="form-label">Support Contact *</label>
                            <input type="text" class="form-control @error('support_contact') is-invalid @enderror"
                                   id="support_contact" name="support_contact" value="{{ old('support_contact') }}"
                                   placeholder="e.g., 0700123456" required>
                            @error('support_contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="billing_email" class="form-label">Billing Email</label>
                            <input type="email" class="form-control @error('billing_email') is-invalid @enderror"
                                   id="billing_email" name="billing_email" value="{{ old('billing_email') }}"
                                   placeholder="e.g., billing@provider.com">
                            @error('billing_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="standard_amount" class="form-label">Standard Amount (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('standard_amount') is-invalid @enderror"
                                   id="standard_amount" name="standard_amount" value="{{ old('standard_amount') }}">
                            @error('standard_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="due_day" class="form-label">Due Day of Month</label>
                            <input type="number" min="1" max="31" class="form-control @error('due_day') is-invalid @enderror"
                                   id="due_day" name="due_day" value="{{ old('due_day', 1) }}">
                            <small class="text-muted">Default: 1st of month</small>
                            @error('due_day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="grace_period_days" class="form-label">Grace Period (Days)</label>
                            <input type="number" min="0" max="30" class="form-control @error('grace_period_days') is-invalid @enderror"
                                   id="grace_period_days" name="grace_period_days" value="{{ old('grace_period_days', 3) }}">
                            <small class="text-muted">Default: 3 days</small>
                            @error('grace_period_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('internet-providers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Provider
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
