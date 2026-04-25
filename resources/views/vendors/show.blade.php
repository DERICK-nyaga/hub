@extends('layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Vendor Details</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Vendor Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $vendor->name }}</p>
                            <p><strong>Category:</strong> {{ $vendor->category->name }}</p>
                            <p><strong>Contact:</strong> {{ $vendor->contact_name }}</p>
                            <p><strong>Email:</strong> {{ $vendor->contact_email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $vendor->contact_phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Payment Terms:</strong> {{ str_replace('_', ' ', ucfirst($vendor->payment_terms)) }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $vendor->is_active ? 'success' : 'danger' }}">
                                    {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                            @if($vendor->contract_path)
                                <p><strong>Contract:</strong>
                                    <a href="{{ route('vendors.contract.download', $vendor) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p><strong>Notes:</strong></p>
                            <p>{{ $vendor->notes ?? 'No notes available' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Account Number:</strong> {{ $vendor->account_number ?? 'N/A' }}</p>
                    <p><strong>Tax ID:</strong> {{ $vendor->tax_id ?? 'N/A' }}</p>
                    <p><strong>Website:</strong>
                        @if($vendor->website)
                            <a href="{{ $vendor->website }}" target="_blank">{{ $vendor->website }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Address:</strong> {{ $vendor->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
