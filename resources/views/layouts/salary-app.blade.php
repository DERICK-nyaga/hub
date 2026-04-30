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
    <style>
        [x-cloak] { display: none !important; }
        .transition-fast { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-900 text-white flex flex-col">
            <div class="p-6">
                <h2 class="text-2xl font-bold"><i class="fas fa-coins mr-2"></i>Salary Pro</h2>
                <p class="text-indigo-300 text-sm mt-1">Payment Management</p>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="{{ route('salary.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('salary.dashboard') ? 'bg-indigo-800' : '' }}">
                    <i class="fas fa-chart-line w-5"></i><span class="ml-3">Dashboard</span>
                </a>
                <a href="{{ route('salary.payments.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('salary.payments.*') ? 'bg-indigo-800' : '' }}">
                    <i class="fas fa-money-bill-wave w-5"></i><span class="ml-3">Payments</span>
                </a>
                <a href="{{ route('salary.deductions.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('salary.deductions.*') ? 'bg-indigo-800' : '' }}">
                    <i class="fas fa-minus-circle w-5"></i><span class="ml-3">Deductions</span>
                </a>
                <a href="{{ route('salary.schedules.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('salary.schedules.*') ? 'bg-indigo-800' : '' }}">
                    <i class="fas fa-calendar-alt w-5"></i><span class="ml-3">Schedules</span>
                </a>
                <a href="{{ route('salary.history') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-indigo-800 transition">
                    <i class="fas fa-history w-5"></i><span class="ml-3">Transaction History</span>
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>