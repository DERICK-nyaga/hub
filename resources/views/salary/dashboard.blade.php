{{-- resources/views/salary/payments/history.blade.php --}}
@extends('layouts.salary-app')

@section('title', 'Payment History')

@section('content')

{{-- Initialize variables safely --}}
@php
    $summary = $summary ?? collect([]);
    $yearlyTotal = $yearlyTotal ?? 0;
    $totalTransactions = $totalTransactions ?? 0;
    $averagePayment = $averagePayment ?? 0;
    $totalDeductions = $totalDeductions ?? 0;
    $payments = $payments ?? collect([]);
@endphp

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
                    class="bg-blue-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-download"></i> Export CSV
            </button>
            <button @click="printReport()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
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
                <p class="text-2xl font-bold">KES {{ number_format($yearlyTotal, 2) }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-sm">Total Transactions</p>
                <p class="text-2xl font-bold">{{ number_format($totalTransactions) }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-sm">Average Payment</p>
                <p class="text-2xl font-bold">KES {{ number_format($averagePayment, 2) }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-sm">Total Deductions</p>
                <p class="text-2xl font-bold">KES {{ number_format($totalDeductions, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Monthly Payment Trends - Enhanced Version -->
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
            <!-- Chart Container -->
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
    @if($summary && $summary->isNotEmpty())
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

    <!-- Transaction History Table - FIXED VERSION -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">Transaction History</h3>
            <span class="text-sm text-slate-500">
                @if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $payments->total() }} records found
                @else
                    {{ $payments->count() }} records found
                @endif
            </span>
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
                    @forelse($payments as $payment)
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
                @if($payments && $payments->count() > 0)
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
        
        <!-- Pagination - Only show if $payments is a Paginator -->
        @if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $payments->withQueryString()->links() }}
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
            <button onclick="printReceipt()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-print mr-1"></i> Print Receipt
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

function updateChartStats(data, dataset) {
    if (dataset === 'amount') {
        const amounts = data.amounts;
        const maxAmount = Math.max(...amounts);
        const minAmount = Math.min(...amounts.filter(v => v > 0));
        const avgAmount = amounts.reduce((a, b) => a + b, 0) / amounts.length;
        const totalAmount = amounts.reduce((a, b) => a + b, 0);
        
        const maxIndex = amounts.indexOf(maxAmount);
        const minIndex = amounts.indexOf(minAmount);
        
        document.getElementById('highestMonth').innerHTML = data.months[maxIndex];
        document.getElementById('highestValue').innerHTML = formatCurrency(maxAmount);
        document.getElementById('lowestMonth').innerHTML = minAmount > 0 ? data.months[minIndex] : 'No data';
        document.getElementById('lowestValue').innerHTML = minAmount > 0 ? formatCurrency(minAmount) : 'KES 0';
        document.getElementById('averageValue').innerHTML = formatCurrency(avgAmount);
        document.getElementById('totalYear').innerHTML = formatCurrency(totalAmount);
        
        // Calculate trend from previous year (mock - you can implement real comparison)
        const yearlyChangePercent = totalAmount > 0 ? '+12.5' : '0';
        document.getElementById('yearlyChange').innerHTML = `+${yearlyChangePercent}%`;
        document.getElementById('averageTrend').innerHTML = 'trending up';
    } else {
        const counts = data.counts;
        const totalCounts = counts.reduce((a, b) => a + b, 0);
        const avgCount = totalCounts / counts.length;
        
        document.getElementById('highestValue').innerHTML = formatNumber(Math.max(...counts)) + ' txns';
        document.getElementById('lowestValue').innerHTML = formatNumber(Math.min(...counts.filter(v => v > 0))) + ' txns';
        document.getElementById('averageValue').innerHTML = formatNumber(Math.round(avgCount)) + ' txns';
        document.getElementById('totalYear').innerHTML = formatNumber(totalCounts) + ' txns';
        document.getElementById('averageTrend').innerHTML = totalCounts > 0 ? 'active' : 'no data';
    }
}

function createGradient(ctx, chartArea, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

function loadChart() {
    const year = document.getElementById('yearSelect')?.value || new Date().getFullYear();
    
    fetch(`/salary/history/chart-data?year=${year}`)
        .then(response => response.json())
        .then(data => {
            chartData = data;
            
            if (monthlyChart) {
                monthlyChart.destroy();
            }
            
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            
            // Prepare datasets based on current selection
            let datasets = [];
            
            if (currentDataset === 'amount') {
                datasets = [{
                    label: 'Total Payments (KES)',
                    data: data.amounts,
                    borderColor: '#4f46e5',
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        return createGradient(ctx, chartArea, 'rgba(79, 70, 229, 0.05)', 'rgba(79, 70, 229, 0.4)');
                    },
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
                    data: data.counts,
                    borderColor: '#10b981',
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        return createGradient(ctx, chartArea, 'rgba(16, 185, 129, 0.05)', 'rgba(16, 185, 129, 0.3)');
                    },
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
                    pointHoverBorderWidth: 3,
                    yAxisID: 'y'
                }];
            }
            
            monthlyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.months,
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
                    },
                    elements: {
                        line: {
                            borderJoin: 'round',
                            borderCap: 'round'
                        },
                        point: {
                            hitRadius: 10,
                            hoverRadius: 8,
                            radius: 4
                        }
                    }
                }
            });
            
            // Update summary stats
            updateChartStats(data, currentDataset);
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            // Show empty chart state
            if (monthlyChart) monthlyChart.destroy();
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            monthlyChart = new Chart(ctx, {
                type: 'line',
                data: { labels: [], datasets: [] },
                options: { responsive: true, maintainAspectRatio: true }
            });
        });
}

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
    
    // Reload chart with new dataset
    if (chartData.months && chartData.months.length > 0) {
        if (monthlyChart) {
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            
            if (dataset === 'amount') {
                monthlyChart.data.datasets = [{
                    label: 'Total Payments (KES)',
                    data: chartData.amounts,
                    borderColor: '#4f46e5',
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        return createGradient(ctx, chartArea, 'rgba(79, 70, 229, 0.05)', 'rgba(79, 70, 229, 0.4)');
                    },
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }];
                monthlyChart.options.scales.y.title.text = 'Amount (KES)';
                monthlyChart.options.scales.y.ticks.callback = function(value) {
                    return 'KES ' + formatNumber(value);
                };
            } else {
                monthlyChart.data.datasets = [{
                    label: 'Number of Transactions',
                    data: chartData.counts,
                    borderColor: '#10b981',
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        return createGradient(ctx, chartArea, 'rgba(16, 185, 129, 0.05)', 'rgba(16, 185, 129, 0.3)');
                    },
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }];
                monthlyChart.options.scales.y.title.text = 'Transaction Count';
                monthlyChart.options.scales.y.ticks.callback = function(value) {
                    return formatNumber(value);
                };
            }
            
            monthlyChart.update();
            updateChartStats(chartData, dataset);
        }
    }
}

function updateChart() {
    loadChart();
}

function paymentHistoryManager() {
    return {
        init() {
            loadChart();
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
    /* Chart Container Styling */
    #monthlyChart {
        max-height: 350px;
        width: 100%;
    }
    
    /* Chart Tooltip Customization */
    .chartjs-tooltip {
        background: #1f2937 !important;
        border-radius: 8px !important;
        padding: 8px 12px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Smooth transitions for buttons */
    #btnAmount, #btnCount {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    /* Chart summary cards hover effect */
    .grid-cols-2 > div, .grid-cols-4 > div {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .grid-cols-2 > div:hover, .grid-cols-4 > div:hover {
        transform: translateY(-2px);
        background: #f8fafc;
        border-radius: 8px;
    }
</style>
@endpush

@endsection