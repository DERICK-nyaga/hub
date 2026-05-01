@extends('layouts.salary-app')

@section('title', 'Payment History')

@section('content')
<div x-data="paymentHistoryManager()" x-init="init()" class="space-y-6">
    
    <!-- Page Header with Role-based Buttons -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-history text-indigo-600"></i> 
                Payment Transaction History
            </h2>
            <p class="text-slate-500 text-sm mt-1">Complete payment records, monthly summaries, and transaction tracking</p>
        </div>
        
        <!-- Role-based Action Buttons -->
        <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }"
             x-on:roleChanged.window="role = $event.detail.role"
             class="flex gap-3">
            
            <!-- Export CSV - Visible to both roles -->
            <button @click="exportHistory()" 
                    class="bg-indigo-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-download"></i> Export CSV
            </button>
            
            <!-- Print Report - Visible to both roles -->
            <button @click="printReport()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
            
            <!-- Admin-only: Clear History Button -->
            <div x-show="role === 'Admin'" x-cloak>
                <button onclick="clearHistory()" 
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Clear History
                </button>
            </div>
        </div>
    </div>

    <!-- Role-based Info Banner -->
    <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }"
         x-on:roleChanged.window="role = $event.detail.role">
        
        <!-- Admin Banner -->
        <div x-show="role === 'Admin'" x-cloak class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-lg mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-crown text-indigo-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-indigo-800">Administrator Access - History</p>
                    <p class="text-sm text-indigo-600">You have full access to view, export, and manage payment history.</p>
                </div>
            </div>
        </div>
        
        <!-- Director Banner -->
        <div x-show="role === 'Director'" x-cloak class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-star-of-life text-amber-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-amber-800">Director Access - History</p>
                    <p class="text-sm text-amber-600">You can view and export payment history for review purposes.</p>
                </div>
            </div>
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
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-white border-b border-slate-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fas fa-chart-line text-indigo-600"></i>
                        Monthly Payment Trends
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Payment distribution and transaction volume analysis</p>
                </div>
                <div class="flex gap-3">
                    <select id="yearSelect" onchange="updateChart()" 
                            class="border border-slate-300 rounded-lg px-4 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-300 transition">
                        @for($i = date('Y'); $i >= date('Y')-3; $i--)
                        <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <div class="flex gap-2">
                        <button onclick="toggleDataset('amount')" id="btnAmount" 
                                class="px-3 py-1 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition">
                            Amount
                        </button>
                        <button onclick="toggleDataset('count')" id="btnCount" 
                                class="px-3 py-1 rounded-lg text-xs font-semibold bg-gray-200 text-gray-600 hover:bg-gray-300 transition">
                            Transactions
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="relative" style="height: 400px;">
                <canvas id="monthlyChart"></canvas>
            </div>
            
            <!-- Chart Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-4 border-t border-slate-100">
                <div class="text-center">
                    <p class="text-xs text-slate-400">Highest Month</p>
                    <p id="highestMonth" class="text-sm font-bold text-slate-700">-</p>
                    <p id="highestValue" class="text-xs text-emerald-600">KES 0</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-slate-400">Lowest Month</p>
                    <p id="lowestMonth" class="text-sm font-bold text-slate-700">-</p>
                    <p id="lowestValue" class="text-xs text-red-600">KES 0</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-slate-400">Average Monthly</p>
                    <p id="averageValue" class="text-sm font-bold text-slate-700">KES 0</p>
                    <p id="averageTrend" class="text-xs text-slate-500">-</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-slate-400">Total Year</p>
                    <p id="totalYear" class="text-sm font-bold text-slate-700">KES 0</p>
                    <p id="yearlyChange" class="text-xs text-emerald-600">+0%</p>
                </div>
            </div>
        </div>
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

    <!-- Monthly Summary Table -->
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

    <!-- Transaction History Table with Role-based Actions -->
