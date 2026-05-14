/**
 * Salary Management System - Role Manager
 * Handles role switching, quick actions, modals, and notifications
 */

// 15 Vibrant Colors for Role Change Notifications
const SALARY_NOTIFICATION_COLORS = [
    { bg: '#ef4444', border: '#dc2626' },
    { bg: '#f97316', border: '#ea580c' },
    { bg: '#f59e0b', border: '#d97706' },
    { bg: '#eab308', border: '#ca8a04' },
    { bg: '#84cc16', border: '#65a30d' },
    { bg: '#22c55e', border: '#16a34a' },
    { bg: '#10b981', border: '#059669' },
    { bg: '#14b8a6', border: '#0d9488' },
    { bg: '#06b6d4', border: '#0891b2' },
    { bg: '#0ea5e9', border: '#0284c7' },
    { bg: '#3b82f6', border: '#2563eb' },
    { bg: '#6366f1', border: '#4f46e5' },
    { bg: '#8b5cf6', border: '#7c3aed' },
    { bg: '#a855f7', border: '#9333ea' },
    { bg: '#d946ef', border: '#c026d3' }
];

let salaryColorIndex = 0;

function getNextSalaryNotificationColor() {
    const color = SALARY_NOTIFICATION_COLORS[salaryColorIndex % SALARY_NOTIFICATION_COLORS.length];
    salaryColorIndex++;
    return color;
}

// Toast notification helper
function salaryShowToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
    toast.className = 'salary-notification-toast';
    toast.style.background = bgColor;
    toast.innerHTML = `
        <div class="px-5 py-4 min-w-[280px]">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')} text-white text-lg"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-white/90 text-sm font-medium">${message}</p>
                </div>
                <button class="close-toast text-white/70 hover:text-white transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        <div class="h-1 bg-white/20" style="width: 100%; animation: salaryShrink 2s linear forwards;"></div>
    `;
    document.body.appendChild(toast);
    
    const closeBtn = toast.querySelector('.close-toast');
    closeBtn.addEventListener('click', () => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    });
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }
    }, 2000);
}

