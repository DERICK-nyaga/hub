// resources/js/salary.js

// Global variables
let currentUserRole = localStorage.getItem('sidebar_user_role') || 'admin';

// Toggle dropdown function
function toggleDropdown(submenuId, element) {
    if (event) event.stopPropagation();
    
    var submenu = document.getElementById(submenuId);
    var chevron = element.querySelector('.chevron-icon');
    
    if (!submenu) return;
    
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
    } else {
        submenu.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
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

// Toggle user role
function toggleUserRole() {
    const track = document.getElementById('roleToggleTrack');
    const statusText = document.getElementById('roleStatusText');
    const roleIcon = document.getElementById('roleIcon');
    const userRoleDisplay = document.getElementById('userRoleDisplay');
    const topRoleDisplay = document.getElementById('topRoleDisplay');
    const toggleRoleText = document.getElementById('toggleRoleText');
    
    if (currentUserRole === 'admin') {
        currentUserRole = 'director';
        statusText.innerHTML = 'Director Mode';
        roleIcon.className = 'fas fa-star-of-life';
        if (userRoleDisplay) userRoleDisplay.innerHTML = '<i class="fas fa-star-of-life"></i><span>Director</span>';
        if (topRoleDisplay) topRoleDisplay.innerText = 'Director';
        if (toggleRoleText) toggleRoleText.innerText = 'Admin';
        if (track) {
            track.classList.remove('admin-mode');
            track.classList.add('director-mode');
        }
        document.body.classList.remove('admin-mode');
        document.body.classList.add('director-mode');
    } else {
        currentUserRole = 'admin';
        statusText.innerHTML = 'Admin Mode';
        roleIcon.className = 'fas fa-crown';
        if (userRoleDisplay) userRoleDisplay.innerHTML = '<i class="fas fa-shield-alt"></i><span>Admin</span>';
        if (topRoleDisplay) topRoleDisplay.innerText = 'Admin';
        if (toggleRoleText) toggleRoleText.innerText = 'Director';
        if (track) {
            track.classList.remove('director-mode');
            track.classList.add('admin-mode');
        }
        document.body.classList.remove('director-mode');
        document.body.classList.add('admin-mode');
    }
    
    localStorage.setItem('sidebar_user_role', currentUserRole);
    localStorage.setItem('user_role', currentUserRole === 'admin' ? 'Admin' : 'Director');
    
    showRoleNotification(currentUserRole === 'admin' ? 'Administrator' : 'Director');
    window.dispatchEvent(new CustomEvent('roleChanged', { 
        detail: { role: currentUserRole === 'admin' ? 'Admin' : 'Director' } 
    }));
}

// Update date/time display
function updateDateTime() {
    const datetimeDisplay = document.getElementById('datetimeDisplay');
    if (datetimeDisplay) {
        const now = new Date();
        const options = { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        datetimeDisplay.textContent = now.toLocaleDateString('en-US', options);
    }
}

// Update every minute
setInterval(updateDateTime, 60000);
updateDateTime();

// Show notification
function showRoleNotification(role) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-5 py-3 rounded-xl shadow-lg z-50 animate-slide-in ${
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

// Mobile sidebar functions
function toggleMobileSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    sidebar.classList.toggle('mobile-open');
    backdrop.classList.toggle('active');
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    sidebar.classList.remove('mobile-open');
    backdrop.classList.remove('active');
}

// Initialize sidebar
function initSidebar() {
    const savedRole = localStorage.getItem('sidebar_user_role');
    if (savedRole && (savedRole === 'admin' || savedRole === 'director')) {
        currentUserRole = savedRole;
    }
    
    const track = document.getElementById('roleToggleTrack');
    const statusText = document.getElementById('roleStatusText');
    const roleIcon = document.getElementById('roleIcon');
    const userRoleDisplay = document.getElementById('userRoleDisplay');
    
    if (currentUserRole === 'admin') {
        if (statusText) statusText.innerHTML = 'Admin Mode';
        if (roleIcon) roleIcon.className = 'fas fa-crown';
        if (userRoleDisplay) userRoleDisplay.innerHTML = '<i class="fas fa-shield-alt"></i><span>Admin</span>';
        if (track) track.classList.add('admin-mode');
    } else {
        if (statusText) statusText.innerHTML = 'Director Mode';
        if (roleIcon) roleIcon.className = 'fas fa-star-of-life';
        if (userRoleDisplay) userRoleDisplay.innerHTML = '<i class="fas fa-star-of-life"></i><span>Director</span>';
        if (track) track.classList.add('director-mode');
    }
    
    closeAllDropdowns();
    
    // Open dropdown based on current page
    var pathname = window.location.pathname;
    if (pathname.includes('/payments')) {
        var paymentsSubmenu = document.getElementById('paymentsSubmenu');
        if (paymentsSubmenu) paymentsSubmenu.style.display = 'block';
    }
    
    if (pathname.includes('/reports') || pathname.includes('/deductions') || pathname.includes('/losses')) {
        var reportsSubmenu = document.getElementById('reportsSubmenu');
        if (reportsSubmenu) reportsSubmenu.style.display = 'block';
    }
    
    // Close sidebar on mobile when clicking a link
    document.querySelectorAll('.salary-sidebar .nav-link, .salary-sidebar .dropdown-toggle-custom').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && !this.classList.contains('dropdown-toggle-custom')) {
                setTimeout(() => closeMobileSidebar(), 100);
            }
        });
    });
}

// Run when DOM is ready
document.addEventListener('DOMContentLoaded', initSidebar);

// Make functions globally available
window.toggleDropdown = toggleDropdown;
window.toggleUserRole = toggleUserRole;
window.toggleMobileSidebar = toggleMobileSidebar;
window.closeMobileSidebar = closeMobileSidebar;