/**
 * SDO-BACtrack Notifications System
 * Handles real-time notification polling.
 */

/**
 * Real-time notification polling
 */
const NotificationSystem = {
    lastNotificationId: 0,
    pollInterval: 30000, // 30 seconds

    init() {
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
                    // Only process if it's "new" (not seen in this session)
                    if (this.isNew(notification.id)) {
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