// Format number as currency
function salaryFormatNumber(value) {
    return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

// Quick Actions Renderer
window.renderSalaryQuickActions = function(role) {
    const quickActionsContent = document.getElementById('salaryQuickActionsContent');
    if (!quickActionsContent) return;
    
    if (role === 'Admin') {
        quickActionsContent.innerHTML = `
            <button onclick="window.location.href='${salaryRoutes.paymentsCreate}'" class="salary-action-button">
                <i class="fas fa-plus-circle"></i>
                <span>Create New Payment</span>
            </button>
            <button onclick="window.location.href='${salaryRoutes.deductionsCreate}'" class="salary-action-button">
                <i class="fas fa-minus-circle"></i>
                <span>Add Deduction</span>
            </button>
            <button onclick="window.location.href='${salaryRoutes.schedulesCreate}'" class="salary-action-button">
                <i class="fas fa-calendar-plus</i>
                <span>Schedule Payment</span>
            </button>
            <button onclick="window.showSalaryMetricsModal()" class="salary-action-button">
                <i class="fas fa-chart-line"></i>
                <span>View Analytics</span>
            </button>
            <button onclick="window.salarySystemBackup()" class="salary-action-button">
                <i class="fas fa-database"></i>
                <span>System Backup</span>
            </button>
        `;
    } else {
        quickActionsContent.innerHTML = `
            <button onclick="window.location.href='${salaryRoutes.history}'" class="salary-action-button">
                <i class="fas fa-chart-bar"></i>
                <span>View Reports</span>
            </button>
            <button onclick="window.showSalaryApprovalModal()" class="salary-action-button">
                <i class="fas fa-check-double"></i>
                <span>Review Approvals</span>
            </button>
            <button onclick="window.viewSalaryPendingPayments()" class="salary-action-button">
                <i class="fas fa-clock"></i>
                <span>Pending Payments</span>
            </button>
            <button onclick="window.location.href='${salaryRoutes.history}?export=true'" class="salary-action-button">
                <i class="fas fa-download"></i>
                <span>Export Reports</span>
            </button>
            <button onclick="window.showSalaryMetricsModal()" class="salary-action-button">
                <i class="fas fa-chart-line"></i>
                <span>Performance Metrics</span>
            </button>
        `;
    }
};

// System Backup Function
window.salarySystemBackup = function() {
    if (confirm('This will create a full system backup. Continue?')) {
        salaryShowToast('System backup initiated...', 'info');
        setTimeout(() => {
            salaryShowToast('Backup completed successfully!', 'success');
        }, 2000);
    }
};

// View Pending Payments Function
window.viewSalaryPendingPayments = function() {
    window.location.href = `${salaryRoutes.payments}?status=pending_approval`;
};

// Load pending approval items
function loadSalaryApprovalItems() {
    const approvalList = document.getElementById('salaryApprovalListContent');
    if (!approvalList) return;
    
    approvalList.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i>
            <p class="mt-2 text-gray-500">Loading pending approvals...</p>
        </div>
    `;
    
    fetch(salaryRoutes.pendingApprovals)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                approvalList.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                        <p class="text-gray-500">No pending approvals at this time.</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.forEach(item => {
                const badgeColor = item.type === 'Payment' ? 'purple' : (item.type === 'Deduction' ? 'orange' : 'blue');
                html += `
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-1 bg-${badgeColor}-100 text-${badgeColor}-700 rounded-full text-xs font-semibold">
                                        ${item.type}
                                    </span>
                                    <span class="text-xs text-gray-500">${item.reference}</span>
                                </div>
                                <p class="font-semibold text-gray-800">${item.employee_name}</p>
                                <p class="text-sm text-gray-600">Amount: KES ${salaryFormatNumber(item.amount)}</p>
                                ${item.reason ? `<p class="text-xs text-gray-500 mt-1">Reason: ${item.reason}</p>` : ''}
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="salaryApproveItem('${item.type}', ${item.id})" 
                                        class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button onclick="salaryRejectItem('${item.type}', ${item.id})" 
                                        class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            approvalList.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading approvals:', error);
            approvalList.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-3"></i>
                    <p class="text-gray-500">Error loading pending approvals.</p>
                    <button onclick="loadSalaryApprovalItems()" class="mt-3 px-4 py-2 bg-purple-600 text-white rounded-lg">
                        Try Again
                    </button>
                </div>
            `;
        });
}

// Approve item
window.salaryApproveItem = function(type, id) {
    if (!confirm(`Are you sure you want to approve this ${type}?`)) return;
    
    const typeLower = type.toLowerCase();
    fetch(`/salary/${typeLower}/approve/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            salaryShowToast('Item approved successfully!', 'success');
            loadSalaryApprovalItems();
            updateSalaryPendingCount();
        } else {
            salaryShowToast(data.message || 'Error approving item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        salaryShowToast('Error processing request', 'error');
    });
};

