{{-- resources/views/salary/deductions/index.blade.php --}}
@extends('layouts.salary-app')

@section('title', 'Salary Deductions')

@section('content')
<div x-data="deductionsManager()" x-init="init()" class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-minus-circle text-red-600"></i> 
                Salary Deductions
            </h2>
            <p class="text-slate-500 text-sm mt-1">Manage penalties, loans, losses, and other deductions</p>
        </div>
        <button @click="openCreateModal()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
            <i class="fas fa-plus-circle"></i> New Deduction
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-red-50 to-white rounded-xl p-5 border border-red-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-red-600 text-sm font-medium">Total Deductions</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">KES {{ number_format($totalDeductions ?? 0, 2) }}</p>
                </div>
                <div class="bg-red-100 p-2 rounded-lg"><i class="fas fa-chart-line text-red-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-amber-50 to-white rounded-xl p-5 border border-amber-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-amber-600 text-sm font-medium">Pending Approval</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($pendingApprovals ?? 0) }}</p>
                </div>
                <div class="bg-amber-100 p-2 rounded-lg"><i class="fas fa-clock text-amber-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-5 border border-purple-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-purple-600 text-sm font-medium">This Month</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">KES {{ number_format($monthlyTotal ?? 0, 2) }}</p>
                </div>
                <div class="bg-purple-100 p-2 rounded-lg"><i class="fas fa-calendar-alt text-purple-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-5 border border-blue-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Employees Affected</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($affectedEmployees ?? 0) }}</p>
                </div>
                <div class="bg-blue-100 p-2 rounded-lg"><i class="fas fa-users text-blue-600 text-xl"></i></div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('salary.deductions.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Employee, reason..." 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="applied" {{ request('status') == 'applied' ? 'selected' : '' }}>Applied</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                    <select name="type" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="penalty" {{ request('type') == 'penalty' ? 'selected' : '' }}>Penalty</option>
                        <option value="loan" {{ request('type') == 'loan' ? 'selected' : '' }}>Loan Recovery</option>
                        <option value="advance_recovery" {{ request('type') == 'advance_recovery' ? 'selected' : '' }}>Advance Recovery</option>
                        <option value="loss" {{ request('type') == 'loss' ? 'selected' : '' }}>Loss</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Employee</label>
                    <select name="employee_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Employees</option>
                        @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('salary.deductions.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm hover:bg-slate-50 transition">Reset</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                    <i class="fas fa-search mr-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Deductions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Reason</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deductions as $deduction)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 text-sm font-mono text-slate-500">#{{ $deduction->id }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $deduction->employee->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $deduction->employee->position ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-sm text-slate-700">{{ $deduction->reason }}</div>
                            @if($deduction->description)
                                <div class="text-xs text-slate-400">{{ Str::limit($deduction->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-bold text-red-600">-KES {{ number_format($deduction->amount, 2) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $typeColors = [
                                    'penalty' => 'bg-orange-100 text-orange-700',
                                    'loan' => 'bg-blue-100 text-blue-700',
                                    'advance_recovery' => 'bg-purple-100 text-purple-700',
                                    'loss' => 'bg-red-100 text-red-700',
                                    'other' => 'bg-gray-100 text-gray-700'
                                ];
                                $typeIcons = [
                                    'penalty' => 'gavel',
                                    'loan' => 'hand-holding-usd',
                                    'advance_recovery' => 'money-bill-wave',
                                    'loss' => 'frown',
                                    'other' => 'ellipsis-h'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit {{ $typeColors[$deduction->type] ?? 'bg-gray-100' }}">
                                <i class="fas fa-{{ $typeIcons[$deduction->type] ?? 'tag' }}"></i>
                                {{ ucfirst(str_replace('_', ' ', $deduction->type)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ \Carbon\Carbon::parse($deduction->deduction_date)->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'applied' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-gray-100 text-gray-700'
                                ];
                                $statusIcons = [
                                    'pending' => 'clock',
                                    'applied' => 'check-circle',
                                    'cancelled' => 'ban'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit {{ $statusColors[$deduction->status] ?? 'bg-gray-100' }}">
                                <i class="fas fa-{{ $statusIcons[$deduction->status] ?? 'info-circle' }}"></i>
                                {{ ucfirst($deduction->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="viewDeduction({{ $deduction->id }})" class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($deduction->status === 'pending')
                                <button onclick="approveDeduction({{ $deduction->id }})" class="text-emerald-600 hover:bg-emerald-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="cancelDeduction({{ $deduction->id }})" class="text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                @endif
                                @if($deduction->payment_id)
                                <a href="{{ route('salary.payments.show', $deduction->payment_id) }}" class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-link"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            <p>No deduction records found</p>
                            <button @click="openCreateModal()" class="text-red-600 hover:underline text-sm mt-2 inline-block">Create first deduction</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $deductions->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Create/Edit Deduction Modal -->
<div id="deductionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 id="modalTitle" class="text-xl font-bold">New Deduction</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="deductionForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="deduction_id" name="deduction_id">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Employee <span class="text-red-500">*</span></label>
                <select id="employee_id" name="employee_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="">Select Employee</option>
                    @foreach($employees ?? [] as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->position }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <input type="text" id="reason" name="reason" required placeholder="e.g., Late arrival penalty, Equipment loss"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount (KES) <span class="text-red-500">*</span></label>
                    <input type="number" id="amount" name="amount" step="0.01" required placeholder="0.00"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required class="w-full border border-slate-300 rounded-lg px-3 py-2">
                        <option value="penalty">Penalty / Fine</option>
                        <option value="loan">Loan Recovery</option>
                        <option value="advance_recovery">Advance Payment Recovery</option>
                        <option value="loss">Loss / Damage</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deduction Date <span class="text-red-500">*</span></label>
                    <input type="date" id="deduction_date" name="deduction_date" required value="{{ date('Y-m-d') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description (Optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Additional details about this deduction..."
                          class="w-full border border-slate-300 rounded-lg px-3 py-2"></textarea>
            </div>
            
            <div class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-sm">
                <i class="fas fa-info-circle text-amber-600 mr-2"></i>
                This deduction will be pending approval. Once approved, it will be applied to the next salary payment.
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-save mr-1"></i> Save Deduction
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Deduction Modal -->
<div id="viewDeductionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold">Deduction Details</h3>
            <button onclick="closeViewModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="deductionDetailsContent" class="p-6">
            <!-- Dynamic content loads here -->
        </div>
    </div>
</div>

<script>
function deductionsManager() {
    return {
        init() {
            // Auto-hide flash messages
            setTimeout(() => {
                const alerts = document.querySelectorAll('.flash-message');
                alerts.forEach(alert => alert.style.display = 'none');
            }, 5000);
        },
        openCreateModal() {
            document.getElementById('modalTitle').innerText = 'New Deduction';
            document.getElementById('deductionForm').reset();
            document.getElementById('deduction_id').value = '';
            document.getElementById('deduction_date').value = '{{ date("Y-m-d") }}';
            document.getElementById('deductionModal').classList.remove('hidden');
        }
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    const deductionForm = document.getElementById('deductionForm');
    
    if (deductionForm) {
        deductionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const formData = new FormData(this);
            const id = document.getElementById('deduction_id').value;
            const url = id ? `/salary/deductions/${id}` : '/salary/deductions';
            const method = id ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save deduction'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving deduction. Please try again.');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    }
});

// View deduction details
function viewDeduction(id) {
    fetch(`/salary/deductions/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs text-slate-500">Employee</label><p class="font-semibold">${data.employee?.name}</p></div>
                        <div><label class="text-xs text-slate-500">Position</label><p>${data.employee?.position || 'N/A'}</p></div>
                        <div><label class="text-xs text-slate-500">Reason</label><p class="font-medium">${data.reason}</p></div>
                        <div><label class="text-xs text-slate-500">Amount</label><p class="text-xl font-bold text-red-600">-KES ${Number(data.amount).toLocaleString()}</p></div>
                        <div><label class="text-xs text-slate-500">Type</label><p>${data.type.replace('_', ' ').toUpperCase()}</p></div>
                        <div><label class="text-xs text-slate-500">Date</label><p>${new Date(data.deduction_date).toLocaleDateString()}</p></div>
                        <div><label class="text-xs text-slate-500">Status</label><p><span class="px-2 py-1 rounded-full text-xs ${data.status === 'applied' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">${data.status.toUpperCase()}</span></p></div>
                        ${data.description ? `<div class="col-span-2"><label class="text-xs text-slate-500">Description</label><p class="text-sm">${data.description}</p></div>` : ''}
                        ${data.payment_id ? `<div class="col-span-2"><label class="text-xs text-slate-500">Applied to Payment</label><p><a href="/salary/payments/${data.payment_id}" class="text-indigo-600 hover:underline">View Payment #${data.payment_id}</a></p></div>` : ''}
                    </div>
                </div>
            `;
            document.getElementById('deductionDetailsContent').innerHTML = content;
            document.getElementById('viewDeductionModal').classList.remove('hidden');
        })
        .catch(error => {
            alert('Error loading deduction details');
        });
}

function approveDeduction(id) {
    if(confirm('Approve this deduction? It will be applied to the next salary payment.')) {
        fetch(`/salary/deductions/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

function cancelDeduction(id) {
    if(confirm('Cancel this deduction?')) {
        fetch(`/salary/deductions/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

function closeModal() {
    document.getElementById('deductionModal').classList.add('hidden');
}

function closeViewModal() {
    document.getElementById('viewDeductionModal').classList.add('hidden');
}
</script>

@push('styles')
<style>
    table { min-width: 900px; }
    .pagination { display: flex; justify-content: center; gap: 0.5rem; }
    .pagination .page-item { list-style: none; }
    .pagination .page-link { padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: white; border: 1px solid #e2e8f0; }
    .pagination .active .page-link { background: #dc2626; color: white; border-color: #dc2626; }
</style>
@endpush
@endsection