@extends('layouts.app')

@section('title', 'Internet Providers')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-network-wired me-2"></i>Internet Providers</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('internet-providers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add Provider
                    </a>
                </div>
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
                                <th>Paybill No.</th>
                                <th>Account Prefix</th>
                                <th>Contact</th>
                                <th>Standard Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($providers as $provider)
                            <tr>
                                <td>{{ $provider->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($provider->category) }}</span>
                                </td>
                                <td>{{ $provider->paybill_number ?? 'N/A' }}</td>
                                <td><code>{{ $provider->account_prefix }}</code></td>
                                <td>{{ $provider->support_contact }}</td>
                                <td>
                                    @if($provider->standard_amount)
                                        KES {{ number_format((float)$provider->standard_amount, 2) }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td class>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('internet-providers.edit', $provider->vendor_id) }}"
                                           class="btn btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('internet-providers.destroy', $provider->vendor_id) }}"
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                    onclick="return confirm('Delete this provider?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    <i class="fas fa-network-wired fa-2x mb-2"></i><br>
                                    No internet providers found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($providers->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $providers->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
