extract css and js but maintain functionality
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

    <!-- Salary-specific styles with unique prefix to avoid conflicts -->
    <style>
        /* ========================================
           Salary Management System Specific Styles
           All classes prefixed with 'salary-' to avoid conflicts
           ======================================== */
        
        /* Role Banner Base */
        .salary-role-banner {
            position: relative;
            isolation: isolate;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border-radius: 1rem;
        }
        
        .salary-role-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 100% 0%, rgba(255,255,255,0.15) 0%, transparent 50%);
            z-index: -1;
        }
        
        /* Administrator Banner */
        .salary-role-banner-admin {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
        }
        
        .salary-role-banner-admin::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
            pointer-events: none;
            z-index: -1;
        }
        
        /* Director Banner */
        .salary-role-banner-director {
            background: linear-gradient(135deg, #b45309, #d97706);
            box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.4);
        }
        
        .salary-role-banner-director::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M30 20 L35 28 L44 30 L35 32 L30 40 L25 32 L16 30 L25 28 Z' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.2;
            pointer-events: none;
            z-index: -1;
        }
        
        /* Salary Stat Cards */
        .salary-stat-card {
            background: white;
            border-radius: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.6);
            position: relative;
            overflow: hidden;
        }
        
        .salary-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .salary-stat-card-admin::before {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
        }
        
        .salary-stat-card-director::before {
            background: linear-gradient(90deg, #b45309, #d97706);
        }
        
        .salary-stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .salary-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -12px rgba(0, 0, 0, 0.15);
            border-color: transparent;
        }
        
        /* Quick Actions */
        .salary-quick-actions-admin {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .salary-quick-actions-director {
            background: linear-gradient(135deg, #b45309, #d97706);
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .salary-quick-actions-admin::before,
        .salary-quick-actions-director::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        .salary-quick-actions-admin:hover::before,
        .salary-quick-actions-director:hover::before {
            opacity: 1;
        }
        
        /* Action Buttons */
        .salary-action-button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.875rem;
            font-weight: 500;
            width: 100%;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            color: white;
        }
        
        .salary-action-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(4px);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        /* Role Badges */
        .salary-badge-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            background: rgba(79, 70, 229, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(79, 70, 229, 0.3);
            color: #e0e7ff;
        }
        
        .salary-badge-director {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            background: rgba(217, 119, 6, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(217, 119, 6, 0.3);
            color: #fef3c7;
        }
        
        .salary-badge-admin:hover,
        .salary-badge-director:hover {
            transform: scale(1.02);
        }
        
        /* Icon Containers */
        .salary-icon-container {
            width: 4rem;
            height: 4rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Session Cards */
        .salary-session-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            padding: 0.75rem 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .salary-session-card-director {
            background: rgba(0, 0, 0, 0.2);
        }
        
        .salary-session-card:hover {
            transform: translateY(-2px);
        }
        
        /* Notification Toast */
        .salary-notification-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            animation: salarySlideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-radius: 1rem;
            overflow: hidden;
        }
        
        .salary-notification-toast.fade-out {
            animation: salaryFadeOutRight 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes salarySlideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes salaryFadeOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @keyframes salaryShrink {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        @keyframes salarySubtlePulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.95; }
        }
        
        .salary-role-icon-pulse {
            animation: salarySubtlePulse 2s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .salary-icon-container {
                width: 3rem;
                height: 3rem;
            }
            
            .salary-badge-admin,
            .salary-badge-director {
                padding: 0.25rem 0.75rem;
                font-size: 0.7rem;
            }
            
            .salary-notification-toast {
                bottom: 1rem;
                right: 1rem;
                left: 1rem;
            }
        }
    </style>

    <script>
    // 15 Vibrant Colors for Role Change Notifications
    const SALARY_NOTIFICATION_COLORS = [
        { bg: '#ef4444', border: '#dc2626' },
        { bg: '#f97316', border: '#ea580c' },
        { bg: '#f59e0b', border: '#d97706' },
        { bg: '#eab308', border: '#ca8a04' },
        { bg: '#84cc16', border: '#65a30d' },
        { bg: '#22c55e', border: '#16a34a' },
        { bg: '#10b981', border: '#059669' },
        { bg: '#14b8a6', border: '#0d9488' },
        { bg: '#06b6d4', border: '#0891b2' },
        { bg: '#0ea5e9', border: '#0284c7' },
        { bg: '#3b82f6', border: '#2563eb' },
        { bg: '#6366f1', border: '#4f46e5' },
        { bg: '#8b5cf6', border: '#7c3aed' },
        { bg: '#a855f7', border: '#9333ea' },
        { bg: '#d946ef', border: '#c026d3' }
    ];
    
    let salaryColorIndex = 0;
    
    function getNextSalaryNotificationColor() {
        const color = SALARY_NOTIFICATION_COLORS[salaryColorIndex % SALARY_NOTIFICATION_COLORS.length];
        salaryColorIndex++;
        return color;
    }
    
    document.addEventListener('alpine:init', () => {
        Alpine.data('salaryRoleManager', () => ({
            currentRole: 'Admin',
            toggleRoleText: 'Director',
            isRotated: false,
            
            initRole() {
                const savedRole = localStorage.getItem('salary_user_role');
                if (savedRole && (savedRole === 'Admin' || savedRole === 'Director')) {
                    this.currentRole = savedRole;
                    this.updateToggleText();
                }
                if (typeof window.renderSalaryQuickActions !== 'undefined') {
                    window.renderSalaryQuickActions(this.currentRole);
                }
                this.dispatchRoleEvent();
            },
            
            toggleRole() {
                this.isRotated = true;
                setTimeout(() => { this.isRotated = false; }, 300);
                this.currentRole = this.currentRole === 'Admin' ? 'Director' : 'Admin';
                this.updateToggleText();
                localStorage.setItem('salary_user_role', this.currentRole);
                
                if (typeof window.renderSalaryQuickActions !== 'undefined') {
                    window.renderSalaryQuickActions(this.currentRole);
                }
                
                this.showModernNotification();
                this.dispatchRoleEvent();
                
                this.$nextTick(() => {
                    console.log(`Role switched to: ${this.currentRole}`);
                });
            },
            
            updateToggleText() { 
                this.toggleRoleText = this.currentRole === 'Admin' ? 'Director' : 'Admin'; 
            },
            
            dispatchRoleEvent() { 
                window.dispatchEvent(new CustomEvent('salaryRoleChanged', { detail: { role: this.currentRole } })); 
            },
            
            showModernNotification() {
                const colorSet = getNextSalaryNotificationColor();
                const role = this.currentRole;
                const icon = role === 'Admin' ? 'fa-crown' : 'fa-star-of-life';
                const roleText = role === 'Admin' ? 'Administrator' : 'Director';
                
                const notification = document.createElement('div');
                notification.className = 'salary-notification-toast';
                notification.style.background = colorSet.bg;
                notification.style.borderLeft = `4px solid ${colorSet.border}`;
                
                notification.innerHTML = `
                    <div class="px-5 py-4 min-w-[280px] sm:min-w-[320px]">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fas ${icon} text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-bold text-white text-sm uppercase tracking-wide">Role Changed</h4>
                                    <span class="px-2 py-0.5 bg-white/20 rounded-full text-white text-xs font-semibold">
                                        ${roleText}
                                    </span>
                                </div>
                                <p class="text-white/90 text-sm font-medium">
                                    Switched to <span class="font-bold">${roleText} Mode</span>
                                </p>
                                <p class="text-white/70 text-xs mt-1">
                                    ${role === 'Admin' ? 'Full access privileges enabled' : 'Review and approve payments'}
                                </p>
                            </div>
                            <button class="close-toast text-white/70 hover:text-white transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-1 bg-white/20" style="width: 100%; animation: salaryShrink 3s linear forwards;"></div>
                `;
                
                document.body.appendChild(notification);
                
                const closeBtn = notification.querySelector('.close-toast');
                closeBtn.addEventListener('click', () => {
                    notification.classList.add('fade-out');
                    setTimeout(() => notification.remove(), 300);
                });
                
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.classList.add('fade-out');
                        setTimeout(() => notification.remove(), 300);
                    }
                }, 3000);
            }
        }));
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
</head>
<body class="bg-gray-50" x-data="salaryRoleManager()" x-init="initRole()">
    
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white flex flex-col shadow-xl">
            <div class="p-6 border-b border-gray-800">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-coins text-indigo-400"></i>
                    <span>Salary Pro</span>
                </h2>
                <p class="text-gray-400 text-sm mt-1">Payment Management</p>
            </div>
            
            <div class="mx-4 mt-4 mb-2 bg-gray-800/50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-shield text-amber-400 text-sm"></i>
                        <span class="text-gray-400 text-xs">Current Role:</span>
                    </div>
                    <span class="text-white font-bold text-sm px-3 py-1 rounded-full"
                         :class="{
                             'bg-indigo-600': currentRole === 'Admin',
                             'bg-amber-600': currentRole === 'Director'
                         }" 
                         x-text="currentRole"></span>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2 mt-2">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-all duration-200 group">
                    <i class="fas fa-tachometer-alt w-5 group-hover:scale-110 transition-transform"></i>
                    <span class="ml-3">Main Dashboard</span>
                </a>
                
                <a href="{{ route('salary.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-all duration-200 group {{ request()->routeIs('salary.dashboard') ? 'bg-gray-800 shadow-lg' : '' }}">
                    <i class="fas fa-chart-line w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Salary Dashboard</span>
                </a>
                
                <div class="border-t border-gray-800 my-2"></div>
                
                <a href="{{ route('salary.payments.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-all duration-200 group {{ request()->routeIs('salary.payments.*') ? 'bg-gray-800 shadow-lg' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Payments</span>
                </a>
                <a href="{{ route('salary.deductions.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-all duration-200 group {{ request()->routeIs('salary.deductions.*') ? 'bg-gray-800 shadow-lg' : '' }}">
                    <i class="fas fa-minus-circle w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Deductions</span>
                </a>
                <a href="{{ route('salary.schedules.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-all duration-200 group {{ request()->routeIs('salary.schedules.*') ? 'bg-gray-800 shadow-lg' : '' }}">
                    <i class="fas fa-calendar-alt w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Schedules</span>
                </a>
                <a href="{{ route('salary.history') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition-all duration-200 group {{ request()->routeIs('salary.history') ? 'bg-gray-800 shadow-lg' : '' }}">
                    <i class="fas fa-history w-5 group-hover:scale-110 transition-transform"></i><span class="ml-3">Transaction History</span>
                </a>
            </nav>
            
            <div class="p-4 border-t border-gray-800 mt-auto">
                <div class="text-gray-500 text-xs text-center">
                    <i class="fas fa-shield-alt mr-1"></i> Secure System
                </div>
                <a href="{{ route('dashboard') }}" class="mt-3 flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-700 rounded-lg px-3 py-2 text-sm transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Main</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Header - Fixed Styling (Does NOT change) -->
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
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            <i class="fas fa-home"></i>
                            <span>Main Dashboard</span>
                        </a>
                        
                        <div class="w-px h-6 bg-gray-300"></div>
                        
                        <div class="flex items-center gap-3 bg-gray-100 rounded-full px-4 py-2 shadow-inner">
                            <i class="fas fa-user-tag text-indigo-600 text-sm"></i>
                            <span class="text-gray-600 text-sm font-medium">Role:</span>
                            <span class="font-bold text-sm text-indigo-600" x-text="currentRole"></span>
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
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-600"></i>
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
                
                <div x-on:salaryRoleChanged.window="refreshQuickActions()" class="transition-all duration-300">
                    
                    <!-- Role Banner - Distinct for each role -->
                    <div class="salary-role-banner mb-6 p-6 text-white shadow-xl transition-all duration-300"
                         :class="{
                             'salary-role-banner-admin': currentRole === 'Admin',
                             'salary-role-banner-director': currentRole === 'Director'
                         }">
                        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                            <div class="flex items-center gap-5">
                                <div class="salary-icon-container" :class="{'salary-role-icon-pulse': currentRole === 'Admin'}">
                                    <i class="text-2xl" :class="{
                                        'fas fa-crown': currentRole === 'Admin',
                                        'fas fa-star-of-life': currentRole === 'Director'
                                    }"></i>
                                </div>
                                
                                <div>
                                    <h2 class="text-2xl lg:text-3xl font-bold tracking-tight" 
                                        x-text="currentRole === 'Admin' ? 'Administrator Mode' : 'Director Mode'"></h2>
                                    <p class="text-sm lg:text-base opacity-90 mt-2 max-w-lg" 
                                       x-text="currentRole === 'Admin' ? 
                                               'You have full access to all features including approvals, edits, and system configuration.' : 
                                               'You can review and approve payments, monitor reports, and view all transactions.'">
                                    </p>
                                </div>
                            </div>
                            
                            <div class="salary-session-card" :class="{'salary-session-card-director': currentRole === 'Director'}">
                                <div class="text-right">
                                    <p class="text-xs opacity-75 uppercase tracking-wider">Current Session</p>
                                    <p class="text-sm font-mono font-semibold mt-1" 
                                       x-text="currentRole === 'Admin' ? 'full_admin_access' : 'director_review_only'"></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role-specific Badges -->
                        <div class="mt-5 pt-4 border-t border-white/20 flex flex-wrap gap-2">
                            <template x-if="currentRole === 'Admin'">
                                <div class="flex flex-wrap gap-2">
                                    <span class="salary-badge-admin"><i class="fas fa-check-circle"></i> Create & Edit</span>
                                    <span class="salary-badge-admin"><i class="fas fa-trash-alt"></i> Delete Records</span>
                                    <span class="salary-badge-admin"><i class="fas fa-user-plus"></i> Manage Users</span>
                                    <span class="salary-badge-admin"><i class="fas fa-database"></i> System Backup</span>
                                </div>
                            </template>
                            
                            <template x-if="currentRole === 'Director'">
                                <div class="flex flex-wrap gap-2">
                                    <span class="salary-badge-director"><i class="fas fa-check-double"></i> Approve Payments</span>
                                    <span class="salary-badge-director"><i class="fas fa-chart-line"></i> View Reports</span>
                                    <span class="salary-badge-director"><i class="fas fa-eye"></i> Read-only Access</span>
                                    <span class="salary-badge-director"><i class="fas fa-chart-bar"></i> Performance Metrics</span>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                        <div class="salary-stat-card p-5"
                             :class="{
                                 'salary-stat-card-admin': currentRole === 'Admin',
                                 'salary-stat-card-director': currentRole === 'Director'
                             }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Total Payments</p>
                                    <p class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($totalPayments ?? 0, 2) }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                     :class="{
                                         'bg-indigo-100': currentRole === 'Admin',
                                         'bg-amber-100': currentRole === 'Director'
                                     }">
                                    <i class="fas fa-money-bill-wave text-lg"
                                       :class="{
                                           'text-indigo-600': currentRole === 'Admin',
                                           'text-amber-600': currentRole === 'Director'
                                       }"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="salary-stat-card p-5"
                             :class="{
                                 'salary-stat-card-admin': currentRole === 'Admin',
                                 'salary-stat-card-director': currentRole === 'Director'
                             }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Pending Approvals</p>
                                    <p id="pendingCount" class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingApprovals ?? 0 }}</p>
                                </div>
                                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-clock text-amber-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button onclick="window.showSalaryApprovalModal()" 
                                        class="text-xs font-medium transition"
                                        :class="{
                                            'text-indigo-600 hover:text-indigo-800': currentRole === 'Admin',
                                            'text-amber-600 hover:text-amber-800': currentRole === 'Director'
                                        }">
                                    <i class="fas fa-check-double mr-1"></i> Review all pending
                                </button>
                            </div>
                        </div>
                        
                        <div class="salary-stat-card p-5"
                             :class="{
                                 'salary-stat-card-admin': currentRole === 'Admin',
                                 'salary-stat-card-director': currentRole === 'Director'
                             }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Total Deductions</p>
                                    <p class="text-2xl font-bold text-red-600 mt-1">KES {{ number_format($totalDeductions ?? 0, 2) }}</p>
                                </div>
                                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-minus-circle text-red-600 text-lg"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions Card -->
                        <div :class="{
                            'salary-quick-actions-admin': currentRole === 'Admin',
                            'salary-quick-actions-director': currentRole === 'Director'
                        }" class="p-5 text-white">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="opacity-75 text-sm font-medium">Quick Actions</p>
                                    <p class="text-xl font-bold" x-text="currentRole === 'Admin' ? 'Admin Tools' : 'Director Tools'"></p>
                                </div>
                                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-bolt text-white text-lg"></i>
                                </div>
                            </div>
                            <div id="salaryQuickActionsContent" class="space-y-2"></div>
                        </div>
                    </div>
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Approval Modal -->
    <div id="salaryApprovalModal" class="fixed inset-0 z-50 hidden modal-overlay flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden shadow-2xl">
            <div class="sticky top-0 px-6 py-4 flex justify-between items-center"
                 :class="{
                     'bg-gradient-to-r from-indigo-600 to-purple-600': currentRole === 'Admin',
                     'bg-gradient-to-r from-amber-600 to-amber-700': currentRole === 'Director'
                 }">
                <h3 class="text-xl font-bold text-white">Pending Approvals</h3>
                <button onclick="closeSalaryApprovalModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6" style="max-height: calc(80vh - 70px);">
                <div id="salaryApprovalListContent" class="space-y-3"></div>
            </div>
        </div>
    </div>
    
    <!-- Performance Metrics Modal -->
    <div id="salaryMetricsModal" class="fixed inset-0 z-50 hidden modal-overlay flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden shadow-2xl">
            <div class="sticky top-0 px-6 py-4 flex justify-between items-center"
                 :class="{
                     'bg-gradient-to-r from-indigo-600 to-purple-600': currentRole === 'Admin',
                     'bg-gradient-to-r from-amber-600 to-amber-700': currentRole === 'Director'
                 }">
                <h3 class="text-xl font-bold text-white">Performance Metrics Dashboard</h3>
                <button onclick="closeSalaryMetricsModal()" class="text-white hover:text-gray-200 transition">
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
                                <p id="salaryMetricsPendingCount" class="text-2xl font-bold text-indigo-700">{{ $pendingApprovals ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Monthly Performance Trend</h4>
                    <canvas id="salaryPerformanceChart" height="200"></canvas>
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
    // Quick Actions Renderer
        window.renderSalaryQuickActions = function(role) {
            const quickActionsContent = document.getElementById('salaryQuickActionsContent');
            if (!quickActionsContent) return;
            
            if (role === 'Admin') {
                quickActionsContent.innerHTML = `
                    <button onclick="window.location.href='{{ route("salary.payments.create") }}'" class="salary-action-button">
                        <i class="fas fa-plus-circle"></i>
                        <span>Create New Payment</span>
                    </button>
                    <button onclick="window.location.href='{{ route("salary.deductions.create") }}'" class="salary-action-button">
                        <i class="fas fa-minus-circle"></i>
                        <span>Add Deduction</span>
                    </button>
                    <button onclick="window.location.href='{{ route("salary.schedules.create") }}'" class="salary-action-button">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Schedule Payment</span>
                    </button>
                    <button onclick="window.showSalaryMetricsModal()" class="salary-action-button">
                        <i class="fas fa-chart-line"></i>
                        <span>View Analytics</span>
                    </button>
                    <button onclick="window.salarySystemBackup()" class="salary-action-button">
                        <i class="fas fa-database"></i>
                        <span>System Backup</span>
                    </button>
                `;
            } else {
                quickActionsContent.innerHTML = `
                    <button onclick="window.location.href='{{ route("salary.history") }}'" class="salary-action-button">
                        <i class="fas fa-chart-bar"></i>
                        <span>View Reports</span>
                    </button>
                    <button onclick="window.showSalaryApprovalModal()" class="salary-action-button">
                        <i class="fas fa-check-double"></i>
                        <span>Review Approvals</span>
                    </button>
                    <button onclick="window.viewSalaryPendingPayments()" class="salary-action-button">
                        <i class="fas fa-clock"></i>
                        <span>Pending Payments</span>
                    </button>
                    <button onclick="window.location.href='{{ route("salary.history") }}?export=true'" class="salary-action-button">
                        <i class="fas fa-download"></i>
                        <span>Export Reports</span>
                    </button>
                    <button onclick="window.showSalaryMetricsModal()" class="salary-action-button">
                        <i class="fas fa-chart-line"></i>
                        <span>Performance Metrics</span>
                    </button>
                `;
            }
        };
        
        // System Backup Function
        window.salarySystemBackup = function() {
            if (confirm('This will create a full system backup. Continue?')) {
                salaryShowToast('System backup initiated...', 'info');
                setTimeout(() => {
                    salaryShowToast('Backup completed successfully!', 'success');
                }, 2000);
            }
        };
        
        // View Pending Payments Function
        window.viewSalaryPendingPayments = function() {
            window.location.href = '{{ route("salary.payments.index") }}?status=pending_approval';
        };
        
        // Toast notification helper
        function salaryShowToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
            toast.className = 'salary-notification-toast';
            toast.style.background = bgColor;
            toast.innerHTML = `
                <div class="px-5 py-4 min-w-[280px]">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')} text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-white/90 text-sm font-medium">${message}</p>
                        </div>
                        <button class="close-toast text-white/70 hover:text-white transition-colors">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="h-1 bg-white/20" style="width: 100%; animation: salaryShrink 2s linear forwards;"></div>
            `;
            document.body.appendChild(toast);
            
            const closeBtn = toast.querySelector('.close-toast');
            closeBtn.addEventListener('click', () => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            });
            
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('fade-out');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 2000);
        }
        
        // Modal Functions
        window.showSalaryApprovalModal = function() {
            const modal = document.getElementById('salaryApprovalModal');
            if (modal) {
                modal.classList.remove('hidden');
                loadSalaryApprovalItems();
            }
        };
        
        window.closeSalaryApprovalModal = function() {
            const modal = document.getElementById('salaryApprovalModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };
        
        window.showSalaryMetricsModal = function() {
            const modal = document.getElementById('salaryMetricsModal');
            if (modal) {
                modal.classList.remove('hidden');
                initSalaryPerformanceChart();
            }
        };
        
        window.closeSalaryMetricsModal = function() {
            const modal = document.getElementById('salaryMetricsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };
        
        // Load pending approval items
        function loadSalaryApprovalItems() {
            const approvalList = document.getElementById('salaryApprovalListContent');
            if (!approvalList) return;
            
            approvalList.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i>
                    <p class="mt-2 text-gray-500">Loading pending approvals...</p>
                </div>
            `;
            
            fetch('{{ route("salary.pending.approvals") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        approvalList.innerHTML = `
                            <div class="text-center py-8">
                                <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                                <p class="text-gray-500">No pending approvals at this time.</p>
                            </div>
                        `;
                        return;
                    }
                    
                    let html = '';
                    data.forEach(item => {
                        const badgeColor = item.type === 'Payment' ? 'purple' : (item.type === 'Deduction' ? 'orange' : 'blue');
                        html += `
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-1 bg-${badgeColor}-100 text-${badgeColor}-700 rounded-full text-xs font-semibold">
                                                ${item.type}
                                            </span>
                                            <span class="text-xs text-gray-500">${item.reference}</span>
                                        </div>
                                        <p class="font-semibold text-gray-800">${item.employee_name}</p>
                                        <p class="text-sm text-gray-600">Amount: KES ${salaryFormatNumber(item.amount)}</p>
                                        ${item.reason ? `<p class="text-xs text-gray-500 mt-1">Reason: ${item.reason}</p>` : ''}
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <button onclick="salaryApproveItem('${item.type}', ${item.id})" 
                                                class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="salaryRejectItem('${item.type}', ${item.id})" 
                                                class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    approvalList.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading approvals:', error);
                    approvalList.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-3"></i>
                            <p class="text-gray-500">Error loading pending approvals.</p>
                            <button onclick="loadSalaryApprovalItems()" class="mt-3 px-4 py-2 bg-purple-600 text-white rounded-lg">
                                Try Again
                            </button>
                        </div>
                    `;
                });
        }
        
        // Approve item
        window.salaryApproveItem = function(type, id) {
            if (!confirm(`Are you sure you want to approve this ${type}?`)) return;
            
            const typeLower = type.toLowerCase();
            fetch(`/salary/${typeLower}/approve/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    salaryShowToast('Item approved successfully!', 'success');
                    loadSalaryApprovalItems();
                    updateSalaryPendingCount();
                } else {
                    salaryShowToast(data.message || 'Error approving item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                salaryShowToast('Error processing request', 'error');
            });
        };
        
        // Reject item
        window.salaryRejectItem = function(type, id) {
            const reason = prompt('Please provide a reason for rejection:');
            if (!reason) return;
            
            const typeLower = type.toLowerCase();
            fetch(`/salary/${typeLower}/reject/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    salaryShowToast('Item rejected successfully!', 'info');
                    loadSalaryApprovalItems();
                    updateSalaryPendingCount();
                } else {
                    salaryShowToast(data.message || 'Error rejecting item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                salaryShowToast('Error processing request', 'error');
            });
        };
        
        // Update pending count
        function updateSalaryPendingCount() {
            fetch('{{ route("salary.pending.count") }}')
                .then(response => response.json())
                .then(data => {
                    const pendingCount = document.getElementById('pendingCount');
                    const metricsPendingCount = document.getElementById('salaryMetricsPendingCount');
                    if (pendingCount) pendingCount.textContent = data.pending_count;
                    if (metricsPendingCount) metricsPendingCount.textContent = data.pending_count;
                })
                .catch(error => console.error('Error updating count:', error));
        }
        
        // Format number as currency
        function salaryFormatNumber(value) {
            return new Intl.NumberFormat('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }
        
        // Initialize performance chart
        function initSalaryPerformanceChart() {
            const canvas = document.getElementById('salaryPerformanceChart');
            if (!canvas) return;
            
            if (window.salaryPerformanceChartInstance) {
                window.salaryPerformanceChartInstance.destroy();
            }
            
            window.salaryPerformanceChartInstance = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Payment Processing Time (hours)',
                        data: [2.8, 2.6, 2.4, 2.3, 2.2, 2.1, 2.0, 1.9, 1.8, 1.7, 1.6, 1.5],
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        }
        
        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const approvalModal = document.getElementById('salaryApprovalModal');
            const metricsModal = document.getElementById('salaryMetricsModal');
            
            if (event.target === approvalModal) {
                closeSalaryApprovalModal();
            }
            if (event.target === metricsModal) {
                closeSalaryMetricsModal();
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSalaryApprovalModal();
                closeSalaryMetricsModal();
            }
        });
        
        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            const savedRole = localStorage.getItem('salary_user_role');
            const role = (savedRole === 'Admin' || savedRole === 'Director') ? savedRole : 'Admin';
            if (typeof window.renderSalaryQuickActions !== 'undefined') {
                window.renderSalaryQuickActions(role);
            }
            updateSalaryPendingCount();
        });
    </script>
</body>
</html>