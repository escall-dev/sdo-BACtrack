/**
 * SDO-BACtrack Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initFlashMessages();
    initNotifications();
    initModals();
});

/**
 * Sidebar Toggle
 */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const adminLayout = document.querySelector('.admin-layout');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    // Restore sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed && window.innerWidth >= 992) {
        sidebar.classList.add('collapsed');
        adminLayout.classList.add('sidebar-collapsed');
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            adminLayout.classList.toggle('sidebar-collapsed', isCollapsed);
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            window.dispatchEvent(new Event('resize'));
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            if (mobileToggle && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
}

/**
 * Flash Messages Auto-hide
 */
function initFlashMessages() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

/**
 * Notifications
 */
function initNotifications() {
    const btn = document.getElementById('notificationBtn');
    const panel = document.getElementById('notificationPanel');
    
    if (btn && panel) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if (!panel.contains(e.target) && !btn.contains(e.target)) {
                panel.classList.remove('show');
            }
        });
    }
}

/**
 * Modal Functions
 */
function initModals() {
    // Close modal when clicking overlay
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeActivityModal();
            }
        });
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeActivityModal();
        }
    });
}

function openActivityModal(activityId) {
    const modal = document.getElementById('activityModal');
    const modalBody = document.getElementById('modalBody');
    
    modalBody.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    modal.classList.add('show');
    
    fetch(APP_URL + '/admin/api/activity-detail.php?id=' + activityId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderActivityModal(data.activity);
            } else {
                modalBody.innerHTML = '<p class="text-danger">Failed to load activity details.</p>';
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<p class="text-danger">An error occurred.</p>';
        });
}

function renderActivityModal(activity) {
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = activity.step_name;
    
    let statusClass = 'status-' + activity.status.toLowerCase().replace('_', '-');
    let complianceHtml = activity.compliance_status 
        ? `<span class="compliance-badge compliance-${activity.compliance_status.toLowerCase().replace('_', '-')}">${activity.compliance_status}</span>`
        : '<span class="text-muted">Not set</span>';
    
    let documentsHtml = '';
    if (activity.documents && activity.documents.length > 0) {
        documentsHtml = '<div class="documents-list">';
        activity.documents.forEach(doc => {
            documentsHtml += `
                <div class="document-item">
                    <i class="fas fa-file"></i>
                    <div class="document-info">
                        <div class="document-name">${doc.original_name}</div>
                        <div class="document-meta">Uploaded by ${doc.uploader_name} on ${doc.uploaded_at}</div>
                    </div>
                    <a href="${APP_URL}/uploads/${doc.file_path}" class="btn btn-sm btn-secondary" target="_blank">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            `;
        });
        documentsHtml += '</div>';
    } else {
        documentsHtml = '<p class="text-muted">No documents uploaded.</p>';
    }
    
    modalBody.innerHTML = `
        <div class="activity-detail-section">
            <h4>Project Information</h4>
            <div class="detail-row">
                <span class="detail-label">Project</span>
                <span class="detail-value">${activity.project_title}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cycle</span>
                <span class="detail-value">Cycle ${activity.cycle_number}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Step Order</span>
                <span class="detail-value">${activity.step_order}</span>
            </div>
        </div>
        
        <div class="activity-detail-section">
            <h4>Timeline</h4>
            <div class="detail-row">
                <span class="detail-label">Planned Start</span>
                <span class="detail-value">${activity.planned_start_date}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Planned End</span>
                <span class="detail-value">${activity.planned_end_date}</span>
            </div>
            ${activity.actual_completion_date ? `
            <div class="detail-row">
                <span class="detail-label">Actual Completion</span>
                <span class="detail-value">${activity.actual_completion_date}</span>
            </div>
            ` : ''}
        </div>
        
        <div class="activity-detail-section">
            <h4>Status & Compliance</h4>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="status-badge ${statusClass}">${activity.status}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Compliance</span>
                ${complianceHtml}
            </div>
            ${activity.compliance_remarks ? `
            <div class="detail-row">
                <span class="detail-label">Remarks</span>
                <span class="detail-value">${activity.compliance_remarks}</span>
            </div>
            ` : ''}
        </div>
        
        <div class="activity-detail-section">
            <h4>Documents</h4>
            ${documentsHtml}
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="${APP_URL}/admin/activity-view.php?id=${activity.id}" class="btn btn-primary">
                <i class="fas fa-eye"></i> View Full Details
            </a>
        </div>
    `;
}

function closeActivityModal() {
    const modal = document.getElementById('activityModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Confirm Delete
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

/**
 * Show Notification Toast
 */
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;animation:slideIn 0.3s ease;';
    toast.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i><span>${message}</span>`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
// Global APP_URL variable
const APP_URL = '/SDO-BACtrack';

