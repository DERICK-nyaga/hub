<nav class="col-md-2 sidebar">
    <div class="sidebar-sticky pt-3">
        <div class="text-center py-3 border-bottom">
            <h5 class="text-white mb-0">Bill Payment System</h5>
        </div>

        <ul class="nav flex-column mt-3">

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item mt-2">
                <span class="nav-link text-muted small">PAYMENT MANAGEMENT</span>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('payments.internet.*') ? 'active bg-primary' : '' }}"
                   href="{{ route('payments.internet.index') }}">
                    <i class="fas fa-wifi me-2"></i> Internet Payments
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('payments.airtime.*') ? 'active bg-primary' : '' }}"
                   href="{{ route('payments.airtime.index') }}">
                    <i class="fas fa-phone-alt me-2"></i> Airtime Payments
                </a>
            </li>

            {{-- <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('payments.schedules.*') ? 'active bg-primary' : '' }}"
                   href="{{ route('payments.schedules.index') }}">
                    <i class="fas fa-calendar-alt me-2"></i> Schedules
                </a>
            </li> --}}

            <li class="nav-item mt-2">
                <span class="nav-link text-muted small">QUICK ACTIONS</span>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('payments.internet.create') }}">
                    <i class="fas fa-plus-circle me-2"></i> New Internet
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('payments.airtime.create') }}">
                    <i class="fas fa-plus-circle me-2"></i> New Airtime
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('stations.create') }}">
                    <i class="fas fa-plus-circle me-2"></i> Add Station
                </a>
            </li>

            <li class="nav-item mt-2">
                <span class="nav-link text-muted small">QUICK VIEWS</span>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('payments.upcoming') }}">
                    <i class="fas fa-clock me-2"></i> Upcoming
                    <span class="badge bg-warning float-end" id="upcoming-count">0</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('payments.overdue') }}">
                    <i class="fas fa-exclamation-triangle me-2"></i> Overdue
                    <span class="badge bg-danger float-end" id="overdue-count">0</span>
                </a>
            </li>

            <li class="nav-item mt-2">
                <span class="nav-link text-muted small">STATION MANAGEMENT</span>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('stations.*') ? 'active bg-primary' : '' }}"
                   href="{{ route('stations.index') }}">
                    <i class="fas fa-building me-2"></i> Stations
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('internet-providers.*') ? 'active bg-primary' : '' }}"
                   href="{{ route('internet-providers.index') }}">
                    <i class="fas fa-network-wired me-2"></i> Providers
                </a>
            </li>

            <li class="nav-item mt-2">
                <span class="nav-link text-muted small">REPORTS</span>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('reports.monthly') }}">
                    <i class="fas fa-chart-bar me-2"></i> Monthly Report
                </a>
            </li>
        </ul>
    </div>
</nav>
