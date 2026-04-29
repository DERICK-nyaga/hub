@auth
<div class="col-md-2 sidebar p-0">
    <div class="sidebar-brand">
        <h4 class="text-center mb-0">
            <i class="fas fa-store-alt me-2"></i> PickupPoints
        </h4>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav flex-column" id="sidebarNav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('stations.*') ? 'active' : '' }}" href="{{ route('stations.index') }}">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <span>Stations</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                    <i class="fas fa-users me-2"></i>
                    <span>Employees</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees_profile.*') ? 'active' : '' }}" href="{{ route('employees_profile.index') }}">
                    <i class="fas fa-user-circle me-2"></i>
                    <span>Emp-Profiles</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-laptop me-2"></i>
                    <span>Equipment</span>
                </a>
            </li>
            
            <!-- Payments Dropdown -->
            <li class="nav-item dropdown-container">
                <a class="nav-link dropdown-toggle-custom" href="javascript:void(0)" onclick="toggleDropdown('paymentsSubmenu', this)">
                    <i class="fas fa-money-bill me-2"></i>
                    <span>Payments</span>
                    <i class="fas fa-chevron-down chevron-icon ms-auto"></i>
                </a>
                <ul class="nav flex-column submenu" id="paymentsSubmenu" style="display: none;">
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.index') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                            <i class="fas fa-list me-2"></i>
                            <span>All Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.internet.*') ? 'active' : '' }}" href="{{ route('payments.internet.index') }}">
                            <i class="fas fa-globe me-2"></i>
                            <span>Internet Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.airtime.*') ? 'active' : '' }}" href="{{ route('payments.airtime.index') }}">
                            <i class="fas fa-phone me-2"></i>
                            <span>Airtime Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('payments.schedules.*') ? 'active' : '' }}" href="{{ route('payments.schedules.index') }}">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span>Payment Schedules</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports Dropdown -->
            <li class="nav-item dropdown-container">
                <a class="nav-link dropdown-toggle-custom" href="javascript:void(0)" onclick="toggleDropdown('reportsSubmenu', this)">
                    <i class="fas fa-chart-bar me-2"></i>
                    <span>Reports</span>
                    <i class="fas fa-chevron-down chevron-icon ms-auto"></i>
                </a>
                <ul class="nav flex-column submenu" id="reportsSubmenu" style="display: none;">
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="fas fa-file me-2"></i>
                            <span>My Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('reports.create') ? 'active' : '' }}" href="{{ route('reports.create') }}">
                            <i class="fas fa-plus-circle me-2"></i>
                            <span>Create Report</span>
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('reports.handle') ? 'active' : '' }}" href="{{ route('reports.handle') }}">
                            <i class="fas fa-tasks me-2"></i>
                            <span>Manage Reports</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('ViewAll') ? 'active' : '' }}" href="{{ route('ViewAll') }}">
                            <i class="fas fa-list me-2"></i>
                            <span>All Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('clear.reports') ? 'active' : '' }}" href="{{ route('clear.reports') }}">
                            <i class="fas fa-broom me-2"></i>
                            <span>Clear Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('deductions.index') ? 'active' : '' }}" href="{{ route('deductions.index') }}">
                            <i class="fas fa-calculator me-2"></i>
                            <span>Deductions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link submenu-link {{ request()->routeIs('losses.index') ? 'active' : '' }}" href="{{ route('losses.index') }}">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>Losses</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog me-2"></i>
                    <span>Settings</span>
                </a>
            </li>
            
            <li class="nav-item mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link w-100 text-start">
                        <i class="fas fa-power-off me-2"></i>
                        <span>LOGOUT</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>

