/**
 * SDO-BACtrack Notifications System
 * Handles floating toasts and real-time notification polling.
 */

class Toast {
    static container = null;

    static init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    }

    static show(title, message, type = 'info', duration = 10000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;

        const iconClass = this.getIcon(type);

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas fa-${iconClass}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">&times;</button>
            <div class="toast-progress">
                <div class="toast-progress-fill" style="animation: toast-progress ${duration}ms linear forwards"></div>
            </div>
        `;

        this.container.appendChild(toast);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.hide(toast);
        });

        // Auto hide
        if (duration > 0) {
            setTimeout(() => {
                this.hide(toast);
            }, duration);
        }

        return toast;
    }

    static hide(toast) {
        toast.classList.add('hide');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }

    static getIcon(type) {
        switch (type) {
            case 'success': return 'check-circle';
            case 'danger':
            case 'error': return 'exclamation-circle';
            case 'warning': return 'exclamation-triangle';
            case 'info':
            default: return 'info-circle';
        }
    }
}

// Global Toast shorthand
window.showToast = (title, message, type, duration) => Toast.show(title, message, type, duration);

/**
 * Real-time notification polling
 */
const NotificationSystem = {
    lastNotificationId: 0,
    pollInterval: 30000, // 30 seconds

    init() {
        // Find the latest notification ID currently in the DOM (from header)
        const firstNotification = document.querySelector('.notification-item');
        if (firstNotification) {
            // This is a bit brittle, we might need a better way to track "new"
            // For now, we'll just track if we've shown a toast for it.
        }

        // Start polling
        setInterval(() => this.poll(), this.pollInterval);
        
        // Initial poll after short delay
        setTimeout(() => this.poll(), 2000);
    },

    async poll() {
        try {
            const response = await fetch(`${window.APP_URL}/admin/api/unread-notifications.php`);
            if (!response.ok) return;

            const data = await response.json();
            if (data.unread && data.unread.length > 0) {
                data.unread.forEach(notification => {
                    // Only show toast if it's "new" (not seen in this session)
                    if (this.isNew(notification.id)) {
                        const type = this.mapType(notification.type);
                        window.showToast(notification.title, notification.message, type);
                        this.updateUI(notification);
                    }
                });
            }
        } catch (error) {
            console.error('Notification polling error:', error);
        }
    },

    isNew(id) {
        const seen = JSON.parse(sessionStorage.getItem('seen_notifications') || '[]');
        if (seen.includes(id)) return false;
        
        seen.push(id);
        sessionStorage.setItem('seen_notifications', JSON.stringify(seen));
        return true;
    },

    mapType(type) {
        switch (type) {
            case 'DEADLINE_WARNING': return 'warning';
            case 'ACTIVITY_DELAYED': return 'danger';
            case 'DOCUMENT_UPLOADED': return 'info';
            case 'ADJUSTMENT_REQUEST': return 'warning';
            case 'ADJUSTMENT_RESPONSE': return 'success';
            case 'PROJECT_REJECTED': return 'danger';
            default: return 'info';
        }
    },

    updateUI(notification) {
        // Update the badge count
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            let count = parseInt(badge.textContent) || 0;
            badge.textContent = count + 1;
            badge.style.display = 'flex';
        } else {
            const btn = document.querySelector('.notification-btn');
            if (btn) {
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge';
                newBadge.textContent = '1';
                btn.appendChild(newBadge);
            }
        }

        // Add to dropdown list if it exists
        const list = document.querySelector('.notification-list');
        if (list) {
            const empty = list.querySelector('.notification-empty');
            if (empty) empty.remove();

            const item = document.createElement('a');
            item.href = `${window.APP_URL}/admin/activity-view.php?id=${notification.reference_id}`;
            item.className = 'notification-item unread';
            
            const icon = this.getIconForType(notification.type);
            
            item.innerHTML = `
                <div class="notification-icon ${notification.type.toLowerCase()}">
                    <i class="fas fa-${icon}"></i>
                </div>
                <div class="notification-content">
                    <strong>${notification.title}</strong>
                    <p>${notification.message}</p>
                    <span class="notification-time">Just now</span>
                </div>
            `;
            list.insertBefore(item, list.firstChild);
        }
    },

    getIconForType(type) {
        switch (type) {
            case 'DEADLINE_WARNING': return 'clock';
            case 'ACTIVITY_DELAYED': return 'exclamation-triangle';
            case 'DOCUMENT_UPLOADED': return 'file-upload';
            case 'ADJUSTMENT_REQUEST': return 'calendar-plus';
            case 'ADJUSTMENT_RESPONSE': return 'calendar-check';
            case 'PROJECT_REJECTED': return 'times-circle';
            default: return 'bell';
        }
    }
};

// Start the system
document.addEventListener('DOMContentLoaded', () => {
    NotificationSystem.init();
});