// Reject item
window.salaryRejectItem = function(type, id) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    const typeLower = type.toLowerCase();
    fetch(`/salary/${typeLower}/reject/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            salaryShowToast('Item rejected successfully!', 'info');
            loadSalaryApprovalItems();
            updateSalaryPendingCount();
        } else {
            salaryShowToast(data.message || 'Error rejecting item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        salaryShowToast('Error processing request', 'error');
    });
};

// Update pending count
function updateSalaryPendingCount() {
    fetch(salaryRoutes.pendingCount)
        .then(response => response.json())
        .then(data => {
            const pendingCount = document.getElementById('pendingCount');
            const metricsPendingCount = document.getElementById('salaryMetricsPendingCount');
            if (pendingCount) pendingCount.textContent = data.pending_count;
            if (metricsPendingCount) metricsPendingCount.textContent = data.pending_count;
        })
        .catch(error => console.error('Error updating count:', error));
}

// Initialize performance chart
function initSalaryPerformanceChart() {
    const canvas = document.getElementById('salaryPerformanceChart');
    if (!canvas) return;
    
    if (window.salaryPerformanceChartInstance) {
        window.salaryPerformanceChartInstance.destroy();
    }
    
    window.salaryPerformanceChartInstance = new Chart(canvas, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Payment Processing Time (hours)',
                data: [2.8, 2.6, 2.4, 2.3, 2.2, 2.1, 2.0, 1.9, 1.8, 1.7, 1.6, 1.5],
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            }
        }
    });
}

// Modal Functions
window.showSalaryApprovalModal = function() {
    const modal = document.getElementById('salaryApprovalModal');
    if (modal) {
        modal.classList.remove('hidden');
        loadSalaryApprovalItems();
    }
};

window.closeSalaryApprovalModal = function() {
    const modal = document.getElementById('salaryApprovalModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};

window.showSalaryMetricsModal = function() {
    const modal = document.getElementById('salaryMetricsModal');
    if (modal) {
        modal.classList.remove('hidden');
        initSalaryPerformanceChart();
    }
};

window.closeSalaryMetricsModal = function() {
    const modal = document.getElementById('salaryMetricsModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};

// Alpine.js Role Manager Component
document.addEventListener('alpine:init', () => {
    Alpine.data('salaryRoleManager', () => ({
        currentRole: 'Admin',
        toggleRoleText: 'Director',
        isRotated: false,
        
        initRole() {
            const savedRole = localStorage.getItem('salary_user_role');
            if (savedRole && (savedRole === 'Admin' || savedRole === 'Director')) {
                this.currentRole = savedRole;
                this.updateToggleText();
            }
            if (typeof window.renderSalaryQuickActions !== 'undefined') {
                window.renderSalaryQuickActions(this.currentRole);
            }
            this.dispatchRoleEvent();
        },
        
        toggleRole() {
            this.isRotated = true;
            setTimeout(() => { this.isRotated = false; }, 300);
            this.currentRole = this.currentRole === 'Admin' ? 'Director' : 'Admin';
            this.updateToggleText();
            localStorage.setItem('salary_user_role', this.currentRole);
            
            if (typeof window.renderSalaryQuickActions !== 'undefined') {
                window.renderSalaryQuickActions(this.currentRole);
            }
            
            this.showModernNotification();
            this.dispatchRoleEvent();
            
            this.$nextTick(() => {
                console.log(`Role switched to: ${this.currentRole}`);
            });
        },
        
        updateToggleText() { 
            this.toggleRoleText = this.currentRole === 'Admin' ? 'Director' : 'Admin'; 
        },
        
        dispatchRoleEvent() { 
            window.dispatchEvent(new CustomEvent('salaryRoleChanged', { detail: { role: this.currentRole } })); 
        },
        
        showModernNotification() {
            const colorSet = getNextSalaryNotificationColor();
            const role = this.currentRole;
            const icon = role === 'Admin' ? 'fa-crown' : 'fa-star-of-life';
            const roleText = role === 'Admin' ? 'Administrator' : 'Director';
            
            const notification = document.createElement('div');
            notification.className = 'salary-notification-toast';
            notification.style.background = colorSet.bg;
            notification.style.borderLeft = `4px solid ${colorSet.border}`;
            
            notification.innerHTML = `
                <div class="px-5 py-4 min-w-[280px] sm:min-w-[320px]">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <i class="fas ${icon} text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-bold text-white text-sm uppercase tracking-wide">Role Changed</h4>
                                <span class="px-2 py-0.5 bg-white/20 rounded-full text-white text-xs font-semibold">
                                    ${roleText}
                                </span>
                            </div>
                            <p class="text-white/90 text-sm font-medium">
                                Switched to <span class="font-bold">${roleText} Mode</span>
                            </p>
                            <p class="text-white/70 text-xs mt-1">
                                ${role === 'Admin' ? 'Full access privileges enabled' : 'Review and approve payments'}
                            </p>
                        </div>
                        <button class="close-toast text-white/70 hover:text-white transition-colors">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="h-1 bg-white/20" style="width: 100%; animation: salaryShrink 3s linear forwards;"></div>
            `;
            
            document.body.appendChild(notification);
            
            const closeBtn = notification.querySelector('.close-toast');
            closeBtn.addEventListener('click', () => {
                notification.classList.add('fade-out');
                setTimeout(() => notification.remove(), 300);
            });
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.classList.add('fade-out');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
    }));
});

// Route configuration (to be populated by Blade)
let salaryRoutes = {
    paymentsCreate: '',
    deductionsCreate: '',
    schedulesCreate: '',
    history: '',
    payments: '',
    pendingApprovals: '',
    pendingCount: ''
};

// Function to set routes from Blade
window.setSalaryRoutes = function(routes) {
    salaryRoutes = { ...salaryRoutes, ...routes };
};

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const approvalModal = document.getElementById('salaryApprovalModal');
    const metricsModal = document.getElementById('salaryMetricsModal');
    
    if (event.target === approvalModal) {
        closeSalaryApprovalModal();
    }
    if (event.target === metricsModal) {
        closeSalaryMetricsModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSalaryApprovalModal();
        closeSalaryMetricsModal();
    }
});

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    const savedRole = localStorage.getItem('salary_user_role');
    const role = (savedRole === 'Admin' || savedRole === 'Director') ? savedRole : 'Admin';
    if (typeof window.renderSalaryQuickActions !== 'undefined') {
        window.renderSalaryQuickActions(role);
    }
    updateSalaryPendingCount();
});