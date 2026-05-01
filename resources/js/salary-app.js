/* ========================================
   Salary Management System - Main JavaScript
   ======================================== */

// Global variables
let pendingApprovals = [];
let performanceChartInstance = null;

// ========================================
// Pending Approvals Functions
// ========================================

window.fetchPendingApprovals = async function() {
    console.log('fetchPendingApprovals called');
    try {
        const response = await fetch('/salary/pending-approvals', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        console.log('Fetched pending approvals:', data);
        pendingApprovals = data;
        window.renderApprovalList();
        window.updatePendingCount();
    } catch (error) {
        console.error('Error fetching pending approvals:', error);
        pendingApprovals = [];
        window.renderApprovalList();
    }
};

window.renderApprovalList = function() {
    const container = document.getElementById('approvalListContent');
    if (!container) {
        console.error('approvalListContent not found');
        return;
    }
    
    if (!pendingApprovals || pendingApprovals.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <p class="text-gray-500">No pending approvals</p>
                <p class="text-xs text-gray-400">All requests have been processed</p>
                <button onclick="window.fetchPendingApprovals()" class="mt-3 px-3 py-1 bg-purple-600 text-white rounded-lg text-sm">
                    Refresh
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = pendingApprovals.map(item => `
        <div id="approval-item-${item.id}" class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg transition-all duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-gray-800">${item.type} Request #${item.reference}</p>
                    <p class="text-sm text-gray-600">Employee: ${item.employee_name}</p>
                    <p class="text-sm text-gray-600">Amount: KES ${Number(item.amount).toLocaleString()} ${item.reason ? `(${item.reason})` : ''}</p>
                    <p class="text-xs text-gray-500 mt-1">Submitted: ${new Date(item.created_at).toLocaleDateString()}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.approveRequest(${item.id}, '${item.type}')" class="px-3 py-1 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                    <button onclick="window.rejectRequest(${item.id}, '${item.type}')" class="px-3 py-1 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    `).join('');
};

window.updatePendingCount = function() {
    const pendingCountElement = document.getElementById('pendingCount');
    const metricsPendingCount = document.getElementById('metricsPendingCount');
    if (pendingCountElement) {
        pendingCountElement.textContent = pendingApprovals.length;
    }
    if (metricsPendingCount) {
        metricsPendingCount.textContent = pendingApprovals.length;
    }
};

window.approveRequest = async function(id, type) {
    const itemElement = document.getElementById(`approval-item-${id}`);
    if (itemElement) {
        itemElement.classList.add('fade-out');
        
        try {
            let endpoint = '';
            if (type === 'Payment') {
                endpoint = `/salary/payments/${id}/approve`;
            } else if (type === 'Deduction') {
                endpoint = `/salary/deductions/${id}/approve`;
            } else if (type === 'Schedule') {
                endpoint = `/salary/schedules/${id}/approve`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                setTimeout(() => {
                    window.fetchPendingApprovals();
                    window.refreshDashboardStats();
                    window.showToast('Request approved successfully!', 'success');
                    
                    if (pendingApprovals.length === 1) {
                        setTimeout(() => window.closeApprovalModal(), 1500);
                    }
                }, 300);
            } else {
                window.showToast('Error: ' + (data.message || 'Failed to approve'), 'error');
                itemElement.classList.remove('fade-out');
            }
        } catch (error) {
            console.error('Error approving request:', error);
            window.showToast('Network error. Please try again.', 'error');
            itemElement.classList.remove('fade-out');
        }
    }
};

window.rejectRequest = async function(id, type) {
    const itemElement = document.getElementById(`approval-item-${id}`);
    if (itemElement) {
        itemElement.classList.add('fade-out');
        
        try {
            let endpoint = '';
            if (type === 'Payment') {
                endpoint = `/salary/payments/${id}/reject`;
            } else if (type === 'Deduction') {
                endpoint = `/salary/deductions/${id}/reject`;
            } else if (type === 'Schedule') {
                endpoint = `/salary/schedules/${id}/reject`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                setTimeout(() => {
                    window.fetchPendingApprovals();
                    window.refreshDashboardStats();
                    window.showToast('Request rejected!', 'error');
                    
                    if (pendingApprovals.length === 1) {
                        setTimeout(() => window.closeApprovalModal(), 1500);
                    }
                }, 300);
            } else {
                window.showToast('Error: ' + (data.message || 'Failed to reject'), 'error');
                itemElement.classList.remove('fade-out');
            }
        } catch (error) {
            console.error('Error rejecting request:', error);
            window.showToast('Network error. Please try again.', 'error');
            itemElement.classList.remove('fade-out');
        }
    }
};

window.refreshDashboardStats = async function() {
    try {
        const response = await fetch('/salary/pending-count', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await response.json();
        
        const pendingCountElement = document.getElementById('pendingCount');
        if (pendingCountElement) {
            pendingCountElement.textContent = data.pending_count || 0;
        }
    } catch (error) {
        console.error('Error refreshing stats:', error);
    }
};

window.showApprovalModal = function() {
    console.log('showApprovalModal called');
    window.fetchPendingApprovals();
    const modal = document.getElementById('approvalModal');
    if (modal) modal.classList.remove('hidden');
};

window.closeApprovalModal = function() {
    const modal = document.getElementById('approvalModal');
    if (modal) modal.classList.add('hidden');
};

window.showToast = function(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-20 right-4 px-5 py-3 rounded-xl shadow-lg z-50 animate-slide-in ${
        type === 'success' ? 'bg-purple-600' : 'bg-red-600'
    } text-white`;
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'} text-lg"></i>
            <div>
                <p class="font-semibold">${type === 'success' ? 'Approved' : 'Rejected'}</p>
                <p class="text-sm opacity-90">${message}</p>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

// ========================================
// Quick Actions Configuration
// ========================================

const adminActions = [
    { icon: 'fa-plus-circle', text: 'Create New Payment', onclick: "window.location.href='/salary/payments/create'", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-minus-circle', text: 'Add Deduction', onclick: "window.location.href='/salary/deductions'", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-calendar-plus', text: 'Schedule Payment', onclick: "window.location.href='/salary/schedules'", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-chart-line', text: 'View Analytics', onclick: "window.showAnalytics()", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-database', text: 'System Backup', onclick: "window.showBackup()", color: 'bg-white/10 hover:bg-white/20' }
];

const directorActions = [
    { icon: 'fa-chart-line', text: 'View Reports', onclick: "window.location.href='/salary/history'", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-check-circle', text: 'Review Approvals', onclick: "window.showApprovalModal()", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-clock', text: 'Pending Payments', onclick: "window.location.href='/salary/payments?status=pending_approval'", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-download', text: 'Export Reports', onclick: "window.exportReports()", color: 'bg-white/10 hover:bg-white/20' },
    { icon: 'fa-chart-pie', text: 'Performance Metrics', onclick: "window.showMetrics()", color: 'bg-white/10 hover:bg-white/20' }
];

window.renderQuickActions = function(role) {
    const container = document.getElementById('quickActionsContent');
    if (!container) return;
    const actions = role === 'Admin' ? adminActions : directorActions;
    container.innerHTML = actions.map(action => `
        <button onclick="${action.onclick}" class="w-full text-left text-xs ${action.color} rounded-lg px-2 py-1.5 transition flex items-center gap-2">
            <i class="fas ${action.icon}"></i><span>${action.text}</span>
        </button>
    `).join('');
    container.classList.add('fade-in');
    setTimeout(() => container.classList.remove('fade-in'), 300);
};

window.refreshQuickActions = function() {
    const role = localStorage.getItem('user_role') || 'Admin';
    window.renderQuickActions(role);
};

window.showAnalytics = function() { 
    alert('Analytics dashboard with detailed payment statistics.'); 
};

window.showBackup = function() { 
    alert('System backup initiated.'); 
};

window.exportReports = function() { 
    alert('Exporting reports. Download will start shortly.'); 
};

// ========================================
// Performance Metrics
// ========================================

window.showMetrics = function() {
    const modal = document.getElementById('metricsModal');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            const ctx = document.getElementById('performanceChart');
            if (ctx && typeof Chart !== 'undefined') {
                if (performanceChartInstance) performanceChartInstance.destroy();
                performanceChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Payments Processed (KES)',
                            data: [45000, 52000, 48000, 61000, 58000, 75000],
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: true }
                });
            }
        }, 100);
    }
};

window.closeMetricsModal = function() {
    const modal = document.getElementById('metricsModal');
    if (modal) modal.classList.add('hidden');
};

// ========================================
// Helper Functions
// ========================================

window.hasRole = function(role) {
    const userRole = localStorage.getItem('user_role') || 'Admin';
    return userRole === role;
};

window.isAdmin = function() {
    return window.hasRole('Admin');
};

window.isDirector = function() {
    return window.hasRole('Director');
};

// ========================================
// Initialize on DOM Load
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Salary App JS loaded successfully');
    
    if (document.getElementById('quickActionsContent')) {
        const role = localStorage.getItem('user_role') || 'Admin';
        window.renderQuickActions(role);
    }
});