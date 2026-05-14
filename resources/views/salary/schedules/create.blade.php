@extends('layouts.salary-app')

@section('title', 'Create Schedule')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold mb-6">Create Payment Schedule</h2>
        
        <form action="{{ route('salary.schedules.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Employee</label>
                <select name="employee_id" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->position }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Scheduled Date</label>
                <input type="date" name="scheduled_date" required class="w-full border rounded-lg px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Amount (KES)</label>
                <input type="number" name="amount" step="0.01" required class="w-full border rounded-lg px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Payment Type</label>
                <select name="type" required class="w-full border rounded-lg px-3 py-2">
                    <option value="regular">Regular Salary</option>
                    <option value="advance">Advance Payment</option>
                    <option value="adjustment">Adjustment</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Create Schedule
                </button>
                <a href="{{ route('salary.schedules.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection