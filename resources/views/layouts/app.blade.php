<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Salary Management - @yield('title')</title>
    
    {{-- Preload Critical Assets --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    {{-- Bootstrap & Font Awesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Vite Assets --}}
    @vite([
        'resources/css/app.css', 
        'resources/css/dashboard.css', 
        'resources/css/employee-create.css', 
        'resources/css/fixedstyles.css', 
        'resources/css/modifiedstyles.css', 
        'resources/css/order-numbers.css', 
        'resources/css/sidebar.css', 
        'resources/css/dark-mode.css',
        'resources/css/settings-panel.css',
        'resources/css/theme-variables.css',
        'resources/js/sidebar.js', 
        'resources/js/payments.js', 
        'resources/js/app.js', 
        'resources/js/employee-balance.js', 
        'resources/js/deductions.js', 
        'resources/js/employee-statuses.js', 
        'resources/js/airtime.js',
    ])
    
    {{-- Anti-Flash Theme Script (Critical - Must Run First) --}}
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
                const meta = document.createElement('meta');
                meta.name = 'theme-color';
                meta.content = '#1a1a1a';
                document.head.appendChild(meta);
            }
            
            const brightness = localStorage.getItem('brightness');
            if (brightness && brightness !== '100') {
                document.documentElement.style.filter = `brightness(${brightness}%)`;
            }
        })();
    </script>
    
    @stack('styles')
</head>
<body>
    {{-- Navigation Transition Handler --}}
    <script>
        document.body.classList.add('no-transition');
        
        window.addEventListener('load', () => {
            setTimeout(() => document.body.classList.remove('no-transition'), 100);
        });
        
        // Handle various navigation methods
        document.addEventListener('turbo:before-render', () => document.body.classList.add('no-transition'));
        document.addEventListener('turbo:render', () => setTimeout(() => document.body.classList.remove('no-transition'), 50));
        
        document.querySelectorAll('a:not([target="_blank"]):not([data-turbo="false"])').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.href && !link.href.startsWith('javascript:')) {
                    document.body.classList.add('no-transition');
                }
            });
        });
        
        window.addEventListener('pageshow', () => {
            if (window.themeManager) {
                const theme = localStorage.getItem('theme') || 'light';
                const brightness = localStorage.getItem('brightness') || '100';
                window.themeManager.applyThemeImmediately(theme);
                window.themeManager.applyBrightnessImmediately(parseInt(brightness));
            }
            setTimeout(() => document.body.classList.remove('no-transition'), 50);
        });
    </script>
    
    {{-- Sidebar --}}
    @include('partials.sidebar')
    
    {{-- Main Content --}}
    <div class="salary-main-content" id="mainContent">
        {{-- Modern Header --}}
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
                    {{-- Display Settings Button --}}
                    <button id="toggleSettings" class="settings-toggle-commercial" title="Display Settings">
                        <i class="fas fa-sliders-h"></i>
                        <span>Display</span>
                        <div class="toggle-indicator"></div>
                    </button>
                    
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
        
        {{-- Settings Panel Modal --}}
        <div id="settingsPanel" class="settings-panel-overlay" style="display: none;">
            <div class="settings-panel-container-commercial">
                <div class="settings-panel-header-commercial">
                    <button class="close-settings-commercial" onclick="closeSettingsPanel()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="settings-panel-body-commercial">
                    @include('components.settings-panel')
                </div>
            </div>
        </div>
        
        {{-- Breadcrumb --}}
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
        
        {{-- Content Area with Flash Messages --}}
        <div class="content-area">
            @if(session('success'))
                <div class="alert-success">
                    <div class="alert-content">
                        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="alert-message"><strong>Success!</strong> {{ session('success') }}</div>
                        <button class="alert-close" onclick="this.closest('.alert-success').remove()"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert-error">
                    <div class="alert-content">
                        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="alert-message"><strong>Error!</strong> {{ session('error') }}</div>
                        <button class="alert-close" onclick="this.closest('.alert-error').remove()"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif
            
            <div class="page-content">
                @yield('content')
            </div>
        </div>
    </div>
    
    {{-- Theme Components --}}
    @include('components.theme-manager')
    
    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Settings Panel Controls --}}
    <script>
        function toggleSettingsPanel() {
            const panel = document.getElementById('settingsPanel');
            if (panel) {
                const isHidden = panel.style.display === 'none' || panel.style.display === '';
                panel.style.display = isHidden ? 'flex' : 'none';
                document.body.style.overflow = isHidden ? 'hidden' : '';
            }
        }
        
        function closeSettingsPanel() {
            const panel = document.getElementById('settingsPanel');
            if (panel) {
                panel.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggleSettings');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSettingsPanel);
            }
            
            const panel = document.getElementById('settingsPanel');
            if (panel) {
                panel.addEventListener('click', (e) => {
                    if (e.target === panel) closeSettingsPanel();
                });
            }
            
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeSettingsPanel();
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>