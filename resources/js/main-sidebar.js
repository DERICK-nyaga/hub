/* ========================================
   Enhanced Sidebar JavaScript - Dynamic Features
   ======================================== */

// Global variables
let currentUserRole = localStorage.getItem('sidebar_user_role') || 'admin';

// Get CSRF token from meta tag
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

// Toggle dropdown function
function toggleDropdown(submenuId, element) {
    var submenu = document.getElementById(submenuId);
    var chevron = element.querySelector('.chevron-icon');
    
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
        
        submenu.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        localStorage.setItem(submenuId + '_state', 'open');
    } else {
        submenu.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        localStorage.setItem(submenuId + '_state', 'closed');
    }
}

// Close all dropdowns
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

// Toggle user role (Admin/Director)
function toggleUserRole() {
    const track = document.getElementById('roleToggleTrack');
    const statusText = document.getElementById('roleStatusText');
    const roleIcon = document.getElementById('roleIcon');
    const userRoleDisplay = document.getElementById('userRoleDisplay');
    
    if (currentUserRole === 'admin') {
        currentUserRole = 'director';
        statusText.innerHTML = 'Director Mode';
        roleIcon.className = 'fas fa-star-of-life';
        userRoleDisplay.innerHTML = '<i class="fas fa-star-of-life"></i><span>Director</span>';
        track.classList.remove('admin-mode');
        track.classList.add('director-mode');
        document.body.classList.remove('admin-mode');
        document.body.classList.add('director-mode');
    } else {
        currentUserRole = 'admin';
        statusText.innerHTML = 'Admin Mode';
        roleIcon.className = 'fas fa-crown';
        userRoleDisplay.innerHTML = '<i class="fas fa-shield-alt"></i><span>Admin</span>';
        track.classList.remove('director-mode');
        track.classList.add('admin-mode');
        document.body.classList.remove('director-mode');
        document.body.classList.add('admin-mode');
    }
    
    localStorage.setItem('sidebar_user_role', currentUserRole);
    localStorage.setItem('user_role', currentUserRole === 'admin' ? 'Admin' : 'Director');
    
    // Show notification
    showRoleNotification(currentUserRole === 'admin' ? 'Administrator' : 'Director');
    
    // Dispatch event for other components
    window.dispatchEvent(new CustomEvent('roleChanged', { 
        detail: { role: currentUserRole === 'admin' ? 'Admin' : 'Director' } 
    }));
}

// Show role change notification
function showRoleNotification(role) {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-5 py-3 rounded-xl shadow-lg z-50 animate-slide-in ${
        role === 'Administrator' ? 'bg-emerald-500' : 'bg-amber-500'
    } text-white`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas fa-${role === 'Administrator' ? 'crown' : 'star-of-life'} text-lg"></i>
            <div>
                <p class="font-semibold">Role Changed</p>
                <p class="text-sm opacity-90">Switched to ${role} mode</p>
            </div>
        </div>
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Check if current page is related to a dropdown
function isPaymentsPage() {
    var paymentsPatterns = ['/payments', '/internet', '/airtime', '/schedules'];
    return paymentsPatterns.some(pattern => window.location.pathname.includes(pattern));
}

function isReportsPage() {
    var reportsPatterns = ['/reports', '/deductions', '/losses', 'ViewAll', 'clear-reports'];
    return reportsPatterns.some(pattern => window.location.pathname.includes(pattern));
}

// Update dashboard badge with pending count
async function updateDashboardBadge() {
    try {
        const response = await fetch('/salary/pending-count', {
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        const badge = document.getElementById('dashboardBadge');
        if (badge && data.pending_count > 0) {
            badge.textContent = data.pending_count;
            badge.style.display = 'inline-block';
        } else if (badge) {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.log('Pending count not available:', error);
    }
}

// Initialize sidebar
function initSidebar() {
    // Set initial role
    const savedRole = localStorage.getItem('sidebar_user_role');
    if (savedRole && (savedRole === 'admin' || savedRole === 'director')) {
        currentUserRole = savedRole;
    }
    
    const track = document.getElementById('roleToggleTrack');
    const statusText = document.getElementById('roleStatusText');
    const roleIcon = document.getElementById('roleIcon');
    const userRoleDisplay = document.getElementById('userRoleDisplay');
    
    if (currentUserRole === 'admin') {
        statusText.innerHTML = 'Admin Mode';
        roleIcon.className = 'fas fa-crown';
        userRoleDisplay.innerHTML = '<i class="fas fa-shield-alt"></i><span>Admin</span>';
        if (track) track.classList.add('admin-mode');
        document.body.classList.add('admin-mode');
    } else {
        statusText.innerHTML = 'Director Mode';
        roleIcon.className = 'fas fa-star-of-life';
        userRoleDisplay.innerHTML = '<i class="fas fa-star-of-life"></i><span>Director</span>';
        if (track) track.classList.add('director-mode');
        document.body.classList.add('director-mode');
    }
    
    // Close all dropdowns initially
    closeAllDropdowns();
    
    // Open relevant dropdown based on current page
    if (isPaymentsPage()) {
        var paymentsSubmenu = document.getElementById('paymentsSubmenu');
        if (paymentsSubmenu) {
            paymentsSubmenu.style.display = 'block';
            var paymentsToggle = document.querySelector('#paymentsSubmenu')?.closest('.dropdown-container')?.querySelector('.dropdown-toggle-custom');
            if (paymentsToggle) {
                var chevron = paymentsToggle.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        }
    }
    
    if (isReportsPage()) {
        var reportsSubmenu = document.getElementById('reportsSubmenu');
        if (reportsSubmenu) {
            reportsSubmenu.style.display = 'block';
            var reportsToggle = document.querySelector('#reportsSubmenu')?.closest('.dropdown-container')?.querySelector('.dropdown-toggle-custom');
            if (reportsToggle) {
                var chevron = reportsToggle.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        }
    }
    
    // Update dashboard badge
    updateDashboardBadge();
    
    // Auto-refresh badge every 30 seconds
    setInterval(updateDashboardBadge, 30000);
    
    console.log('✅ Dynamic Sidebar Initialized');
    console.log('Current Role:', currentUserRole === 'admin' ? 'Admin' : 'Director');
}

// Run initialization when DOM is ready
document.addEventListener('DOMContentLoaded', initSidebar);

// Make functions globally available
window.toggleDropdown = toggleDropdown;
window.toggleUserRole = toggleUserRole;
window.closeAllDropdowns = closeAllDropdowns;