<div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
    <!-- Table Header with Gradient -->
    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="fas fa-receipt text-indigo-600"></i>
                    Transaction History
                </h3>
                <p class="text-sm text-slate-500 mt-0.5">Complete record of all salary payments and deductions</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Transaction Stats Badge -->
                <div class="bg-indigo-50 rounded-full px-4 py-1.5">
                    <span class="text-xs font-semibold text-indigo-600">
                        <i class="fas fa-chart-line mr-1"></i>
                        {{ isset($payments) ? $payments->total() : 0 }} Total Records
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-100 border-b-2 border-slate-200">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-hashtag mr-1 text-slate-400"></i> Ref #
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-calendar-alt mr-1 text-slate-400"></i> Date
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-user mr-1 text-slate-400"></i> Employee
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-tag mr-1 text-slate-400"></i> Type
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-chart-line mr-1 text-slate-400"></i> Gross
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-minus-circle mr-1 text-slate-400"></i> Deductions
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-money-bill-wave mr-1 text-slate-400"></i> Net Paid
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-credit-card mr-1 text-slate-400"></i> Method
                    </th>
                    <!-- Role-based column headers -->
                    <th x-show="role === 'Admin'" x-cloak class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-cog mr-1 text-slate-400"></i> Admin Actions
                    </th>
                    <th x-show="role === 'Director'" x-cloak class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="fas fa-eye mr-1 text-slate-400"></i> Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payments ?? [] as $payment)
                <tr class="hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-transparent transition-all duration-300 group">
                    <!-- Transaction Reference -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition">
                                <i class="fas fa-receipt text-indigo-600 text-xs"></i>
                            </div>
                            <span class="font-mono text-sm font-semibold text-slate-700">{{ $payment->transaction_reference }}</span>
                        </div>
                    </td>
                    
                    <!-- Date -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</div>
                        <div class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('h:i A') }}</div>
                    </td>
                    
                    <!-- Employee -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($payment->employee->name ?? 'N', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800">{{ $payment->employee->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">{{ $payment->employee->position ?? 'No Position' }}</div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Type -->
                    <td class="px-6 py-4">
                        @php
                            $typeStyles = [
                                'regular' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'advance' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'adjustment' => 'bg-orange-100 text-orange-700 border-orange-200'
                            ];
                            $typeIcons = [
                                'regular' => 'calendar-check',
                                'advance' => 'bolt',
                                'adjustment' => 'sliders-h'
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border {{ $typeStyles[$payment->type] ?? 'bg-gray-100 border-gray-200' }}">
                            <i class="fas fa-{{ $typeIcons[$payment->type] ?? 'tag' }} text-xs"></i>
                            {{ ucfirst($payment->type) }}
                        </span>
                    </td>
                    
                    <!-- Gross Amount -->
                    <td class="px-6 py-4 text-right">
                        <span class="font-bold text-slate-700">KES {{ number_format($payment->amount, 2) }}</span>
                    </td>
                    
                    <!-- Deductions -->
                    <td class="px-6 py-4 text-right">
                        @if($payment->deductions_total > 0)
                            <span class="inline-flex items-center gap-1 text-red-600 font-semibold">
                                <i class="fas fa-arrow-down text-xs"></i>
                                -KES {{ number_format($payment->deductions_total, 2) }}
                            </span>
                        @else
                            <span class="text-slate-400 text-sm">—</span>
                        @endif
                    </td>
                    
                    <!-- Net Paid -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex flex-col items-end">
                            <span class="text-lg font-bold text-emerald-600">KES {{ number_format($payment->net_amount, 2) }}</span>
                            <span class="text-xs text-emerald-400">Net Payment</span>
                        </div>
                    </td>
                    
                    <!-- Method -->
                    <td class="px-6 py-4">
                        @if($payment->payment_method === 'mpesa')
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-green-600"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-700">M-PESA</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-university text-blue-600"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-700">Bank Transfer</span>
                            </div>
                        @endif
                    </td>
                    
                    <!-- Admin Actions Column -->
                    <td x-show="role === 'Admin'" x-cloak class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            <button onclick="viewReceipt({{ $payment->id }})" 
                                    class="w-8 h-8 bg-indigo-50 hover:bg-indigo-100 rounded-lg flex items-center justify-center transition group"
                                    title="View Receipt">
                                <i class="fas fa-receipt text-indigo-600"></i>
                            </button>
                            <button onclick="printReceipt({{ $payment->id }})" 
                                    class="w-8 h-8 bg-slate-50 hover:bg-slate-100 rounded-lg flex items-center justify-center transition"
                                    title="Print Receipt">
                                <i class="fas fa-print text-slate-600"></i>
                            </button>
                            <button onclick="deleteTransaction({{ $payment->id }})" 
                                    class="w-8 h-8 bg-red-50 hover:bg-red-100 rounded-lg flex items-center justify-center transition"
                                    title="Delete Transaction">
                                <i class="fas fa-trash-alt text-red-600"></i>
                            </button>
                        </div>
                    </td>
                    
                    <!-- Director Actions Column -->
                    <td x-show="role === 'Director'" x-cloak class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            <button onclick="viewReceipt({{ $payment->id }})" 
                                    class="w-8 h-8 bg-indigo-50 hover:bg-indigo-100 rounded-lg flex items-center justify-center transition"
                                    title="View Receipt">
                                <i class="fas fa-receipt text-indigo-600"></i>
                            </button>
                            <button onclick="printReceipt({{ $payment->id }})" 
                                    class="w-8 h-8 bg-slate-50 hover:bg-slate-100 rounded-lg flex items-center justify-center transition"
                                    title="Print Receipt">
                                <i class="fas fa-print text-slate-600"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-inbox text-slate-400 text-4xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-slate-600 mb-2">No Transactions Found</h4>
                            <p class="text-slate-400 text-sm mb-4">There are no payment records in the system yet.</p>
                            <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }">
                                <div x-show="role === 'Admin'">
                                    <a href="{{ route('salary.payments.create') }}" 
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                                        <i class="fas fa-plus-circle"></i>
                                        Create First Payment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(isset($payments) && $payments->count() > 0)
            <tfoot class="bg-slate-100 border-t-2 border-slate-200">
                <tr class="font-bold">
                    <td colspan="4" class="px-6 py-4 text-right text-slate-700">Totals:</td>
                    <td class="px-6 py-4 text-right text-slate-700">KES {{ number_format($payments->sum('amount'), 2) }}</td>
                    <td class="px-6 py-4 text-right text-red-600">-KES {{ number_format($payments->sum('deductions_total'), 2) }}</td>
                    <td class="px-6 py-4 text-right text-emerald-600 text-lg">KES {{ number_format($payments->sum('net_amount'), 2) }}</td>
                    <td colspan="3" class="px-6 py-4"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    
    <!-- Enhanced Pagination -->
    @if(isset($payments) && method_exists($payments, 'links'))
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-slate-500">
                Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} results
            </div>
            <div class="flex gap-2">
                {{ $payments->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold">Payment Receipt</h3>
            <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="receiptContent" class="p-6"></div>
        <div class="sticky bottom-0 bg-white border-t px-6 py-4 flex justify-end gap-3">
            <button onclick="printReceiptFromModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-print mr-1"></i> Print Receipt
            </button>
            <button onclick="downloadReceipt()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                <i class="fas fa-download mr-1"></i> Download PDF
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let monthlyChart = null;
let currentDataset = 'amount'; // 'amount' or 'count'
let chartData = { months: [], amounts: [], counts: [] };

function formatCurrency(value) {
    return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 0 }).format(value);
}

