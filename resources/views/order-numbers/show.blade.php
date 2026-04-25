@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card order-numbers-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Order Details</h4>
                            <p class="text-muted mb-0">{{ $orderNumber->order_number }}</p>
                        </div>
                        <div class="btn-group order-action-btn-group">
                            <a href="{{ route('order-numbers.edit', $orderNumber->id) }}"
                               class="btn btn-warning order-action-btn">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                            <a href="{{ route('order-numbers.index') }}"
                               class="btn btn-secondary order-action-btn">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body order-numbers-details">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Order Number:</th>
                                    <td>{{ $orderNumber->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td>{{ $orderNumber->order_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $orderNumber->order_status == 'completed' ? 'success' : ($orderNumber->order_status == 'pending' ? 'warning' : 'info') }}">
                                            {{ ucfirst($orderNumber->order_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td>${{ number_format($orderNumber->total_amount, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Assignment Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Station:</th>
                                    <td>{{ $orderNumber->station->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Employee:</th>
                                    <td>
                                        @if($orderNumber->employee)
                                            {{ $orderNumber->employee->first_name }} {{ $orderNumber->employee->last_name }}
                                        @else
                                            Not Assigned
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $orderNumber->description ?? 'No description' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
