<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salary Management System - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .transition-fast { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        .role-transition {
            transition: all 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-20px); }
        }
        .fade-out {
            animation: fadeOut 0.3s ease-out forwards;
        }
        
        /* Modal styles */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        tbody tr {
            animation: fadeInUp 0.3s ease-out;
            animation-fill-mode: backwards;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="roleManager()" x-init="initRole()">
    
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-900 text-white flex flex-col shadow-xl">
            <div class="p-6 border-b border-indigo-800">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-coins"></i>
                    <span>Salary Pro</span>
                </h2>
                <p class="text-indigo-300 text-sm mt-1">Payment Management</p>
            </div>
            
            <div class="mx-4 mt-4 mb-2 bg-indigo-800/50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-shield text-amber-400 text-sm"></i>
                        <span class="text-indigo-200 text-xs">Current Role:</span>
                    </div>
                    <span class="text-white font-bold text-sm bg-indigo-700 px-3 py-1 rounded-full" x-text="currentRole"></span>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2 mt-2">
                <a href="{{ route('salary.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group {{ request()->routeIs('salary.dashboard') ? 'bg-indigo-800 shadow-lg' : '' }}">
                    <i class="fas fa-chart-line w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Dashboard</span>
                </a>
                <a href="{{ route('salary.payments.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group {{ request()->routeIs('salary.payments.*') ? 'bg-indigo-800 shadow-lg' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Payments</span>
                </a>
                <a href="{{ route('salary.deductions.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group {{ request()->routeIs('salary.deductions.*') ? 'bg-indigo-800 shadow-lg' : '' }}">
                    <i class="fas fa-minus-circle w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Deductions</span>
                </a>
                <a href="{{ route('salary.schedules.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group {{ request()->routeIs('salary.schedules.*') ? 'bg-indigo-800 shadow-lg' : '' }}">
                    <i class="fas fa-calendar-alt w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Schedules</span>
                </a>
                <a href="{{ route('salary.history') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group {{ request()->routeIs('salary.history') ? 'bg-indigo-800 shadow-lg' : '' }}">
                    <i class="fas fa-history w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Transaction History</span>
                </a>
            </nav>
            
            <div class="p-4 border-t border-indigo-800 mt-auto">
                <div class="text-indigo-300 text-xs text-center">
                    <i class="fas fa-shield-alt mr-1"></i> Secure System
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
                <div class="px-8 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-chart-pie text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-slate-800">@yield('title', 'Dashboard')</h1>
                            <p class="text-xs text-slate-400">Salary Management System</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3 bg-gray-100 rounded-full px-4 py-2 shadow-inner">
                            <i class="fas fa-user-tag text-indigo-600 text-sm"></i>
                            <span class="text-gray-600 text-sm font-medium">Role:</span>
                            <span class="font-bold text-indigo-600 text-sm" x-text="currentRole"></span>
                        </div>
                        
                        <button @click="toggleRole()" 
                                class="relative inline-flex items-center gap-3 bg-gradient-to-r from-indigo-500 to-indigo-600 
                                       hover:from-indigo-600 hover:to-indigo-700 text-white font-medium 
                                       rounded-full px-5 py-2 transition-all duration-300 shadow-md hover:shadow-lg group">
                            <i class="fas fa-exchange-alt text-sm group-hover:rotate-180 transition-transform duration-300" :class="{'rotate-180': isRotated}"></i>
                            <span class="text-sm">Switch to</span>
                            <span class="font-bold text-sm bg-white/20 px-3 py-1 rounded-full" x-text="toggleRoleText"></span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-4 bg-indigo-100 border-l-4 border-indigo-500 text-indigo-700 p-4 rounded-lg shadow-sm" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-indigo-600"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-red-600"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                <div x-data="{ role: currentRole }" 
                     x-on:roleChanged.window="role = $event.detail.role; refreshQuickActions()"
                     class="role-transition">
                    
                    <!-- Dynamic Banner -->
                    <div class="mb-6 rounded-xl p-4 text-white shadow-lg transition-all duration-300 fade-in"
                         :class="{
                             'bg-gradient-to-r from-purple-600 to-indigo-600': role === 'Admin',
                             'bg-gradient-to-r from-indigo-600 to-purple-600': role === 'Director'
                         }">
                        <div class="flex items-center gap-3">
                            <i class="text-2xl" :class="{
                                'fas fa-crown': role === 'Admin',
                                'fas fa-star-of-life': role === 'Director'
                            }"></i>
                            <div>
                                <p class="font-bold" x-text="role === 'Admin' ? 'Administrator Mode' : 'Director Mode'"></p>
                                <p class="text-sm opacity-90" 
                                   x-text="role === 'Admin' ? 
                                           'You have full access to all features including approvals, edits, and system configuration.' : 
                                           'You can review and approve payments, monitor reports, and view all transactions.'">
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                        <div class="bg-white rounded-xl shadow-sm p-5 card-hover transition-fast">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Total Payments</p>
                                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalPayments ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-indigo-100 p-3 rounded-full">
                                    <i class="fas fa-money-bill-wave text-indigo-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="mt-2 text-xs">
                                <span x-show="role === 'Admin'" class="text-purple-600"><i class="fas fa-edit"></i> Full access</span>
                                <span x-show="role === 'Director'" class="text-indigo-600"><i class="fas fa-eye"></i> View only</span>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-5 card-hover transition-fast">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Pending Approvals</p>
                                    <p id="pendingCount" class="text-2xl font-bold text-amber-600">{{ $pendingApprovals ?? 0 }}</p>
                                </div>
                                <div class="bg-amber-100 p-3 rounded-full">
                                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button onclick="showApprovalModal()" class="text-xs text-purple-600 hover:text-purple-800">
                                    <i class="fas fa-check-double"></i> Review all pending
                                </button>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-5 card-hover transition-fast">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Total Deductions</p>
                                    <p class="text-2xl font-bold text-red-600">KES {{ number_format($totalDeductions ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-red-100 p-3 rounded-full">
                                    <i class="fas fa-minus-circle text-red-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="mt-2 text-xs">
                                <span x-show="role === 'Admin'" class="text-purple-600"><i class="fas fa-plus-circle"></i> Add/Manage deductions</span>
                                <span x-show="role === 'Director'" class="text-indigo-600"><i class="fas fa-chart-line"></i> View deduction reports</span>
                            </div>
                        </div>
                        
                        <div id="quickActionsCard" class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl shadow-sm p-5 text-white transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-200 text-sm">Quick Actions</p>
                                    <p class="text-2xl font-bold" x-text="role === 'Admin' ? 'Admin Tools' : 'Director Tools'"></p>
                                </div>
                                <div class="bg-white/20 p-3 rounded-full">
                                    <i class="fas fa-bolt text-white text-xl"></i>
                                </div>
                            </div>
                            <div id="quickActionsContent" class="mt-3 space-y-2"></div>
                        </div>
                    </div>
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Approval Modal -->
    <div id="approvalModal" class="fixed inset-0 z-50 hidden modal-overlay flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden shadow-2xl">
            <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Pending Approvals</h3>
                <button onclick="closeApprovalModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6" style="max-height: calc(80vh - 70px);">
                <div id="approvalListContent" class="space-y-3">
                    <!-- Pending items will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Metrics Modal -->
    <div id="metricsModal" class="fixed inset-0 z-50 hidden modal-overlay flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden shadow-2xl">
            <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Performance Metrics Dashboard</h3>
                <button onclick="closeMetricsModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6" style="max-height: calc(80vh - 70px);">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-purple-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-purple-600">Average Payment Processing Time</p>
                                <p class="text-2xl font-bold text-purple-700">2.4 hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600">Approval Rate</p>
                                <p class="text-2xl font-bold text-indigo-700">94.5%</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-purple-600">Active Employees</p>
                                <p class="text-2xl font-bold text-purple-700">127</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600">Pending Items</p>
                                <p id="metricsPendingCount" class="text-2xl font-bold text-indigo-700">{{ $pendingApprovals ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Monthly Performance Trend</h4>
                    <canvas id="performanceChart" height="200"></canvas>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Top Performing Departments</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center"><span>Finance</span><div class="flex-1 mx-4 bg-gray-200 rounded-full h-2"><div class="bg-purple-600 h-2 rounded-full" style="width: 95%"></div></div><span class="text-sm font-semibold">95%</span></div>
                        <div class="flex justify-between items-center"><span>Sales</span><div class="flex-1 mx-4 bg-gray-200 rounded-full h-2"><div class="bg-indigo-600 h-2 rounded-full" style="width: 88%"></div></div><span class="text-sm font-semibold">88%</span></div>
                        <div class="flex justify-between items-center"><span>IT</span><div class="flex-1 mx-4 bg-gray-200 rounded-full h-2"><div class="bg-purple-600 h-2 rounded-full" style="width: 92%"></div></div><span class="text-sm font-semibold">92%</span></div>
                        <div class="flex justify-between items-center"><span>HR</span><div class="flex-1 mx-4 bg-gray-200 rounded-full h-2"><div class="bg-indigo-600 h-2 rounded-full" style="width: 85%"></div></div><span class="text-sm font-semibold">85%</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
    
    <script>
        
    let pendingApprovals = [];

async function fetchPendingApprovals() {
    try {
        const response = await fetch('/salary/pending-approvals', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        console.log('Fetched pending approvals:', data); // Debug log
        pendingApprovals = data;
        renderApprovalList();
        updatePendingCount();
    } catch (error) {
        console.error('Error fetching pending approvals:', error);
        pendingApprovals = [];
        renderApprovalList();
    }
}

function renderApprovalList() {
    const container = document.getElementById('approvalListContent');
    if (!container) return;
    
    if (!pendingApprovals || pendingApprovals.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <p class="text-gray-500">No pending approvals</p>
                <p class="text-xs text-gray-400">All requests have been processed</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = pendingApprovals.map(item => `
        <div id="approval-item-${item.id}" class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg transition-all duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-gray-800">${item.type} Request #${item.reference}</p>
                    <p class="text-sm text-gray-600">Employee: ${item.employee_name}</p>
                    <p class="text-sm text-gray-600">Amount: KES ${Number(item.amount).toLocaleString()} ${item.reason ? `(${item.reason})` : ''}</p>
                    <p class="text-xs text-gray-500 mt-1">Submitted: ${new Date(item.created_at).toLocaleDateString()}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="approveRequest(${item.id}, '${item.type}')" class="px-3 py-1 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                    <button onclick="rejectRequest(${item.id}, '${item.type}')" class="px-3 py-1 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

async function approveRequest(id, type) {
    const itemElement = document.getElementById(`approval-item-${id}`);
    if (itemElement) {
        itemElement.classList.add('fade-out');
        
        try {
            let endpoint = '';
            if (type === 'Payment') {
                endpoint = `/salary/payments/${id}/approve`;
            } else if (type === 'Deduction') {
                endpoint = `/salary/deductions/${id}/approve`;
            } else if (type === 'Schedule') {
                endpoint = `/salary/schedules/${id}/approve`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                setTimeout(() => {
                    // Refresh the pending approvals list
                    fetchPendingApprovals();
                    // Refresh the dashboard stats
                    refreshDashboardStats();
                    showToast('Request approved successfully!', 'success');
                    
                    if (pendingApprovals.length === 1) {
                        setTimeout(() => closeApprovalModal(), 1500);
                    }
                }, 300);
            } else {
                showToast('Error: ' + (data.message || 'Failed to approve'), 'error');
                itemElement.classList.remove('fade-out');
            }
        } catch (error) {
            console.error('Error approving request:', error);
            showToast('Network error. Please try again.', 'error');
            itemElement.classList.remove('fade-out');
        }
    }
}

async function rejectRequest(id, type) {
    const itemElement = document.getElementById(`approval-item-${id}`);
    if (itemElement) {
        itemElement.classList.add('fade-out');
        
        try {
            let endpoint = '';
            if (type === 'Payment') {
                endpoint = `/salary/payments/${id}/reject`;
            } else if (type === 'Deduction') {
                endpoint = `/salary/deductions/${id}/reject`;
            } else if (type === 'Schedule') {
                endpoint = `/salary/schedules/${id}/reject`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                setTimeout(() => {
                    fetchPendingApprovals();
                    refreshDashboardStats();
                    showToast('Request rejected!', 'error');
                    
                    if (pendingApprovals.length === 1) {
                        setTimeout(() => closeApprovalModal(), 1500);
                    }
                }, 300);
            } else {
                showToast('Error: ' + (data.message || 'Failed to reject'), 'error');
                itemElement.classList.remove('fade-out');
            }
        } catch (error) {
            console.error('Error rejecting request:', error);
            showToast('Network error. Please try again.', 'error');
            itemElement.classList.remove('fade-out');
        }
    }
}

async function refreshDashboardStats() {
    try {
        const response = await fetch('/salary/pending-count', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await response.json();
        
        const pendingCountElement = document.getElementById('pendingCount');
        if (pendingCountElement) {
            pendingCountElement.textContent = data.pending_count || 0;
        }
        
        // Also refresh the payments table if we're on that page
        if (typeof location !== 'undefined' && window.location.pathname.includes('/salary/payments')) {
            // Optional: reload the page or refresh the table via AJAX
            setTimeout(() => location.reload(), 500);
        }
    } catch (error) {
        console.error('Error refreshing stats:', error);
    }
}

function showApprovalModal() {
    fetchPendingApprovals(); // Fetch fresh data
    const modal = document.getElementById('approvalModal');
    if (modal) modal.classList.remove('hidden');
}

function closeApprovalModal() {
    const modal = document.getElementById('approvalModal');
    if (modal) modal.classList.add('hidden');
}

    function renderApprovalList() {
        const container = document.getElementById('approvalListContent');
        if (!container) return;
        
        if (pendingApprovals.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No pending approvals</p>
                    <p class="text-xs text-gray-400">All requests have been processed</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = pendingApprovals.map(item => `
            <div id="approval-item-${item.id}" class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-800">${item.type} Request #${item.reference}</p>
                        <p class="text-sm text-gray-600">Employee: ${item.employee}</p>
                        <p class="text-sm text-gray-600">Amount: KES ${item.amount.toLocaleString()} ${item.reason ? `(${item.reason})` : ''}</p>
                        <p class="text-xs text-gray-500 mt-1">Submitted: ${new Date(item.date).toLocaleDateString()}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="approveRequest(${item.id})" class="px-3 py-1 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                            <i class="fas fa-check mr-1"></i> Approve
                        </button>
                        <button onclick="rejectRequest(${item.id})" class="px-3 py-1 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                            <i class="fas fa-times mr-1"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function updatePendingCount() {
        const pendingCountElement = document.getElementById('pendingCount');
        const metricsPendingCount = document.getElementById('metricsPendingCount');
        if (pendingCountElement) {
            pendingCountElement.textContent = pendingApprovals.length;
        }
        if (metricsPendingCount) {
            metricsPendingCount.textContent = pendingApprovals.length;
        }
    }
    
    function approveRequest(id) {
        const itemElement = document.getElementById(`approval-item-${id}`);
        if (itemElement) {
            // Add fade-out animation
            itemElement.classList.add('fade-out');
            setTimeout(() => {
                // Remove from array
                pendingApprovals = pendingApprovals.filter(item => item.id !== id);
                // Re-render the list
                renderApprovalList();
                // Update pending count badges
                updatePendingCount();
                // Show success notification
                showToast('Request approved successfully!', 'success');
                
                // If no items left, close modal after 1 second
                if (pendingApprovals.length === 0) {
                    setTimeout(() => {
                        closeApprovalModal();
                    }, 1500);
                }
            }, 300);
        }
    }
    
    function rejectRequest(id) {
        const itemElement = document.getElementById(`approval-item-${id}`);
        if (itemElement) {
            itemElement.classList.add('fade-out');
            setTimeout(() => {
                pendingApprovals = pendingApprovals.filter(item => item.id !== id);
                renderApprovalList();
                updatePendingCount();
                showToast('Request rejected!', 'error');
                
                if (pendingApprovals.length === 0) {
                    setTimeout(() => {
                        closeApprovalModal();
                    }, 1500);
                }
            }, 300);
        }
    }
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-20 right-4 px-5 py-3 rounded-xl shadow-lg z-50 animate-slide-in ${
            type === 'success' ? 'bg-purple-600' : 'bg-red-600'
        } text-white`;
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'} text-lg"></i>
                <div>
                    <p class="font-semibold">${type === 'success' ? 'Approved' : 'Rejected'}</p>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Quick Actions Configuration
    const adminActions = [
        { icon: 'fa-plus-circle', text: 'Create New Payment', onclick: "window.location.href='{{ route('salary.payments.create') }}'", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-minus-circle', text: 'Add Deduction', onclick: "window.location.href='{{ route('salary.deductions.index') }}'", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-calendar-plus', text: 'Schedule Payment', onclick: "window.location.href='{{ route('salary.schedules.index') }}'", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-chart-line', text: 'View Analytics', onclick: "showAnalytics()", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-database', text: 'System Backup', onclick: "showBackup()", color: 'bg-white/10 hover:bg-white/20' }
    ];
    
    const directorActions = [
        { icon: 'fa-chart-line', text: 'View Reports', onclick: "window.location.href='{{ route('salary.history') }}'", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-check-circle', text: 'Review Approvals', onclick: "showApprovalModal()", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-clock', text: 'Pending Payments', onclick: "window.location.href='{{ route('salary.payments.index') }}?status=pending_approval'", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-download', text: 'Export Reports', onclick: "exportReports()", color: 'bg-white/10 hover:bg-white/20' },
        { icon: 'fa-chart-pie', text: 'Performance Metrics', onclick: "showMetrics()", color: 'bg-white/10 hover:bg-white/20' }
    ];
    
    function renderQuickActions(role) {
        const container = document.getElementById('quickActionsContent');
        if (!container) return;
        const actions = role === 'Admin' ? adminActions : directorActions;
        container.innerHTML = actions.map(action => `
            <button onclick="${action.onclick}" class="w-full text-left text-xs ${action.color} rounded-lg px-2 py-1.5 transition flex items-center gap-2">
                <i class="fas ${action.icon}"></i><span>${action.text}</span>
            </button>
        `).join('');
        container.classList.add('fade-in');
        setTimeout(() => container.classList.remove('fade-in'), 300);
    }
    
    function refreshQuickActions() {
        const role = localStorage.getItem('user_role') || 'Admin';
        renderQuickActions(role);
    }
    
    function showApprovalModal() {
        renderApprovalList();
        const modal = document.getElementById('approvalModal');
        if (modal) modal.classList.remove('hidden');
    }
    
    // function closeApprovalModal() {
    //     const modal = document.getElementById('approvalModal');
    //     if (modal) modal.classList.add('hidden');
    // }
    
    function showMetrics() {
        const modal = document.getElementById('metricsModal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                const ctx = document.getElementById('performanceChart');
                if (ctx && typeof Chart !== 'undefined') {
                    if (window.performanceChartInstance) window.performanceChartInstance.destroy();
                    window.performanceChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            datasets: [{
                                label: 'Payments Processed (KES)',
                                data: [45000, 52000, 48000, 61000, 58000, 75000],
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: true }
                    });
                }
            }, 100);
        }
    }
    
    function closeMetricsModal() {
        const modal = document.getElementById('metricsModal');
        if (modal) modal.classList.add('hidden');
    }
    
    function showAnalytics() { alert('Analytics dashboard with detailed payment statistics.'); }
    function showBackup() { alert('System backup initiated.'); }
    function exportReports() { alert('Exporting reports. Download will start shortly.'); }
    
    function roleManager() {
        return {
            currentRole: 'Admin',
            toggleRoleText: 'Director',
            isRotated: false,
            initRole() {
                const savedRole = localStorage.getItem('user_role');
                if (savedRole && (savedRole === 'Admin' || savedRole === 'Director')) {
                    this.currentRole = savedRole;
                    this.updateToggleText();
                }
                renderQuickActions(this.currentRole);
                this.dispatchRoleEvent();
            },
            toggleRole() {
                this.isRotated = true;
                setTimeout(() => { this.isRotated = false; }, 300);
                this.currentRole = this.currentRole === 'Admin' ? 'Director' : 'Admin';
                this.updateToggleText();
                localStorage.setItem('user_role', this.currentRole);
                renderQuickActions(this.currentRole);
                this.showNotification();
                this.dispatchRoleEvent();
            },
            updateToggleText() { this.toggleRoleText = this.currentRole === 'Admin' ? 'Director' : 'Admin'; },
            dispatchRoleEvent() { window.dispatchEvent(new CustomEvent('roleChanged', { detail: { role: this.currentRole } })); },
            showNotification() {
                const notification = document.createElement('div');
                notification.className = `fixed bottom-4 right-4 px-5 py-3 rounded-xl shadow-lg z-50 animate-slide-in ${this.currentRole === 'Admin' ? 'bg-purple-600' : 'bg-indigo-600'} text-white`;
                notification.innerHTML = `<div class="flex items-center gap-3"><i class="fas fa-${this.currentRole === 'Admin' ? 'crown' : 'star-of-life'} text-lg"></i><div><p class="font-semibold">Role Changed</p><p class="text-sm opacity-90">Switched to ${this.currentRole} mode</p></div></div>`;
                document.body.appendChild(notification);
                setTimeout(() => { notification.style.opacity = '0'; notification.style.transition = 'opacity 0.3s'; setTimeout(() => notification.remove(), 300); }, 3000);
            }
        }
    }
    
    window.refreshQuickActions = refreshQuickActions;
    window.showApprovalModal = showApprovalModal;
    window.closeApprovalModal = closeApprovalModal;
    window.showMetrics = showMetrics;
    window.closeMetricsModal = closeMetricsModal;
    window.approveRequest = approveRequest;
    window.rejectRequest = rejectRequest;
    window.showAnalytics = showAnalytics;
    window.showBackup = showBackup;
    window.exportReports = exportReports;
    </script>
</body>
</html>