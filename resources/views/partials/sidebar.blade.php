@auth
<div class="col-md-2 sidebar p-0">
    <div class="sidebar-brand">
        <h4 class="text-center mb-0">
            <i class="fas fa-store-alt"></i> PickupPoints
        </h4>
    </div>
    <div class="p-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('stations.index') ? 'active' : '' }}" href="{{ route('stations.index') }}">
                    <i class="fas fa-map-marker-alt"></i> Stations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                    <i class="fas fa-users"></i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees_profile.index') ? 'active' : '' }}" href="{{ route('employees_profile.index') }}">
                    <i class="fas fa-users"></i> Emp-Profiles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-laptop"></i> Equipment
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs(['reports.*', 'ViewAll', 'clear.reports']) ? 'active' : '' }}"
                   href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('reports.index') ? 'active' : '' }}"
                           href="{{ route('reports.index') }}">
                            <i class="fas fa-file me-2"></i> My Reports
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('reports.create') ? 'active' : '' }}"
                           href="{{ route('reports.create') }}">
                            <i class="fas fa-plus-circle me-2"></i> Create Report
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('reports.handle') ? 'active' : '' }}"
                           href="{{ route('reports.handle') }}">
                            <i class="fas fa-tasks me-2"></i> Manage Reports
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('ViewAll') ? 'active' : '' }}"
                           href="{{ route('ViewAll') }}">
                            <i class="fas fa-list me-2"></i> All Reports
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('clear.reports') ? 'active' : '' }}"
                           href="{{ route('clear.reports') }}">
                            <i class="fas fa-broom me-2"></i> Clear Reports
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('deductions.index') ? 'active' : '' }}"
                           href="{{ route('deductions.index') }}">
                            <i class="fas fa-calculator me-2"></i> Deductions
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('losses.index') ? 'active' : '' }}"
                           href="{{ route('losses.index') }}">
                            <i class="fas fa-exclamation-triangle me-2"></i> Losses
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>

            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link text-start p-0 border-0 w-100">
                        <i class="fas fa-power-off"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>
@endauth
