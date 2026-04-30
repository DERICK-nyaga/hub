@extends('layouts.salary-app')

@section('title', 'Create Payment')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white"><i class="fas fa-plus-circle mr-2"></i>New Payment Request</h2>
        </div>
        
        <form method="POST" action="{{ route('salary.payments.store') }}" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-salary="{{ $employee->salary }}" data-deductions="{{ $employee->deduction_balance ?? 0 }}">
                            {{ $employee->full_name }} ({{ $employee->employee_id ?? $employee->id }}) - 
                            {{ $employee->position }} - 
                            Base: KES {{ number_format($employee->salary, 2) }}
                            @if(($employee->deduction_balance ?? 0) > 0)
                            | Debt: KES {{ number_format($employee->deduction_balance, 2) }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full border-gray-300 rounded-lg focus:ring-indigo-500">
                        <option value="regular">Regular Monthly Salary</option>
                        <option value="advance">Advance Payment</option>
                        <option value="adjustment">Adjustment / Bonus</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount (KES) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount" step="0.01" required class="w-full border-gray-300 rounded-lg focus:ring-indigo-500" placeholder="Enter amount">
                    <p class="text-xs text-gray-500 mt-1" id="salaryHint"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method <span class="text-red-500">*</span></label>
                    <select name="payment_method" required class="w-full border-gray-300 rounded-lg focus:ring-indigo-500">
                        <option value="mpesa">📱 M-PESA</option>
                        <option value="bank_transfer">🏛️ Bank Transfer</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500" placeholder="Any additional information..."></textarea>
                </div>
            </div>
            
            <!-- Deductions Summary -->
            <div id="deductionsSummary" class="bg-red-50 border-l-4 border-red-500 p-4 rounded hidden">
                <p class="text-sm text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Pending Deductions:</strong> <span id="deductionsAmount">KES 0.00</span>
                    will be deducted from this payment.
                </p>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-700"><i class="fas fa-info-circle mr-2"></i>
                    This request will be sent for approval. Once approved, payment will be processed automatically.
                </p>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('salary.payments.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Submit for Approval
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-fill amount based on selected employee's salary
    document.querySelector('select[name="employee_id"]').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const salary = selectedOption.dataset.salary;
        const deductions = selectedOption.dataset.deductions || 0;
        const amountField = document.getElementById('amount');
        const salaryHint = document.getElementById('salaryHint');
        const deductionsSummary = document.getElementById('deductionsSummary');
        const deductionsAmount = document.getElementById('deductionsAmount');
        
        if (salary) {
            amountField.value = salary;
            salaryHint.innerHTML = `💰 Base salary: KES ${parseFloat(salary).toLocaleString()}`;
            
            if (parseFloat(deductions) > 0) {
                deductionsSummary.classList.remove('hidden');
                deductionsAmount.innerHTML = `KES ${parseFloat(deductions).toLocaleString()}`;
                salaryHint.innerHTML += ` | ⚠️ Deduction balance: KES ${parseFloat(deductions).toLocaleString()}`;
            } else {
                deductionsSummary.classList.add('hidden');
            }
        } else {
            salaryHint.innerHTML = '';
            deductionsSummary.classList.add('hidden');
        }
    });
</script>
@endsection