import './bootstrap';
import './notification-poller';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { purchaseDateRange } from './purchase-date-range';
import { bootAddressAutocomplete } from './address-autocomplete';
import { salesForm, saleReturnForm } from './sales-form';

Alpine.plugin(collapse);
window.Alpine = Alpine;

// Global System Reactivity
document.addEventListener('alpine:init', () => {
    Alpine.data('purchaseDateRange', purchaseDateRange);
    Alpine.data('salesForm', salesForm);
    Alpine.data('saleReturnForm', saleReturnForm);
    
    Alpine.store('system', {
        notifications: [],
        addNotification(data) {
            this.notifications.push({
                id: Date.now(),
                ...data
            });
            // Auto remove after 5 seconds
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== data.id);
            }, 5000);
        }
    });
});

function setupEchoSubscriptions() {
    if (!window.Echo) return;

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ Connected to Reverb WebSocket Server');
    });

    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.warn('⚠️ Reverb WebSocket Connection Error:', err);
    });

    // Sales real-time updates
    window.Echo.private('sales')
        .listen('.SaleCreated', (data) => {
            console.log('Realtime Sale Created (dot):', data);
            window.dispatchEvent(new CustomEvent('sale:created', { detail: data }));
            if (window.showToast) {
                const invoice = data.invoice_no || ('#' + data.id);
                const sp = data.salesperson_name ? ` by ${data.salesperson_name}` : '';
                window.showToast(`New sale ${invoice} recorded${sp}!`, 'success', 6000);
            }
        })
        .listen('SaleCreated', (data) => {
            console.log('Realtime Sale Created:', data);
            window.dispatchEvent(new CustomEvent('sale:created', { detail: data }));
            if (window.showToast) {
                const invoice = data.invoice_no || ('#' + data.id);
                const sp = data.salesperson_name ? ` by ${data.salesperson_name}` : '';
                window.showToast(`New sale ${invoice} recorded${sp}!`, 'success', 6000);
            }
        });

    // Purchases real-time updates
    window.Echo.private('purchases')
        .listen('.PurchaseCreated', (data) => {
            console.log('Realtime Purchase Created (dot):', data);
            window.dispatchEvent(new CustomEvent('purchase:created', { detail: data }));
            if (window.showToast) {
                const invoice = data.invoice_no || ('#' + data.id);
                const supplier = data.supplier_name ? ` from ${data.supplier_name}` : '';
                window.showToast(`New purchase ${invoice} recorded${supplier}!`, 'info', 6000);
            }
        })
        .listen('PurchaseCreated', (data) => {
            console.log('Realtime Purchase Created:', data);
            window.dispatchEvent(new CustomEvent('purchase:created', { detail: data }));
            if (window.showToast) {
                const invoice = data.invoice_no || ('#' + data.id);
                const supplier = data.supplier_name ? ` from ${data.supplier_name}` : '';
                window.showToast(`New purchase ${invoice} recorded${supplier}!`, 'info', 6000);
            }
        });

    window.Echo.channel('system-updates')
        .listen('.model.changed', (data) => {
            console.log('System Update (dot):', data);
            window.dispatchEvent(new CustomEvent('system:update', { detail: data }));
            handleSystemUpdate(data);
        })
        .listen('ModelChanged', (data) => {
            console.log('System Update:', data);
            window.dispatchEvent(new CustomEvent('system:update', { detail: data }));
            handleSystemUpdate(data);
        })
        .listen('model.changed', (data) => {
            console.log('System Update (plain):', data);
            window.dispatchEvent(new CustomEvent('system:update', { detail: data }));
            handleSystemUpdate(data);
        });
}

/**
 * Map model class names to their URL path segments (from routes/web.php).
 * When a change fires for a model, the current URL is checked against these paths.
 * A page auto-reloads if any path in the array is found in window.location.pathname.
 */
const MODEL_ROUTE_MAP = {
    // Sales
    'Sale':                  ['/sales'],
    'SaleReturn':            ['/sales/return', '/sales-returns'],
    'SalePayment':           ['/sales'],
    'SaleItem':              [],   // auto-reload via parent Sale

    // Purchases
    'Purchase':              ['/purchases'],
    'PurchaseReturn':        ['/purchases/return'],
    'PurchasePayment':       ['/purchases'],
    'PurchaseItem':          [],   // auto-reload via parent Purchase

    // Products & Inventory
    'Product':               ['/products'],
    'ProductBatch':          ['/products', '/batches'],
    'Category':              ['/products/categories'],
    'Brand':                 ['/products/brands'],
    'Unit':                  ['/products/units'],
    'StoreCategory':         ['/store-categories'],

    // Customers & Suppliers
    'Customer':              ['/customers'],
    'Supplier':              ['/suppliers'],

    // Adjustments
    'Adjustment':            ['/adjustments'],
    'AdjustmentItem':        ['/adjustments'],

    // Expenses
    'Expense':               ['/expenses'],
    'ExpenseType':           ['/expense_types'],

    // Staff Management
    'User':                  ['/staff/all'],
    'Role':                  ['/staff/roles'],
    'StaffTarget':           ['/staff/performance'],

    // Reports — reload report views when underlying data changes
    'SalePayment':           ['/sales', '/reports/payments/customer', '/customer-payments', '/reports/data-entry/sales'],
    'PurchasePayment':       ['/purchases', '/reports/payments/supplier', '/supplier-payments', '/reports/data-entry/purchases'],
    'StockLedgerEntry':      ['/reports/stock', '/products', '/batches'],
};

