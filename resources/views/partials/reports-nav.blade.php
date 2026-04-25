<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('reports.index') }}">
            <i class="fas fa-chart-line me-2"></i>Reports System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item mx-1">
                    <a class="nav-link px-3 py-2 rounded-3 {{ request()->routeIs('reports.index') ? 'active bg-white text-primary' : '' }}"
                       href="{{ route('reports.index') }}">
                        <i class="fas fa-file me-2"></i>My Reports
                    </a>
                </li>
                <li class="nav-item mx-1">
                    <a class="nav-link px-3 py-2 rounded-3 {{ request()->routeIs('reports.create') ? 'active bg-white text-primary' : '' }}"
                       href="{{ route('reports.create') }}">
                        <i class="fas fa-plus-circle me-2"></i>Create Report
                    </a>
                </li>

                @if(auth()->user()->role === 'admin')
                <li class="nav-item mx-1">
                    <a class="nav-link px-3 py-2 rounded-3 {{ request()->routeIs('reports.manage') ? 'active bg-white text-primary' : '' }}"
                       href="{{ route('reports.manage') }}">
                        <i class="fas fa-tasks me-2"></i>Manage Reports
                    </a>
                </li>
                @endif
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger px-4 py-2 border-0">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
