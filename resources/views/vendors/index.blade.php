@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Vendor Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('vendors.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Vendor
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Contact</th>
                            <th>Payment Terms</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->category->name }}</td>
                            <td>{{ $vendor->contact_name }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($vendor->payment_terms)) }}</td>
                            <td>
                                <span class="badge bg-{{ $vendor->is_active ? 'success' : 'danger' }}">
                                    {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection
