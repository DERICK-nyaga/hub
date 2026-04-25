@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card order-numbers-card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="card-title mb-0" id="heading">Order Numbers</h3>
                    <p class="text-muted mb-0">Manage all orders in the system</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('order-numbers.create') }}" class="btn btn-primary order-action-btn">
                        <i class="fas fa-plus me-2"></i>New Order
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body order-filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Search orders...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Station</label>
                    <select name="station_id" class="form-select">
                        <option value="">All Stations</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100 order-action-btn">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-horder-numbers-table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order Number</th>
                            <th>Station</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $order->order_number }}</div>
                                    @if($order->description)
                                        <small class="text-muted text-truncate d-block" style="max-width: 200px;">
                                            {{ $order->description }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $order->station->name }}</td>
                                <td>{{ $order->employee_name }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td>
                                    @if($order->total_amount)
                                        <span class="order-amount-positive">${{ number_format($order->total_amount, 2) }}</span>
                                    @else
                                        <span class="order-amount-neutral">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="order-status-badge order-status-{{ $order->order_status }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm order-action-btn-group" role="group">
                                        <a href="{{ route('order-numbers.show', $order->id) }}"
                                           class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('order-numbers.edit', $order->id) }}"
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('order-numbers.destroy', $order->id) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this order?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted order-empty-state">
                                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                    No orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries
                </div>
                {{ $orders->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
