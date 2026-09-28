/**
 * Real-time notification polling system
 * Continuously checks for new unread notifications and updates UI
 */

class NotificationPoller {
    constructor() {
        this.lastNotificationId = 0;
        this.isPolling = false;
        this.pollInterval = 15000; // 15 seconds (reduced server strain)
        this.maxRetries = 3;
        this.retryCount = 0;

        // Pause polling when browser tab is inactive/hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stop();
            } else {
                this.start();
            }
        });
    }

    /**
     * Start polling for new notifications
     */
    start() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.getLatestNotificationId();
        this.poll();
    }

    /**
     * Stop polling
     */
    stop() {
        this.isPolling = false;
    }


    /**
     * Get the latest notification ID from the DOM
     */
    getLatestNotificationId() {
        const notifications = document.querySelectorAll('[data-notification-id]');
        if (notifications.length > 0) {
            this.lastNotificationId = Math.max(
                ...Array.from(notifications).map(n => parseInt(n.dataset.notificationId))
            );
        }
    }

    /**
     * Main polling function
     */
    poll() {
        if (!this.isPolling) return;

        this.checkForNewNotifications()
            .then(() => {
                this.retryCount = 0;
                // Schedule next poll
                setTimeout(() => this.poll(), this.pollInterval);
            })
            .catch((error) => {
                console.warn('Notification poll error:', error);
                this.retryCount++;
                
                if (this.retryCount < this.maxRetries) {
                    setTimeout(() => this.poll(), this.pollInterval * 2);
                }
            });
    }

    /**
     * Check for new notifications via API
     */
    async checkForNewNotifications() {
        const response = await fetch(
            `/notifications/unread-count?last_id=${this.lastNotificationId}`,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }
        );


        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.new_notifications && data.new_notifications.length > 0) {
            // Process new notifications
            data.new_notifications.forEach(notification => {
                this.addNotificationToList(notification);
                this.showNotificationToast(notification);
                this.lastNotificationId = Math.max(this.lastNotificationId, notification.id);
            });

            // Update badge count
            this.updateBadgeCount(data.unread_count);
        }

        return data;
    }

    /**
     * Add notification to the dropdown list
     */
    addNotificationToList(notification) {
        const notificationList = document.getElementById('notification-list');
        const emptyState = document.getElementById('empty-notification-state');

        if (!notificationList) return;

        // Remove empty state if it exists
        if (emptyState) {
            emptyState.remove();
        }

        // Create notification element
        const notifElement = this.createNotificationElement(notification);

        // Prepend to list (most recent first)
        notificationList.insertAdjacentHTML('afterbegin', notifElement);

        // Keep only the first 15 notifications in the dropdown
        const notifications = notificationList.querySelectorAll('[data-notification-id]');
        if (notifications.length > 15) {
            notifications[15].remove();
        }
    }

    /**
     * Determine the target URL based on notification type
     */
    getNotificationLink(notification) {
        const title = notification.title.toLowerCase();
        const message = notification.message.toLowerCase();
        
        // Check for sale-related notifications
        if (title.includes('sale') || title.includes('approval')) {
            return '/sales/all';
        }
        
        // Check for stock-related notifications
        if (title.includes('stock') || message.includes('stock') || message.includes('low stock')) {
            return '/products/all';
        }
        
        // Default fallback (notifications page)
        return '/notifications';
    }

    /**
     * Create HTML for a notification element
     */
    createNotificationElement(notification) {
        const isRead = notification.is_read ? 'true' : 'false';
        const severity = notification.severity || 'info';
        const bgColor = isRead 
            ? 'bg-white dark:bg-gray-800' 
            : (severity === 'danger' 
                ? 'bg-red-50/50 dark:bg-red-900/10' 
                : (severity === 'warning' 
                    ? 'bg-amber-50/50 dark:bg-amber-900/10' 
                    : 'bg-blue-50/50 dark:bg-blue-900/10'));

        const timeago = this.formatTimeAgo(new Date(notification.created_at));
        const notificationLink = this.getNotificationLink(notification);
        const menuId = `menu-${notification.id}`;
        const menuBtnId = `menu-btn-${notification.id}`;

        return `
            <div class="border-b border-gray-100 dark:border-gray-700 transition-colors flex items-start gap-2 ${bgColor}" 
                 data-notification-id="${notification.id}" 
                 data-is-read="${isRead}">
                
                <!-- Clickable notification content - Full width minus menu -->
                <div class="flex-1 p-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" 
                     onclick="notificationClickHandler('${notification.id}', '${notificationLink}', event)">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 pt-1">
                            ${!notification.is_read ? '<div class="w-2 h-2 bg-blue-600 rounded-full" data-unread-indicator></div>' : ''}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate ${isRead ? 'opacity-60' : ''}">
                                ${this.escapeHtml(notification.title)}
                            </p>
                            <p class="text-gray-600 dark:text-gray-400 text-xs line-clamp-2 mt-0.5 ${isRead ? 'opacity-60' : ''}">
                                ${this.escapeHtml(notification.message)}
                            </p>
                            <p class="text-gray-400 dark:text-gray-500 text-[10px] mt-1 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                ${timeago}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Action menu button - Vanilla JS based for dynamic elements -->
                <div class="flex-shrink-0 p-3 relative" onclick="event.stopPropagation();">
                    <button id="${menuBtnId}" onclick="toggleNotificationMenu('${menuId}', '${menuBtnId}', event)" 
                            class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </button>
                    <div id="${menuId}" class="hidden absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-xl z-[210] py-1">
                        <button onclick="markAsRead('${notification.id}', this)" 
                                class="w-full text-left px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center ${isRead ? 'hidden' : ''}"
                                data-mark-read-btn>
                            <svg class="w-3.5 h-3.5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mark as read
                        </button>
                        <button onclick="deleteNotification('${notification.id}', this)" 
                                class="w-full text-left px-3 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show a toast notification for new alert
     */
    showNotificationToast(notification) {
        if (window.showToast) {
            const typeMap = {
                'danger': 'error',
                'warning': 'warning',
                'info': 'info',
                'success': 'success'
            };
            
            const type = typeMap[notification.severity] || 'info';
            window.showToast(
                `<strong>${this.escapeHtml(notification.title)}</strong><br>${this.escapeHtml(notification.message)}`,
                type,
                5000
            );
        }
    }

    /**
     * Update the notification badge count
     */
    updateBadgeCount(count) {
        const badge = document.querySelector('[data-notification-badge]');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Format timestamp to relative time string
     */
    formatTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        if (seconds < 60) return 'now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }

    /**
     * Escape HTML special characters
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize and start polling when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const poller = new NotificationPoller();
    poller.start();
    
    // Make it globally accessible for debugging
    window.notificationPoller = poller;
});
