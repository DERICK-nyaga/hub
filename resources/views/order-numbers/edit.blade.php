@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Edit Order: {{ $orderNumber->order_number }}</h3>
        </div>

        <form action="{{ route('order-numbers.update', $orderNumber->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="order_number" class="block text-sm font-medium text-gray-700">Order Number *</label>
                        <input type="text" name="order_number" id="order_number" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('order_number', $orderNumber->order_number) }}">
                        @error('order_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="station_id" class="block text-sm font-medium text-gray-700">Station *</label>
                        <select name="station_id" id="station_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Station</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id }}" {{ old('station_id', $orderNumber->station_id) == $station->id ? 'selected' : '' }}>
                                    {{ $station->name }} - {{ $station->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('station_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Assigned Employee</label>
                        <select name="employee_id" id="employee_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">No Employee Assigned</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $orderNumber->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700">Order Date *</label>
                        <input type="date" name="order_date" id="order_date" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('order_date', $orderNumber->order_date->format('Y-m-d')) }}">
                        @error('order_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="order_status" class="block text-sm font-medium text-gray-700">Status *</label>
                        <select name="order_status" id="order_status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ old('order_status', $orderNumber->order_status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('order_status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount</label>
                        <input type="number" name="total_amount" id="total_amount" step="0.01"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('total_amount', $orderNumber->total_amount) }}">
                        @error('total_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $orderNumber->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <a href="{{ route('order-numbers.index') }}"
                   class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 mr-3">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Update Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