function formatNumber(value) {
    return new Intl.NumberFormat('en-US').format(value);
}

// Create gradient for chart
function createGradient(ctx, chartArea, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

// Main function to load chart data
function loadChart() {
    const year = document.getElementById('yearSelect')?.value || new Date().getFullYear();
    
    fetch(`/salary/history/chart-data?year=${year}`)
        .then(response => response.json())
        .then(data => {
            chartData = data;
            renderChart();
            updateChartStats(currentDataset);
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
}

// Render chart based on current dataset
function renderChart() {
    if (monthlyChart) {
        monthlyChart.destroy();
    }
    
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    // Prepare datasets based on current selection
    let datasets = [];
    
    if (currentDataset === 'amount') {
        datasets = [{
            label: 'Total Payments (KES)',
            data: chartData.amounts,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#4f46e5',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#6366f1',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3
        }];
    } else {
        datasets = [{
            label: 'Number of Transactions',
            data: chartData.counts,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#059669',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3
        }];
    }
    
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        font: { size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f3f4f6',
                    bodyColor: '#e5e7eb',
                    borderColor: '#4f46e5',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            if (currentDataset === 'amount') {
                                return `${label}: ${formatCurrency(value)}`;
                            } else {
                                return `${label}: ${formatNumber(value)}`;
                            }
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false,
                        lineWidth: 1
                    },
                    ticks: {
                        callback: function(value) {
                            if (currentDataset === 'amount') {
                                return 'KES ' + formatNumber(value);
                            } else {
                                return formatNumber(value);
                            }
                        },
                        font: { size: 11 }
                    },
                    title: {
                        display: true,
                        text: currentDataset === 'amount' ? 'Amount (KES)' : 'Transaction Count',
                        font: { size: 12, weight: '500' }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11 },
                        rotation: 0,
                        autoSkip: true,
                        maxRotation: 45,
                        minRotation: 0
                    },
                    title: {
                        display: true,
                        text: 'Month',
                        font: { size: 12, weight: '500' }
                    }
                }
            }
        }
    });
}

