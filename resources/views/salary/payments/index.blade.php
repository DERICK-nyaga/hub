@extends('layouts.salary-app')

@section('title', 'Salary Payments')

@section('content')
<div x-data="paymentsManager()" x-init="init()" class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-indigo-600"></i> 
                Salary Payments
            </h2>
            <p class="text-slate-500 text-sm mt-1">Manage payroll, approvals, and payment history</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('salary.payments.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> New Payment
            </a>
            <button @click="exportData()" class="bg-indigo-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-indigo-50 to-white rounded-xl p-5 border border-indigo-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-indigo-600 text-sm font-medium">Total Payments</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalPayments ?? 0) }}</p>
                </div>
                <div class="bg-indigo-100 p-2 rounded-lg"><i class="fas fa-receipt text-indigo-600 text-xl"></i></div>
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
        <div class="bg-gradient-to-br from-emerald-50 to-white rounded-xl p-5 border border-emerald-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-emerald-600 text-sm font-medium">This Month</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">KES {{ number_format($monthlyTotal ?? 0, 2) }}</p>
                </div>
                <div class="bg-emerald-100 p-2 rounded-lg"><i class="fas fa-calendar-alt text-emerald-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-5 border border-blue-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Avg Payment</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">KES {{ number_format($avgPayment ?? 0, 2) }}</p>
                </div>
                <div class="bg-blue-100 p-2 rounded-lg"><i class="fas fa-chart-line text-blue-600 text-xl"></i></div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('salary.payments.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Employee name, reference..." 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Payment Type</label>
                    <select name="type" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Regular Salary</option>
                        <option value="advance" {{ request('type') == 'advance' ? 'selected' : '' }}>Advance</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Month</label>
                    <input type="month" name="month" value="{{ request('month') }}" 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('salary.payments.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm hover:bg-slate-50 transition">Reset</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Net Pay</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Method</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 text-sm font-mono text-slate-500">#{{ $payment->id }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $payment->employee->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $payment->employee->position ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-semibold text-slate-700">KES {{ number_format($payment->amount, 2) }}</span>
                            @if($payment->deductions_total > 0)
                                <div class="text-xs text-red-500">-{{ number_format($payment->deductions_total, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-bold text-emerald-600">KES {{ number_format($payment->net_amount, 2) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $typeColors = [
                                    'regular' => 'bg-blue-100 text-blue-700',
                                    'advance' => 'bg-purple-100 text-purple-700',
                                    'adjustment' => 'bg-orange-100 text-orange-700'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$payment->type] ?? 'bg-gray-100' }}">
                                {{ ucfirst($payment->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="flex items-center gap-1 text-sm">
                                <i class="fas fa-{{ $payment->payment_method === 'mpesa' ? 'mobile-alt' : 'university' }} text-slate-400"></i>
                                {{ strtoupper($payment->payment_method === 'mpesa' ? 'M-PESA' : 'BANK') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = [
                                    'pending_approval' => 'bg-amber-100 text-amber-700',
                                    'approved' => 'bg-blue-100 text-blue-700',
                                    'processed' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-red-100 text-red-700'
                                ];
                                $statusIcons = [
                                    'pending_approval' => 'clock',
                                    'approved' => 'check-circle',
                                    'processed' => 'check-double',
                                    'failed' => 'times-circle'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit {{ $statusColors[$payment->status] ?? 'bg-gray-100' }}">
                                <i class="fas fa-{{ $statusIcons[$payment->status] ?? 'info-circle' }}"></i>
                                {{ str_replace('_', ' ', ucfirst($payment->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="viewPayment({{ $payment->id }})" class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($payment->status === 'pending_approval')
                                <button onclick="approvePayment({{ $payment->id }})" class="text-emerald-600 hover:bg-emerald-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="rejectPayment({{ $payment->id }})" class="text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                @endif
                                <button onclick="printReceipt({{ $payment->id }})" class="text-slate-500 hover:bg-slate-50 p-1.5 rounded-lg transition">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            <p>No payment records found</p>
                            <a href="{{ route('salary.payments.create') }}" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">Create first payment</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $payments->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div id="viewPaymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold">Payment Details</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="paymentDetailsContent" class="p-6">
            <!-- Dynamic content loads here -->
        </div>
    </div>
</div>

<script>
function paymentsManager() {
    return {
        init() {
            // Auto-hide flash messages
            setTimeout(() => {
                const alerts = document.querySelectorAll('.flash-message');
                alerts.forEach(alert => alert.style.display = 'none');
            }, 5000);
        },
        exportData() {
            window.location.href = '{{ route("salary.payments.index") }}?export=true&' + new URLSearchParams(window.location.search).toString();
        }
    }
}

// View payment details
function viewPayment(id) {
    fetch(`/salary/payments/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs text-slate-500">Transaction Ref</label><p class="font-mono text-sm">${data.transaction_reference}</p></div>
                        <div><label class="text-xs text-slate-500">Payment Date</label><p class="font-semibold">${new Date(data.payment_date).toLocaleDateString()}</p></div>
                        <div><label class="text-xs text-slate-500">Employee</label><p class="font-semibold">${data.employee?.name}</p></div>
                        <div><label class="text-xs text-slate-500">Payment Method</label><p>${data.payment_method.toUpperCase()}</p></div>
                        <div><label class="text-xs text-slate-500">Gross Amount</label><p class="text-lg font-bold">KES ${Number(data.amount).toLocaleString()}</p></div>
                        <div><label class="text-xs text-slate-500">Deductions</label><p class="text-red-600">-KES ${Number(data.deductions_total).toLocaleString()}</p></div>
                        <div class="col-span-2 border-t pt-3"><label class="text-sm font-bold">Net Payment</label><p class="text-2xl font-bold text-emerald-600">KES ${Number(data.net_amount).toLocaleString()}</p></div>
                        ${data.notes ? `<div class="col-span-2"><label class="text-xs text-slate-500">Notes</label><p class="text-sm">${data.notes}</p></div>` : ''}
                    </div>
                    ${data.status === 'processed' ? `<div class="bg-emerald-50 p-3 rounded-lg text-center"><i class="fas fa-check-circle text-emerald-600"></i> Payment processed successfully</div>` : ''}
                </div>
            `;
            document.getElementById('paymentDetailsContent').innerHTML = content;
            document.getElementById('viewPaymentModal').classList.remove('hidden');
        })
        .catch(error => {
            alert('Error loading payment details');
        });
}

function closeModal() {
    document.getElementById('viewPaymentModal').classList.add('hidden');
}

function approvePayment(id) {
    if(confirm('Approve this payment? It will be processed automatically.')) {
        fetch(`/salary/payments/${id}/approve`, {
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

function rejectPayment(id) {
    if(confirm('Reject this payment?')) {
        // Implement reject logic
        alert('Reject functionality - implement via your controller');
    }
}

function printReceipt(id) {
    window.open(`/salary/payments/${id}/print`, '_blank');
}
</script>

@push('styles')
<style>
    table { min-width: 800px; }
    .pagination { display: flex; justify-content: center; gap: 0.5rem; }
    .pagination .page-item { list-style: none; }
    .pagination .page-link { padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: white; border: 1px solid #e2e8f0; }
    .pagination .active .page-link { background: #4f46e5; color: white; border-color: #4f46e5; }
</style>
@endpush
@endsection