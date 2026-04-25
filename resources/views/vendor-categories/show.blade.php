@extends('layouts.app')

@section('title', 'Vendor Category Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Vendor Category Details</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('vendor-categories.edit', $vendorCategory) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('vendor-categories.destroy', $vendorCategory) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Category Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $vendorCategory->name }}</p>
                            <p><strong>Slug:</strong> {{ $vendorCategory->slug }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $vendorCategory->is_active ? 'success' : 'danger' }}">
                                    {{ $vendorCategory->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created At:</strong> {{ $vendorCategory->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Updated At:</strong> {{ $vendorCategory->updated_at->format('M d, Y H:i') }}</p>
                            @if($vendorCategory->deleted_at)
                                <p><strong>Deleted At:</strong> {{ $vendorCategory->deleted_at->format('M d, Y H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <p><strong>Description:</strong></p>
                            <div class="border p-3 rounded bg-light">
                                {{ $vendorCategory->description ?? 'No description available' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Vendors:</span>
                        <strong>{{ $vendorCategory->vendors_count }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Active Vendors:</span>
                        <strong>{{ $vendorCategory->vendors()->active()->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Added Vendor:</span>
                        <strong>
                            @if($vendorCategory->vendors()->latest()->first())
                                {{ $vendorCategory->vendors()->latest()->first()->created_at->diffForHumans() }}
                            @else
                                N/A
                            @endif
                        </strong>
                    </div>
                </div>
            </div>

            @if($vendorCategory->vendors_count > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Vendors</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($vendorCategory->vendors()->latest()->limit(5)->get() as $vendor)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('vendors.show', $vendor) }}" class="text-decoration-none">
                                {{ $vendor->name }}
                            </a>
                            <span class="badge bg-{{ $vendor->is_active ? 'success' : 'secondary' }}">
                                {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                    @if($vendorCategory->vendors_count > 5)
                    <div class="text-center mt-2">
                        <a href="{{ route('vendors.index', ['category' => $vendorCategory->id]) }}" class="btn btn-sm btn-outline-primary">
                            View All Vendors
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