// These sub-models trigger a silent reload (no toast, just reload quietly)
const SILENT_RELOAD_MODELS = ['SaleItem', 'SalePayment', 'PurchaseItem', 'PurchasePayment', 'AdjustmentItem'];

// Track last reload time to prevent rapid successive reloads
let lastReloadTime = 0;
const RELOAD_COOLDOWN_MS = 3000;

function handleSystemUpdate(data) {
    const model = data.model;
    const action = data.action;
    const currentPath = window.location.pathname.toLowerCase();

    // Skip internal join models and logs
    if (['SaleReturnItem', 'PurchaseReturnItem', 'SaleItemBatch', 'SaleReturnItemBatch',
         'PurchaseReturnItemBatch', 'BatchStockLog',
         'NotificationLog', 'ActivityLog', 'UserAppearanceSetting'].includes(model)) {
        return;
    }

    const routes = MODEL_ROUTE_MAP[model] || [];
    const isOnRelevantPage = routes.some(route => currentPath.includes(route));
    const isSilent = SILENT_RELOAD_MODELS.includes(model);

    const actionLabel = action === 'created' ? 'added' : action === 'updated' ? 'updated' : 'deleted';
    const modelLabel = model.replace(/([A-Z])/g, ' $1').trim(); // "SaleReturn" → "Sale Return"

    if (isOnRelevantPage) {
        // Do not auto-reload if the user is currently editing or creating on a form page
        if (currentPath.includes('/create') || currentPath.includes('/edit')) {
            if (window.showToast) {
                window.showToast(`${modelLabel} was ${actionLabel} in the background.`, 'info', 4000);
            }
            return;
        }

        const now = Date.now();
        const canReload = (now - lastReloadTime) > RELOAD_COOLDOWN_MS;

        if (canReload) {
            lastReloadTime = now;

            if (isSilent) {
                // Silent reload for sub-items (payments, line items)
                window.location.reload();
            } else if (action === 'created') {
                // Auto-reload on create so the new row appears instantly
                window.location.reload();
            } else if (action === 'updated') {
                // For Sale or other primary models updated (like confirmed/rejected), reload right away
                if (model === 'Sale') {
                    window.location.reload();
                } else {
                    if (window.showToast) {
                        window.showToast(`${modelLabel} was updated — refreshing…`, 'info', 1500);
                    }
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else if (action === 'deleted') {
                // For deletes, reload immediately
                if (window.showToast) {
                    window.showToast(`${modelLabel} was deleted — refreshing…`, 'warning', 1500);
                }
                setTimeout(() => window.location.reload(), 800);
            }
        }
    } else {
        // Not on the relevant page — show a subtle info toast only for major models
        const MAJOR_MODELS = ['Sale', 'Purchase', 'SaleReturn', 'PurchaseReturn', 'Product',
                               'Customer', 'Supplier', 'Adjustment', 'Expense', 'User', 'Role', 'StaffTarget'];
        if (MAJOR_MODELS.includes(model) && window.showToast) {
            window.showToast(`${modelLabel} ${actionLabel}.`, 'info', 4000);
        }
    }
}

setupEchoSubscriptions();

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    bootAddressAutocomplete();
});

/**
 * Global notification action functions
 * These are called from notification dropdown UI elements
 */

