@extends('layouts.salary-app')

@section('title', 'Payment History')

@section('content')
<div x-data="paymentHistoryManager()" x-init="init()" class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-history text-indigo-600"></i> 
                Payment Transaction History
            </h2>
            <p class="text-slate-500 text-sm mt-1">Complete payment records, monthly summaries, and transaction tracking</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button @click="exportHistory()" 
                    class="bg-indigo-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-download"></i> Export CSV
            </button>
            <button @click="printReport()" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Monthly Summary Cards -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white">
        <h3 class="text-lg font-semibold mb-4">Annual Summary {{ date('Y') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-indigo-200 text-sm">Total Disbursed</p>
                <p class="text-2xl font-bold">KES {{ number_format($yearlyTotal ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-sm">Total Transactions</p>
                <p class="text-2xl font-bold">{{ number_format($totalTransactions ?? 0) }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-sm">Average Payment</p>
                <p class="text-2xl font-bold">KES {{ number_format($averagePayment ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-sm">Total Deductions</p>
                <p class="text-2xl font-bold">KES {{ number_format($totalDeductions ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Monthly Summary Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-slate-800">Monthly Payment Trends</h3>
            <select id="yearSelect" onchange="updateChart()" class="border border-slate-300 rounded-lg px-3 py-1 text-sm">
                @for($i = date('Y'); $i >= date('Y')-3; $i--)
                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <canvas id="monthlyChart" height="80"></canvas>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('salary.history') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Year</label>
                    <select name="year" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Years</option>
                        @for($i = date('Y'); $i >= date('Y')-5; $i--)
                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Month</label>
                    <select name="month" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Months</option>
                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                        <option value="{{ $index + 1 }}" {{ request('month') == ($index + 1) ? 'selected' : '' }}>{{ $month }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Payment Type</label>
                    <select name="type" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Regular Salary</option>
                        <option value="advance" {{ request('type') == 'advance' ? 'selected' : '' }}>Advance</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Payment Method</label>
                    <select name="method" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Methods</option>
                        <option value="mpesa" {{ request('method') == 'mpesa' ? 'selected' : '' }}>M-PESA</option>
                        <option value="bank_transfer" {{ request('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 transition">
                        <i class="fas fa-filter mr-1"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Monthly Summary Table - WITH SAFETY CHECK -->
    @if(isset($summary) && $summary->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b">
            <h3 class="font-semibold text-slate-800">Monthly Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600">Month</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600">Total Amount</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600">Transactions</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600">Average Amount</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($summary as $month)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium">{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('F Y') }}</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-600">KES {{ number_format($month->total_amount, 2) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($month->total_count) }}</td>
                        <td class="px-5 py-3 text-right text-slate-600">KES {{ number_format($month->average_amount, 2) }}</td>
                        <td class="px-5 py-3 text-center">
                            <button onclick="viewMonthDetails('{{ $month->month }}')" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Transaction History Table - WITH SAFETY CHECK -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">Transaction History</h3>
            <span class="text-sm text-slate-500">{{ isset($payments) ? $payments->total() : 0 }} records found</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Ref #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Gross</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Deductions</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Net Paid</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Method</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments ?? [] as $payment)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs text-slate-500">{{ $payment->transaction_reference }}</span>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $payment->employee->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-400">{{ $payment->employee->position ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                {{ ucfirst($payment->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-medium">KES {{ number_format($payment->amount, 2) }}</td>
                        <td class="px-5 py-3 text-right text-red-600">
                            @if($payment->deductions_total > 0)
                            -KES {{ number_format($payment->deductions_total, 2) }}
                            @else
                            —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-600">KES {{ number_format($payment->net_amount, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="flex items-center gap-1 text-sm">
                                <i class="fas fa-{{ $payment->payment_method === 'mpesa' ? 'mobile-alt' : 'university' }} text-slate-400"></i>
                                {{ strtoupper($payment->payment_method === 'mpesa' ? 'M-PESA' : 'BANK') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button onclick="viewReceipt({{ $payment->id }})" class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-lg">
                                <i class="fas fa-receipt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                            <i class="fas fa-history text-4xl mb-3 block"></i>
                            <p>No payment history found</p>
                            <a href="{{ route('salary.payments.create') }}" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">Create first payment</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(isset($payments) && $payments->count() > 0)
                <tfoot class="bg-slate-50 border-t">
                    <tr class="font-semibold">
                        <td colspan="4" class="px-5 py-3 text-right">Totals:</td>
                        <td class="px-5 py-3 text-right">KES {{ number_format($payments->sum('amount'), 2) }}</td>
                        <td class="px-5 py-3 text-right">KES {{ number_format($payments->sum('deductions_total'), 2) }}</td>
                        <td class="px-5 py-3 text-right text-emerald-600">KES {{ number_format($payments->sum('net_amount'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($payments) && method_exists($payments, 'links'))
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $payments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<script>
let monthlyChart = null;

function paymentHistoryManager() {
    return {
        init() {
            this.loadChart();
        },
        loadChart() {
            this.loadMonthlyData();
        },
        loadMonthlyData() {
            const year = document.getElementById('yearSelect').value;
            fetch(`/salary/history/chart-data?year=${year}`)
                .then(response => response.json())
                .then(data => {
                    if (monthlyChart) {
                        monthlyChart.destroy();
                    }
                    const ctx = document.getElementById('monthlyChart').getContext('2d');
                    monthlyChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.months,
                            datasets: [{
                                label: 'Total Payments (KES)',
                                data: data.amounts,
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                tension: 0.3,
                                fill: true
                            }, {
                                label: 'Number of Transactions',
                                data: data.counts,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3,
                                fill: true,
                                yAxisID: 'y1'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            interaction: { mode: 'index', intersect: false },
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: { title: { display: true, text: 'Amount (KES)' } },
                                y1: { position: 'right', title: { display: true, text: 'Transaction Count' }, grid: { drawOnChartArea: false } }
                            }
                        }
                    });
                });
        },
        exportHistory() {
            window.location.href = '{{ route("salary.history") }}?export=true&' + new URLSearchParams(window.location.search).toString();
        },
        printReport() {
            window.print();
        }
    }
}

function updateChart() {
    const year = document.getElementById('yearSelect').value;
    fetch(`/salary/history/chart-data?year=${year}`)
        .then(response => response.json())
        .then(data => {
            if (monthlyChart) {
                monthlyChart.data.labels = data.months;
                monthlyChart.data.datasets[0].data = data.amounts;
                monthlyChart.data.datasets[1].data = data.counts;
                monthlyChart.update();
            }
        });
}

function viewMonthDetails(month) {
    window.location.href = `{{ route("salary.history") }}?year=${month.split('-')[0]}&month=${month.split('-')[1]}`;
}

function viewReceipt(id) {
    fetch(`/salary/payments/${id}/receipt`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="space-y-4">
                    <div class="text-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-indigo-600">PAYMENT RECEIPT</h2>
                        <p class="text-sm text-slate-500">Transaction Reference: ${data.transaction_reference}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs text-slate-500">Date</label><p class="font-semibold">${new Date(data.payment_date).toLocaleDateString()}</p></div>
                        <div><label class="text-xs text-slate-500">Status</label><p><span class="text-emerald-600 font-semibold">${data.status.toUpperCase()}</span></p></div>
                        <div><label class="text-xs text-slate-500">Employee Name</label><p class="font-medium">${data.employee?.name}</p></div>
                        <div><label class="text-xs text-slate-500">Position</label><p>${data.employee?.position || 'N/A'}</p></div>
                        <div><label class="text-xs text-slate-500">Payment Type</label><p>${data.type.toUpperCase()}</p></div>
                        <div><label class="text-xs text-slate-500">Payment Method</label><p>${data.payment_method === 'mpesa' ? 'M-PESA' : 'BANK TRANSFER'}</p></div>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex justify-between py-2"><span>Gross Amount:</span><span class="font-semibold">KES ${Number(data.amount).toLocaleString()}</span></div>
                        <div class="flex justify-between py-2 text-red-600"><span>Deductions:</span><span>-KES ${Number(data.deductions_total).toLocaleString()}</span></div>
                        <div class="flex justify-between py-2 border-t mt-2 pt-2"><span class="text-lg font-bold">NET PAYMENT:</span><span class="text-2xl font-bold text-emerald-600">KES ${Number(data.net_amount).toLocaleString()}</span></div>
                    </div>
                    ${data.notes ? `<div class="bg-slate-50 p-3 rounded"><label class="text-xs text-slate-500">Notes</label><p class="text-sm">${data.notes}</p></div>` : ''}
                    <div class="text-center text-xs text-slate-400 pt-4">
                        This is a computer-generated receipt. No signature required.
                    </div>
                </div>
            `;
            document.getElementById('receiptContent').innerHTML = content;
            window.currentReceiptData = data;
            document.getElementById('receiptModal').classList.remove('hidden');
        });
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
}

function printReceipt() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head><title>Payment Receipt</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; }
            .receipt { max-width: 600px; margin: 0 auto; }
            .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 20px; }
            .total { font-size: 24px; font-weight: bold; color: #059669; margin: 20px 0; }
            table { width: 100%; }
            td { padding: 8px; }
        </style>
        </head>
        <body>
            <div class="receipt">
                ${document.getElementById('receiptContent').innerHTML}
            </div>
            <script>window.print();<\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

function downloadReceipt() {
    const data = window.currentReceiptData;
    const receipt = document.getElementById('receiptContent').innerHTML;
    const blob = new Blob([receipt], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `receipt_${data.transaction_reference}.html`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>

@push('styles')
<style media="print">
    .no-print, .sidebar, nav, button, .pagination, form, .bg-gradient-to-r {
        display: none !important;
    }
    body {
        background: white;
        padding: 20px;
    }
    .bg-white {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
</style>
@endpush
@endsection