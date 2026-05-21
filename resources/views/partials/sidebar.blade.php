<!-- Sidebar Backdrop and Mobile Button -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>
<button class="mobile-menu-btn" id="mobileMenuToggle" onclick="toggleMobileSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- SIDEBAR - RESPONSIVE WITH SALARY COLORS -->
<div class="salary-sidebar" id="mainSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-coins"></i>
            <span>SalaryPro</span>
        </div>
        <p class="sidebar-tagline">Payment Management System</p>
    </div>
    
    <!-- User Info Card -->
    <div class="user-info-card">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-details">
            <div class="user-name">{{ Auth::user()->name ?? 'Admin User' }}</div>
            <div class="user-role" id="userRoleDisplay">
                <i class="fas fa-shield-alt"></i>
                <span>{{ ucfirst(Auth::user()->role ?? 'Admin') }}</span>
            </div>
        </div>
    </div>
    
    <!-- Role Toggle (Dynamic) -->
    <div class="role-toggle-container">
        <div class="role-toggle-label">
            <i class="fas fa-exchange-alt"></i>
            <span>Quick Role Switch</span>
        </div>
        <div class="role-toggle-switch" onclick="toggleUserRole()">
            <div class="role-toggle-track" id="roleToggleTrack">
                <div class="role-toggle-thumb" id="roleToggleThumb"></div>
            </div>
            <div class="role-toggle-status">
                <span id="roleStatusText">Admin Mode</span>
                <i class="fas fa-crown" id="roleIcon"></i>
            </div>
        </div>
    </div>
    
    <!-- Main Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav flex-column" id="sidebarNav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('stations.*') ? 'active' : '' }}" href="{{ route('stations.index') }}">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Stations</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees_profile.*') ? 'active' : '' }}" href="{{ route('employees_profile.index') }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Emp-Profiles</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-laptop"></i>
                    <span>Equipment</span>
                </a>
            </li>
            
            <!-- Payments Dropdown -->
            <li class="nav-item dropdown-container">
                <a class="dropdown-toggle-custom" href="javascript:void(0)" onclick="toggleDropdown('paymentsSubmenu', this)">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payments</span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>
                <ul class="nav flex-column submenu" id="paymentsSubmenu">
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.index') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                            <i class="fas fa-list"></i>
                            <span>All Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.internet.*') ? 'active' : '' }}" href="{{ route('payments.internet.index') }}">
                            <i class="fas fa-globe"></i>
                            <span>Internet Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.airtime.*') ? 'active' : '' }}" href="{{ route('payments.airtime.index') }}">
                            <i class="fas fa-phone-alt"></i>
                            <span>Airtime Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.schedules.*') ? 'active' : '' }}" href="{{ route('payments.schedules.index') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Payment Schedules</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports Dropdown -->
            <li class="nav-item dropdown-container">
                <a class="dropdown-toggle-custom" href="javascript:void(0)" onclick="toggleDropdown('reportsSubmenu', this)">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>
                <ul class="nav flex-column submenu" id="reportsSubmenu">
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="fas fa-file-alt"></i>
                            <span>My Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('reports.create') ? 'active' : '' }}" href="{{ route('reports.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Report</span>
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link submenu-link admin-only {{ request()->routeIs('reports.handle') ? 'active' : '' }}" href="{{ route('reports.handle') }}">
                            <i class="fas fa-tasks"></i>
                            <span>Manage Reports</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('ViewAll') ? 'active' : '' }}" href="{{ route('ViewAll') }}">
                            <i class="fas fa-list-ul"></i>
                            <span>All Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link admin-only {{ request()->routeIs('clear.reports') ? 'active' : '' }}" href="{{ route('clear.reports') }}">
                            <i class="fas fa-broom"></i>
                            <span>Clear Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('deductions.index') ? 'active' : '' }}" href="{{ route('deductions.index') }}">
                            <i class="fas fa-calculator"></i>
                            <span>Deductions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('losses.index') ? 'active' : '' }}" href="{{ route('losses.index') }}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Losses</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Salary Management Link -->
            <li class="nav-item">
                <a class="nav-link salary-link {{ request()->routeIs('salary.*') ? 'active' : '' }}" href="{{ route('salary.dashboard') }}">
                    <i class="fas fa-coins"></i>
                    <span>Salary Management</span>
                    <span class="nav-badge new-badge">NEW</span>
                </a>
            </li>
            
            <!-- Settings Dropdown -->
            <li class="nav-item dropdown-container">
                <a class="dropdown-toggle-custom" href="javascript:void(0)" onclick="toggleDropdown('settingsSubmenu', this)">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>
                <ul class="nav flex-column submenu" id="settingsSubmenu">
                    <li class="nav-item">
                        <a class="nav-link submenu-link" href="#">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link" href="#">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link admin-only" href="#">
                            <i class="fas fa-database"></i>
                            <span>System Settings</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link submenu-link admin-only" href="{{route('preferences.brightness')}}">
                            <i class="fas fa-palette"></i>
                            <span>Theme Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Logout Button -->
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="version-info">
            <i class="fas fa-code-branch"></i>
            <span>v2.0.0</span>
        </div>
        <div class="system-status">
            <i class="fas fa-circle" style="color: #10b981; font-size: 8px;"></i>
            <span>System Online</span>
        </div>
    </div>
</div>