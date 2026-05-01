<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salary Management System - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        @vite([
        'resources/css/app.css',
        'resources/css/salary-app.css',
        'resources/js/app.js',
        'resources/js/salary-app.js'
    ])

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('roleManager', () => ({
            currentRole: 'Admin',
            toggleRoleText: 'Director',
            isRotated: false,
            
            initRole() {
                const savedRole = localStorage.getItem('user_role');
                if (savedRole && (savedRole === 'Admin' || savedRole === 'Director')) {
                    this.currentRole = savedRole;
                    this.updateToggleText();
                }
                if (typeof window.renderQuickActions !== 'undefined') {
                    window.renderQuickActions(this.currentRole);
                }
                this.dispatchRoleEvent();
            },
            
            toggleRole() {
                this.isRotated = true;
                setTimeout(() => { this.isRotated = false; }, 300);
                this.currentRole = this.currentRole === 'Admin' ? 'Director' : 'Admin';
                this.updateToggleText();
                localStorage.setItem('user_role', this.currentRole);
                
                // Update quick actions instantly
                if (typeof window.renderQuickActions !== 'undefined') {
                    window.renderQuickActions(this.currentRole);
                }
                
                this.showNotification();
                this.dispatchRoleEvent();
                
                // Force DOM update for any x-show elements
                this.$nextTick(() => {
                    console.log(`Role switched to: ${this.currentRole}`);
                });
            },
            
            updateToggleText() { 
                this.toggleRoleText = this.currentRole === 'Admin' ? 'Director' : 'Admin'; 
            },
            
            dispatchRoleEvent() { 
                window.dispatchEvent(new CustomEvent('roleChanged', { detail: { role: this.currentRole } })); 
            },
            
            showNotification() {
                const notification = document.createElement('div');
                notification.className = `fixed bottom-4 right-4 px-5 py-3 rounded-xl shadow-lg z-50 animate-slide-in ${
                    this.currentRole === 'Admin' ? 'bg-emerald-500' : 'bg-amber-500'
                } text-white`;
                notification.innerHTML = `
                    <div class="flex items-center gap-3">
                        <i class="fas fa-${this.currentRole === 'Admin' ? 'crown' : 'star-of-life'} text-lg"></i>
                        <div>
                            <p class="font-semibold">Role Changed</p>
                            <p class="text-sm opacity-90">Switched to ${this.currentRole} mode</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(notification);
                setTimeout(() => { 
                    notification.style.opacity = '0'; 
                    notification.style.transition = 'opacity 0.3s'; 
                    setTimeout(() => notification.remove(), 300); 
                }, 2500);
            }
        }));
    });
</script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
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
                <!-- Main Dashboard Link - Your main application dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group">
                    <i class="fas fa-tachometer-alt w-5 group-hover:scale-110 transition-transform"></i>
                    <span class="ml-3">Main Dashboard</span>
                </a>
                
                <!-- Salary Dashboard Link -->
                <a href="{{ route('salary.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition-all duration-200 group {{ request()->routeIs('salary.dashboard') ? 'bg-indigo-800 shadow-lg' : '' }}">
                    <i class="fas fa-chart-line w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Salary Dashboard</span>
                </a>
                
                <!-- Divider -->
                <div class="border-t border-indigo-800 my-2"></div>
                
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
                <!-- Return to Main Dashboard button -->
                <a href="{{ route('dashboard') }}" class="mt-3 flex items-center justify-center gap-2 bg-indigo-700 hover:bg-indigo-600 rounded-lg px-3 py-2 text-sm transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Main</span>
                </a>
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
                        <!-- Quick link to Main Dashboard -->
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            <i class="fas fa-home"></i>
                            <span>Main Dashboard</span>
                        </a>
                        
                        <div class="w-px h-6 bg-gray-300"></div>
                        
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
                
<div x-on:roleChanged.window="refreshQuickActions()" 
     class="role-transition">
    
    <!-- Dynamic Banner - Uses currentRole from parent x-data -->
    <div class="mb-6 rounded-xl p-5 text-white shadow-lg transition-all duration-300 fade-in"
         :class="{
             'bg-gradient-to-r from-purple-600 to-teal-600': currentRole === 'Admin',
             'bg-gradient-to-r from-purple-600 to-orange-600': currentRole === 'Director'
         }">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <!-- Animated Icon Container -->
                <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg"
                     :class="{
                         'animate-pulse': currentRole === 'Admin'
                     }">
                    <i class="text-2xl" :class="{
                        'fas fa-crown': currentRole === 'Admin',
                        'fas fa-star-of-life': currentRole === 'Director'
                    }"></i>
                </div>
                
                <div>
                    <!-- Role Title -->
                    <p class="text-2xl font-bold tracking-tight" 
                       x-text="currentRole === 'Admin' ? 'Administrator Mode' : 'Director Mode'"></p>
                    
                    <!-- Role Description -->
                    <p class="text-sm opacity-90 mt-1 max-w-md" 
                       x-text="currentRole === 'Admin' ? 
                               'You have full access to all features including approvals, edits, and system configuration.' : 
                               'You can review and approve payments, monitor reports, and view all transactions.'">
                    </p>
                    
                    <!-- Role-specific Stats -->
                    <div class="mt-3 flex flex-wrap gap-3">
                        <!-- Admin badges -->
                        <div x-show="currentRole === 'Admin'" class="flex items-center gap-2 text-xs bg-white/20 rounded-full px-3 py-1">
                            <i class="fas fa-check-circle"></i>
                            <span>Create & Edit</span>
                        </div>
                        <div x-show="currentRole === 'Admin'" class="flex items-center gap-2 text-xs bg-white/20 rounded-full px-3 py-1">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Records</span>
                        </div>
                        <div x-show="currentRole === 'Admin'" class="flex items-center gap-2 text-xs bg-white/20 rounded-full px-3 py-1">
                            <i class="fas fa-user-plus"></i>
                            <span>Manage Users</span>
                        </div>
                        
                        <!-- Director badges -->
                        <div x-show="currentRole === 'Director'" class="flex items-center gap-2 text-xs bg-white/20 rounded-full px-3 py-1">
                            <i class="fas fa-check-double"></i>
                            <span>Approve Payments</span>
                        </div>
                        <div x-show="currentRole === 'Director'" class="flex items-center gap-2 text-xs bg-white/20 rounded-full px-3 py-1">
                            <i class="fas fa-chart-line"></i>
                            <span>View Reports</span>
                        </div>
                        <div x-show="currentRole === 'Director'" class="flex items-center gap-2 text-xs bg-white/20 rounded-full px-3 py-1">
                            <i class="fas fa-eye"></i>
                            <span>Read-only Access</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Role Switch Indicator -->
            <div class="hidden md:block">
                <div class="text-right">
                    <p class="text-xs opacity-75">Current Session</p>
                    <p class="text-sm font-mono bg-white/20 rounded-lg px-3 py-1 mt-1" 
                       x-text="currentRole === 'Admin' ? 'full_admin_access' : 'director_review_only'">
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Total Payments Card -->
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
                <span x-show="currentRole === 'Admin'" class="text-purple-600"><i class="fas fa-edit"></i> Full access</span>
                <span x-show="currentRole === 'Director'" class="text-indigo-600"><i class="fas fa-eye"></i> View only</span>
            </div>
        </div>
        
        <!-- Pending Approvals Card -->
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
                <button onclick="window.showApprovalModal()" class="text-xs text-purple-600 hover:text-purple-800">
                    <i class="fas fa-check-double"></i> Review all pending
                </button>
            </div>
        </div>
        
        <!-- Total Deductions Card -->
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
                <span x-show="currentRole === 'Admin'" class="text-purple-600"><i class="fas fa-plus-circle"></i> Add/Manage deductions</span>
                <span x-show="currentRole === 'Director'" class="text-indigo-600"><i class="fas fa-chart-line"></i> View deduction reports</span>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div id="quickActionsCard" class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl shadow-sm p-5 text-white transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-200 text-sm">Quick Actions</p>
                    <p class="text-2xl font-bold" x-text="currentRole === 'Admin' ? 'Admin Tools' : 'Director Tools'"></p>
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
    
<script>
    // Ensure Alpine components are registered
    document.addEventListener('alpine:init', () => {
        console.log('Alpine initialized');
    });
</script>
</body>
</html>