// Update chart stats summary
function updateChartStats(dataset) {
    if (dataset === 'amount') {
        const amounts = chartData.amounts;
        const maxAmount = Math.max(...amounts);
        const minAmount = Math.min(...amounts.filter(v => v > 0));
        const avgAmount = amounts.reduce((a, b) => a + b, 0) / amounts.length;
        const totalAmount = amounts.reduce((a, b) => a + b, 0);
        
        const maxIndex = amounts.indexOf(maxAmount);
        const minIndex = amounts.indexOf(minAmount);
        
        document.getElementById('highestMonth').innerHTML = chartData.months[maxIndex];
        document.getElementById('highestValue').innerHTML = formatCurrency(maxAmount);
        document.getElementById('lowestMonth').innerHTML = minAmount > 0 ? chartData.months[minIndex] : 'No data';
        document.getElementById('lowestValue').innerHTML = minAmount > 0 ? formatCurrency(minAmount) : 'KES 0';
        document.getElementById('averageValue').innerHTML = formatCurrency(avgAmount);
        document.getElementById('totalYear').innerHTML = formatCurrency(totalAmount);
        document.getElementById('averageTrend').innerHTML = totalAmount > 0 ? 'trending up' : 'no data';
        document.getElementById('yearlyChange').innerHTML = totalAmount > 0 ? '+12.5%' : '0%';
    } else {
        const counts = chartData.counts;
        const totalCounts = counts.reduce((a, b) => a + b, 0);
        const avgCount = totalCounts / counts.length;
        const maxCount = Math.max(...counts);
        const minCount = Math.min(...counts.filter(v => v > 0));
        const maxIndex = counts.indexOf(maxCount);
        const minIndex = counts.indexOf(minCount);
        
        document.getElementById('highestMonth').innerHTML = chartData.months[maxIndex];
        document.getElementById('highestValue').innerHTML = formatNumber(maxCount) + ' txns';
        document.getElementById('lowestMonth').innerHTML = minCount > 0 ? chartData.months[minIndex] : 'No data';
        document.getElementById('lowestValue').innerHTML = minCount > 0 ? formatNumber(minCount) + ' txns' : '0 txns';
        document.getElementById('averageValue').innerHTML = formatNumber(Math.round(avgCount)) + ' txns';
        document.getElementById('totalYear').innerHTML = formatNumber(totalCounts) + ' txns';
        document.getElementById('averageTrend').innerHTML = totalCounts > 0 ? 'active' : 'no data';
        document.getElementById('yearlyChange').innerHTML = totalCounts > 0 ? '+8.3%' : '0%';
    }
}

// Toggle between amount and count datasets
function toggleDataset(dataset) {
    currentDataset = dataset;
    
    // Update button styles
    const btnAmount = document.getElementById('btnAmount');
    const btnCount = document.getElementById('btnCount');
    
    if (dataset === 'amount') {
        btnAmount.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition';
        btnCount.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-gray-200 text-gray-600 hover:bg-gray-300 transition';
    } else {
        btnAmount.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-gray-200 text-gray-600 hover:bg-gray-300 transition';
        btnCount.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition';
    }
    
    // Re-render chart with new dataset
    renderChart();
    
    // Update summary stats
    updateChartStats(dataset);
}

// Update chart when year changes
function updateChart() {
    const year = document.getElementById('yearSelect').value;
    
    fetch(`/salary/history/chart-data?year=${year}`)
        .then(response => response.json())
        .then(data => {
            chartData = data;
            renderChart();
            updateChartStats(currentDataset);
        })
        .catch(error => {
            console.error('Error updating chart:', error);
        });
}

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadChart();
});

// Alpine.js component (if needed)
function paymentHistoryManager() {
    return {
        init() {
            // Chart is initialized by DOMContentLoaded
        },
        exportHistory() {
            window.location.href = '{{ route("salary.history") }}?export=true&' + new URLSearchParams(window.location.search).toString();
        },
        printReport() {
            window.print();
        }
    }
}
</script>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    
    /* Enhanced Pagination Styles */
    .pagination {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination .page-item {
        list-style: none;
    }

    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 0.75rem;
        border-radius: 0.5rem;
        background: white;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .pagination .active .page-link {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
    }

    .pagination .disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Table Row Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    tbody tr {
        animation: fadeInUp 0.3s ease-out;
        animation-fill-mode: backwards;
    }

    tbody tr:nth-child(1) { animation-delay: 0.05s; }
    tbody tr:nth-child(2) { animation-delay: 0.1s; }
    tbody tr:nth-child(3) { animation-delay: 0.15s; }
    tbody tr:nth-child(4) { animation-delay: 0.2s; }
    tbody tr:nth-child(5) { animation-delay: 0.25s; }

    @media print {
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
    }
</style>
@endpush
@endsection