// Toggle notification menu for dynamically added elements
window.toggleNotificationMenu = function(menuId, menuBtnId, event) {
    event.stopPropagation();
    const menu = document.getElementById(menuId);
    if (menu) {
        menu.classList.toggle('hidden');
    }
    
    // Close menu if clicking outside
    document.addEventListener('click', function closeMenu(e) {
        const btn = document.getElementById(menuBtnId);
        if (menu && btn && !menu.contains(e.target) && !btn.contains(e.target)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    });
};

// Handle notification click - mark as read and navigate
window.notificationClickHandler = async function(notificationId, link, event) {
    // Prevent any bubbling
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    // Mark as read
    try {
        await fetch(`/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        });

        // Update UI
        const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (element) {
            element.dataset.isRead = 'true';
            element.classList.remove('bg-blue-50/50', 'dark:bg-blue-900/10', 'bg-red-50/50', 'dark:bg-red-900/10', 'bg-amber-50/50', 'dark:bg-amber-900/10');
            element.classList.add('bg-white', 'dark:bg-gray-800');
            
            const unreadIndicator = element.querySelector('[data-unread-indicator]');
            if (unreadIndicator) unreadIndicator.remove();
            
            const markReadBtn = element.querySelector('[data-mark-read-btn]');
            if (markReadBtn) markReadBtn.classList.add('hidden');
        }
        
        // Update badge
        const badge = document.querySelector('[data-notification-badge]');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            const newCount = Math.max(0, currentCount - 1);
            if (newCount > 0) {
                badge.textContent = newCount > 99 ? '99+' : newCount;
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }

    // Navigate to the appropriate page
    setTimeout(() => {
        window.location.href = link;
    }, 100);
};

// Mark a single notification as read
window.markAsRead = async function(notificationId, button) {
    try {
        const response = await fetch(`/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (element) {
                element.dataset.isRead = 'true';
                element.classList.remove('bg-blue-50/50', 'dark:bg-blue-900/10');
                element.classList.add('bg-white', 'dark:bg-gray-800');
                
                const unreadIndicator = element.querySelector('[data-unread-indicator]');
                if (unreadIndicator) unreadIndicator.remove();
                
                const markReadBtn = element.querySelector('[data-mark-read-btn]');
                if (markReadBtn) markReadBtn.classList.add('hidden');
            }
            
            // Update badge
            const badge = document.querySelector('[data-notification-badge]');
            if (badge) {
                const currentCount = parseInt(badge.textContent) || 0;
                const newCount = Math.max(0, currentCount - 1);
                if (newCount > 0) {
                    badge.textContent = newCount > 99 ? '99+' : newCount;
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

// Custom delete confirmation modal (uses the global-delete-modal in app.blade.php)
window.showDeleteConfirm = function(name) {
    const modal = document.getElementById('global-delete-modal');
    const nameEl = document.getElementById('global-delete-name');

    // If the blade modal exists, use it
    if (modal && nameEl) {
        return new Promise(resolve => {
            window._deleteResolve = resolve;
            nameEl.textContent = name;
            modal.style.display = '';
        });
    }

    // Fallback to native confirm if modal isn't in the DOM yet
    return Promise.resolve(confirm(`Are you sure you want to delete ${name}? This action cannot be undone.`));
};

// Delete a single notification
window.deleteNotification = async function(notificationId, button) {
    const confirmed = await showDeleteConfirm('this notification');
    if (!confirmed) return;

    try {
        const response = await fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            // Remove all DOM elements with this notification id (dropdown + page)
            const elements = document.querySelectorAll(`[data-notification-id="${notificationId}"]`);
            elements.forEach(el => {
                const wasUnread = el.dataset.isRead === 'false';
                el.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                el.style.opacity = '0';
                el.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    el.remove();
                    // If page list is now empty, reload to show empty state
                    const pageList = document.getElementById('full-notification-list');
                    if (pageList && pageList.children.length === 0) location.reload();
                }, 300);

                // Decrement badge if it was unread
                if (wasUnread) {
                    const badge = document.querySelector('[data-notification-badge]');
                    if (badge) {
                        let count = parseInt(badge.textContent.replace('+', '')) || 0;
                        count = Math.max(0, count - 1);
                        if (count > 0) {
                            badge.textContent = count > 99 ? '99+' : count;
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error deleting notification:', error);
    }
};

// Mark all notifications as read
window.markAllNotificationsAsRead = async function(button) {
    try {
        const response = await fetch('/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            // Update all notification elements
            document.querySelectorAll('[data-notification-id]').forEach(element => {
                element.dataset.isRead = 'true';
                element.classList.remove('bg-blue-50/50', 'bg-red-50/50', 'bg-amber-50/50');
                element.classList.remove('dark:bg-blue-900/10', 'dark:bg-red-900/10', 'dark:bg-amber-900/10');
                element.classList.add('bg-white', 'dark:bg-gray-800');
                
                const unreadIndicator = element.querySelector('[data-unread-indicator]');
                if (unreadIndicator) unreadIndicator.remove();
                
                const markReadBtn = element.querySelector('[data-mark-read-btn]');
                if (markReadBtn) markReadBtn.classList.add('hidden');
            });
            
            // Hide badge
            const badge = document.querySelector('[data-notification-badge]');
            if (badge) badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

// Delete all notifications
window.deleteAllNotifications = async function(button) {
    const confirmed = await showDeleteConfirm('all notifications');
    if (!confirmed) return;
    
    try {
        const response = await fetch('/notifications/delete-all', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const notificationList = document.getElementById('notification-list');
            if (notificationList) {
                notificationList.innerHTML = `
                    <div class="p-12 text-center bg-white dark:bg-gray-800" id="empty-notification-state">
                        <div class="text-gray-200 dark:text-gray-700 mb-3">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4m8 4v6" />
                            </svg>
                        </div>
                        <p class="text-gray-400 dark:text-gray-500 text-sm font-medium">Inbox is empty</p>
                    </div>
                `;
            }
            
            // Hide badge
            const badge = document.querySelector('[data-notification-badge]');
            if (badge) badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error deleting all notifications:', error);
    }
};
