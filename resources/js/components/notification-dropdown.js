// Notification Dropdown Handler
class NotificationDropdown {
    constructor() {
        this.bellIcon = document.getElementById('notification-bell');
        this.dropdown = document.getElementById('notification-dropdown');
        this.badge = document.getElementById('notification-badge');
        this.notificationList = document.getElementById('notification-list');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        
        // Get CSRF token safely
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        this.csrfToken = csrfMeta ? csrfMeta.content : '';
        
        // Detect if admin or customer - check data attribute first, then URL path
        const wrapper = document.querySelector('.notification-wrapper');
        const userType = wrapper ? wrapper.dataset.userType : null;
        this.isAdmin = userType === 'admin' || window.location.pathname.startsWith('/admin');
        this.baseUrl = this.isAdmin ? '/admin/notifications' : '/notifications';
        
        console.log('NotificationDropdown initialized:', {
            isAdmin: this.isAdmin,
            baseUrl: this.baseUrl,
            bellIcon: !!this.bellIcon
        });
        
        if (!this.bellIcon) return;
        if (!this.csrfToken) {
            console.error('CSRF token not found!');
        }
        
        this.init();
    }
    
    init() {
        // Toggle dropdown on click
        this.bellIcon.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleDropdown();
        });
        
        // Mark all as read
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Mark all as read clicked');
                this.markAllAsRead();
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-wrapper')) {
                this.closeDropdown();
            }
        });
        
        // Load notifications on page load
        this.loadNotifications();
        
        // Auto-refresh every 30 seconds
        setInterval(() => this.loadNotifications(), 30000);
    }
    
    toggleDropdown() {
        const isVisible = this.dropdown.style.display === 'block';
        
        if (isVisible) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    openDropdown() {
        this.dropdown.style.display = 'block';
        this.dropdown.classList.add('show');
        this.dropdown.style.pointerEvents = 'auto';
        this.loadNotifications();
    }
    
    closeDropdown() {
        this.dropdown.style.display = 'none';
        this.dropdown.classList.remove('show');
        this.dropdown.style.pointerEvents = 'none';
    }
    
    async loadNotifications() {
        try {
            const response = await fetch(this.baseUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load notifications');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.unread_count);
                this.renderNotifications(data.notifications);
                this.updateMarkAllButton(data.unread_count);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError();
        }
    }
    
    updateBadge(count) {
        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.style.display = 'flex';
        } else {
            this.badge.style.display = 'none';
        }
    }
    
    updateMarkAllButton(count) {
        if (!this.markAllReadBtn) return;
        
        if (count > 0) {
            this.markAllReadBtn.style.display = 'block';
        } else {
            this.markAllReadBtn.style.display = 'none';
        }
    }
    
    renderNotifications(notifications) {
        if (!notifications || notifications.length === 0) {
            this.showEmptyState();
            return;
        }
        
        const html = notifications.map(notif => this.renderNotification(notif)).join('');
        this.notificationList.innerHTML = html;
        
        // Attach click handlers
        this.attachNotificationHandlers();
    }
    
    renderNotification(notif) {
        const isUnread = !notif.read_at;
        const priorityClass = `priority-${notif.priority}`;
        const unreadClass = isUnread ? 'unread' : '';
        const actionUrl = notif.action_url || '#';
        
        return `
            <div class="notification-item ${unreadClass} ${priorityClass}" data-id="${notif.id}" data-url="${this.escapeHtml(actionUrl)}">
                <div class="notification-icon">
                    ${this.getPriorityIcon(notif.priority)}
                </div>
                <div class="notification-content">
                    <h4 class="notification-title">${this.escapeHtml(notif.title)}</h4>
                    <p class="notification-message">${this.escapeHtml(notif.message)}</p>
                    <span class="notification-time">${notif.created_at}</span>
                </div>
                ${isUnread ? '<div class="notification-dot"></div>' : ''}
            </div>
        `;
    }
    
    getPriorityIcon(priority) {
        switch (priority) {
            case 'high':
                return '<i class="fas fa-exclamation-circle text-red"></i>';
            case 'medium':
                return '<i class="fas fa-info-circle text-yellow"></i>';
            default:
                return '<i class="fas fa-bell text-blue"></i>';
        }
    }
    
    showEmptyState() {
        this.notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>Tidak ada notifikasi</p>
            </div>
        `;
    }
    
    showError() {
        this.notificationList.innerHTML = `
            <div class="notification-error">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Gagal memuat notifikasi</p>
                <button onclick="notificationDropdown.loadNotifications()">Coba Lagi</button>
            </div>
        `;
    }
    
    attachNotificationHandlers() {
        const items = this.notificationList.querySelectorAll('.notification-item');
        
        items.forEach(item => {
            item.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const id = item.dataset.id;
                const url = item.dataset.url;
                
                // Mark as read first and wait for completion
                try {
                    await this.markAsRead(id, item);
                } catch (error) {
                    console.error('Error marking as read:', error);
                }
                
                // Then redirect if URL exists and is not '#'
                if (url && url !== '#') {
                    window.location.href = url;
                }
            });
        });
    }
    
    async markAsRead(id, element) {
        console.log('markAsRead called:', { id, url: `${this.baseUrl}/${id}/read` });
        try {
            const response = await fetch(`${this.baseUrl}/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('markAsRead response:', response.status, response.ok);
            
            if (response.ok) {
                // Update UI immediately
                if (element) {
                    element.classList.remove('unread');
                    const dot = element.querySelector('.notification-dot');
                    if (dot) dot.remove();
                }
                
                // Update badge count immediately
                if (this.badge) {
                    const currentCount = parseInt(this.badge.textContent) || 0;
                    if (currentCount > 0) {
                        const newCount = currentCount - 1;
                        if (newCount > 0) {
                            this.badge.textContent = newCount;
                        } else {
                            this.badge.style.display = 'none';
                        }
                    }
                }
                
                return true;
            } else {
                const errorText = await response.text();
                console.error('markAsRead failed:', response.status, errorText);
            }
            return false;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    }
    
    async markAllAsRead() {
        console.log('markAllAsRead called:', { url: `${this.baseUrl}/read-all` });
        try {
            const response = await fetch(`${this.baseUrl}/read-all`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('markAllAsRead response:', response.status, response.ok);
            
            if (response.ok) {
                // Update UI immediately
                if (this.badge) {
                    this.badge.style.display = 'none';
                    this.badge.textContent = '0';
                }
                
                // Remove unread class from all items
                const items = this.notificationList.querySelectorAll('.notification-item.unread');
                items.forEach(item => {
                    item.classList.remove('unread');
                    const dot = item.querySelector('.notification-dot');
                    if (dot) dot.remove();
                });
                
                // Hide mark all read button
                if (this.markAllReadBtn) {
                    this.markAllReadBtn.style.display = 'none';
                }
                
                return true;
            } else {
                const errorText = await response.text();
                console.error('markAllAsRead failed:', response.status, errorText);
            }
            return false;
        } catch (error) {
            console.error('Error marking all as read:', error);
            return false;
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize on DOM ready and also try immediately in case DOM is already ready
function initializeNotificationDropdown() {
    const bellIcon = document.getElementById('notification-bell');
    if (bellIcon && !window.notificationDropdown) {
        console.log('Initializing NotificationDropdown...');
        window.notificationDropdown = new NotificationDropdown();
    }
}

// Try to initialize immediately
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNotificationDropdown);
} else {
    // DOM is already ready
    initializeNotificationDropdown();
}

// Also initialize on page load as fallback
window.addEventListener('load', initializeNotificationDropdown);
