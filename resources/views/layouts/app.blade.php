<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Management - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/employee-create.css', 'resources/css/fixedstyles.css', 'resources/css/modifiedstyles.css', 'resources/css/order-numbers.css', 'resources/css/sidebar.css', 'resources/js/sidebar.js', 'resources/js/payments.js', 'resources/js/app.js', 'resources/js/employee-balance.js', 'resources/js/deductions.js', 'resources/js/employee-statuses.js', 'resources/js/airtime.js'])
    @stack('styles')
</head>
<body>
    @include('partials.sidebar')
    
    <div class="salary-main-content" id="mainContent">
        <!-- Modern Header -->
        <div class="modern-header">
            <div class="header-container">
                <div class="header-left">
                    <div class="header-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="header-title-section">
                        <h1 class="header-title">@yield('title', 'Dashboard')</h1>
                        <p class="header-subtitle">Salary & Payment Management System</p>
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="stats-badge">
                        <i class="fas fa-chart-simple"></i>
                        <span>Live Updates</span>
                    </div>
                    
                    <div class="role-card">
                        <div class="role-card-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div class="role-card-content">
                            <span class="role-label">Current Role</span>
                            <strong class="role-value" id="topRoleDisplay">Admin</strong>
                        </div>
                    </div>
                    
                    <button onclick="toggleUserRole()" class="role-toggle-btn">
                        <div class="toggle-inner">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Switch to</span>
                            <span class="toggle-role-badge" id="toggleRoleText">Director</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-wrapper">
            <div class="breadcrumb-container">
                <a href="{{ route('salary.dashboard') }}" class="breadcrumb-link">
                    <i class="fas fa-home"></i>
                    <span>Salary Dashboard</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">@yield('title', 'Overview')</span>
            </div>
            <div class="datetime-display" id="datetimeDisplay"></div>
        </div>
        
        <!-- Content -->
        <div class="content-area">
            @if(session('success'))
                <div class="alert-success">
                    <div class="alert-content">
                        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="alert-message"><strong>Success!</strong> {{ session('success') }}</div>
                        <button class="alert-close" onclick="this.parentElement.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert-error">
                    <div class="alert-content">
                        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="alert-message"><strong>Error!</strong> {{ session('error') }}</div>
                        <button class="alert-close" onclick="this.parentElement.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif
            
            <div class="page-content">
                @yield('content')
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>