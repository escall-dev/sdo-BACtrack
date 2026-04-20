/**
 * SDO-BACtrack Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initContextMenuBlock();
    initSidebar();
    initFlashMessages();
    initNotifications();
    initNotificationPolling();
    initModals();
    initFilterEnterKey();
});

function initContextMenuBlock() {
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    });
}

/**
 * Filter Bar - Submit on Enter key
 */
function initFilterEnterKey() {
    document.querySelectorAll('.filter-form input, .filter-form select').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var form = el.closest('form');
                if (form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            }
        });
    });
}

/**
 * Sidebar Toggle
 */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const adminLayout = document.querySelector('.admin-layout');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const body = document.body;

    if (!sidebar || !adminLayout) {
        return;
    }

    function setMobileSidebarOpen(isOpen) {
        sidebar.classList.toggle('open', isOpen);
        body.classList.toggle('sidebar-open', isOpen);
    }
    
    // Restore sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed && window.innerWidth >= 992) {
        sidebar.classList.add('collapsed');
        adminLayout.classList.add('sidebar-collapsed');
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            setMobileSidebarOpen(!sidebar.classList.contains('open'));
        });
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            adminLayout.classList.toggle('sidebar-collapsed', isCollapsed);
            localStorage.setItem('sidebarCollapsed', isCollapsed);

            // Delay resize dispatch until the width transition settles.
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
            }, 220);
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            if (mobileToggle && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                setMobileSidebarOpen(false);
            }
        }
    });

    // Keep mobile sidebar state clean when switching breakpoints.
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            setMobileSidebarOpen(false);
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
/**
 * Notification Sound + Polling
 * Polls every 30 s. Plays a chime when the unread count increases.
 */
function initNotificationPolling() {
    // Build the API URL using the same helper pattern used elsewhere
    var base = (typeof APP_URL !== 'undefined' ? APP_URL : '') + '/admin/api/notification-count.php';
    var apiUrl = (window.SDO_BACTRACK_buildApiUrl ? window.SDO_BACTRACK_buildApiUrl(base) : base);

    // Read the initial count already rendered by PHP so first poll never false-fires
    var badge = document.querySelector('.notification-badge');
    var lastCount = badge ? (parseInt(badge.textContent, 10) || 0) : 0;

    // Synthesise a short double-chime using Web Audio API (no external file needed)
    function playChime() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();

            function beep(freq, startTime, duration) {
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, startTime);
                gain.gain.setValueAtTime(0, startTime);
                gain.gain.linearRampToValueAtTime(0.25, startTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
                osc.start(startTime);
                osc.stop(startTime + duration);
            }

            var t = ctx.currentTime;
            beep(880, t,        0.18);   // first note  (A5)
            beep(1100, t + 0.2, 0.22);   // second note (C#6)
        } catch (e) {
            // Web Audio not supported — silent fallback
        }
    }

    function poll() {
        fetch(apiUrl, { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var count = data.unread || 0;
                if (count > lastCount) {
                    playChime();

                    // Update the badge in the header without a full reload
                    var btn = document.getElementById('notificationBtn');
                    if (btn) {
                        var existing = btn.querySelector('.notification-badge');
                        if (count > 0) {
                            if (existing) {
                                existing.textContent = count > 99 ? '99+' : count;
                            } else {
                                var span = document.createElement('span');
                                span.className = 'notification-badge';
                                span.textContent = count > 99 ? '99+' : count;
                                btn.appendChild(span);
                            }
                        }
                    }
                } else if (count === 0) {
                    // All read — remove badge
                    var existing = document.querySelector('.notification-badge');
                    if (existing) existing.remove();
                }
                lastCount = count;
            })
            .catch(function() { /* network error — skip silently */ });
    }

    // First poll after 30 s; repeat every 30 s
    setInterval(poll, 30000);
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

function getActivityViewUrl(activityId) {
    var baseUrl = (typeof APP_URL !== 'undefined' ? APP_URL : '/SDO-BACtrack') + '/admin/activity-view.php?id=' + activityId;
    if (window.SDO_BACTRACK_buildPageUrl) {
        return window.SDO_BACTRACK_buildPageUrl(baseUrl);
    }
    if (window.SDO_BACTRACK_buildApiUrl) {
        return window.SDO_BACTRACK_buildApiUrl(baseUrl);
    }
    return baseUrl;
}

function navigateToActivityView(activityId) {
    var url = getActivityViewUrl(activityId);
    window.location.href = url;
}

function openActivityModal(activityId) {
    const modal = document.getElementById('activityModal');
    const modalBody = document.getElementById('modalBody');
    
    modalBody.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    modal.classList.add('show');
    
    var url = APP_URL + '/admin/api/activity-detail.php?id=' + activityId;
    if (window.SDO_BACTRACK_buildApiUrl) {
        url = window.SDO_BACTRACK_buildApiUrl(url);
    }
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && (data.id || data.step_name)) {
                renderActivityModal(data);
            } else if (data && data.error) {
                modalBody.innerHTML = '<p class="text-danger">' + (data.error || 'Failed to load activity details.') + '</p>';
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
            <button type="button" class="btn btn-primary" onclick="navigateToActivityView(${activity.id})">
                <i class="fas fa-eye"></i> View Full Details
            </button>
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