<style>
/* Sidebar Styles */
.sidebar {
    background: linear-gradient(180deg, #1a1e2b 0%, #2d3748 100%);
    color: #e2e8f0;
    height: 100vh;
    position: sticky;
    top: 0;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-brand {
    padding: 1.5rem 1rem;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-brand h4 {
    color: white;
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.sidebar-nav {
    padding: 1rem 0;
}

.sidebar .nav {
    flex-direction: column;
    gap: 0.25rem;
}

.sidebar .nav-item {
    width: 100%;
}

.sidebar .nav-link {
    color: #cbd5e1;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    position: relative;
    border-radius: 0;
    cursor: pointer;
    text-decoration: none;
}

.sidebar .nav-link i {
    width: 1.5rem;
    font-size: 1.1rem;
    text-align: center;
    flex-shrink: 0;
}

.sidebar .nav-link span {
    flex: 1;
    text-align: left;
}

.sidebar .nav-link .chevron-icon {
    width: auto;
    font-size: 0.8rem;
    transition: transform 0.3s ease;
    margin-left: auto;
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar .nav-link.active {
    background: linear-gradient(90deg, #0d6efd 0%, rgba(13, 110, 253, 0.2) 100%);
    color: white;
    border-left: 3px solid #0d6efd;
}

.sidebar .submenu {
    margin-left: 2rem !important;
    padding-left: 0;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 0.5rem;
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}

.sidebar .submenu .nav-link {
    padding: 0.6rem 1rem;
    font-size: 0.875rem;
    color: #a0aec0;
}

.sidebar .submenu .nav-link i {
    font-size: 0.9rem;
    width: 1.25rem;
}

.sidebar .submenu .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.08);
    color: white;
}

.sidebar .submenu .nav-link.active {
    background: linear-gradient(90deg, #0d6efd 0%, rgba(13, 110, 253, 0.15) 100%);
    color: white;
    border-left: 2px solid #0d6efd;
}

.sidebar .nav-item.mt-auto {
    margin-top: auto !important;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 1rem;
    padding-top: 0.5rem;
}

.sidebar button.nav-link {
    background: none;
    border: none;
    cursor: pointer;
    width: 100%;
    text-align: left;
}

.sidebar button.nav-link:hover {
    background-color: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: #0d6efd;
    border-radius: 5px;
}
</style>

<script>
// Global toggle function
function toggleDropdown(submenuId, element) {
    var submenu = document.getElementById(submenuId);
    var chevron = element.querySelector('.chevron-icon');
    
    // Stop event from bubbling
    if (event) event.stopPropagation();
    
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        // Close all other dropdowns first
        var allSubmenus = document.querySelectorAll('.submenu');
        for (var i = 0; i < allSubmenus.length; i++) {
            allSubmenus[i].style.display = 'none';
            var parentContainer = allSubmenus[i].closest('.dropdown-container');
            if (parentContainer) {
                var parentLink = parentContainer.querySelector('.dropdown-toggle-custom');
                if (parentLink) {
                    var otherChevron = parentLink.querySelector('.chevron-icon');
                    if (otherChevron) otherChevron.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        // Open current dropdown
        submenu.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        
        // Save to localStorage
        localStorage.setItem(submenuId + '_state', 'open');
    } else {
        // Close current dropdown
        submenu.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        
        // Save to localStorage
        localStorage.setItem(submenuId + '_state', 'closed');
    }
}

// Close all dropdowns function
function closeAllDropdowns() {
    var allSubmenus = document.querySelectorAll('.submenu');
    for (var i = 0; i < allSubmenus.length; i++) {
        allSubmenus[i].style.display = 'none';
        var parentContainer = allSubmenus[i].closest('.dropdown-container');
        if (parentContainer) {
            var parentLink = parentContainer.querySelector('.dropdown-toggle-custom');
            if (parentLink) {
                var chevron = parentLink.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }
    }
}

// Check if current page is related to a dropdown
function isPaymentsPage() {
    var paymentsPatterns = ['/payments'];
    return paymentsPatterns.some(pattern => window.location.pathname.includes(pattern));
}

function isReportsPage() {
    var reportsPatterns = ['/reports', '/deductions', '/losses', 'ViewAll', 'clear-reports'];
    return reportsPatterns.some(pattern => window.location.pathname.includes(pattern));
}

// Initialize sidebar on page load
document.addEventListener('DOMContentLoaded', function() {
    var paymentsSubmenu = document.getElementById('paymentsSubmenu');
    var reportsSubmenu = document.getElementById('reportsSubmenu');
    
    // CLOSE ALL DROPDOWNS BY DEFAULT UNLESS ON RELATED PAGE
    closeAllDropdowns();
    
    // ONLY open Payments dropdown if on a payments page
    if (isPaymentsPage() && paymentsSubmenu) {
        paymentsSubmenu.style.display = 'block';
        var paymentsContainer = document.querySelector('#paymentsSubmenu')?.closest('.dropdown-container');
        if (paymentsContainer) {
            var paymentsToggle = paymentsContainer.querySelector('.dropdown-toggle-custom');
            if (paymentsToggle) {
                var paymentsChevron = paymentsToggle.querySelector('.chevron-icon');
                if (paymentsChevron) paymentsChevron.style.transform = 'rotate(180deg)';
            }
        }
        localStorage.setItem('paymentsSubmenu_state', 'open');
    } else {
        // Ensure payments dropdown is closed
        if (paymentsSubmenu) {
            paymentsSubmenu.style.display = 'none';
            localStorage.setItem('paymentsSubmenu_state', 'closed');
        }
    }
    
    // ONLY open Reports dropdown if on a reports page
    if (isReportsPage() && reportsSubmenu) {
        reportsSubmenu.style.display = 'block';
        var reportsContainer = document.querySelector('#reportsSubmenu')?.closest('.dropdown-container');
        if (reportsContainer) {
            var reportsToggle = reportsContainer.querySelector('.dropdown-toggle-custom');
            if (reportsToggle) {
                var reportsChevron = reportsToggle.querySelector('.chevron-icon');
                if (reportsChevron) reportsChevron.style.transform = 'rotate(180deg)';
            }
        }
        localStorage.setItem('reportsSubmenu_state', 'open');
    } else {
        // Ensure reports dropdown is closed
        if (reportsSubmenu) {
            reportsSubmenu.style.display = 'none';
            localStorage.setItem('reportsSubmenu_state', 'closed');
        }
    }
    
    // Add click listeners to all regular nav links (non-dropdown items)
    var regularLinks = document.querySelectorAll('.sidebar .nav-link:not(.dropdown-toggle-custom)');
    for (var i = 0; i < regularLinks.length; i++) {
        regularLinks[i].addEventListener('click', function() {
            // Close all dropdowns when clicking on regular menu items
            closeAllDropdowns();
        });
    }
    
    // Handle submenu link clicks
    var submenuLinks = document.querySelectorAll('.submenu-link');
    for (var i = 0; i < submenuLinks.length; i++) {
        submenuLinks[i].addEventListener('click', function() {
            // Keep dropdown open since we're still in related section
            // Don't close dropdowns when clicking submenu items
        });
    }
    
    // Handle clicks outside dropdowns
    document.addEventListener('click', function(event) {
        var isInsideDropdown = event.target.closest('.dropdown-container');
        var isInsideSubmenu = event.target.closest('.submenu');
        
        // Only close if clicking on regular nav items or outside
        if (!isInsideDropdown && !isInsideSubmenu) {
            var isRegularLink = event.target.closest('.nav-link:not(.dropdown-toggle-custom)');
            if (isRegularLink || (!isInsideDropdown && !isInsideSubmenu)) {
                closeAllDropdowns();
            }
        }
    });
    
    console.log('✅ Sidebar loaded - Dropdowns only open on relevant pages');
    console.log('Is Payments Page:', isPaymentsPage());
    console.log('Is Reports Page:', isReportsPage());
    console.log('Payments open:', paymentsSubmenu ? paymentsSubmenu.style.display : 'not found');
    console.log('Reports open:', reportsSubmenu ? reportsSubmenu.style.display : 'not found');
});
</script>
@endauth