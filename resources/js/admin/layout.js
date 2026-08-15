// Toggle admin dropdown menu
window.toggleAdminDropdown = function() {
    console.log('toggleAdminDropdown called');
    
    const menu = document.getElementById('adminDropdownMenu');
    if (!menu) {
        console.error('adminDropdownMenu element not found!');
        return;
    }
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    menu.classList.toggle('show');
    // Ensure pointer-events is enabled
    if (menu.style.display === 'block') {
        menu.style.pointerEvents = 'auto';
    }

    // Close notification dropdown if open
    const notifDropdown = document.getElementById('notification-dropdown');
    if (notifDropdown && notifDropdown.style.display === 'block') {
        notifDropdown.style.display = 'none';
    }
};

// Toggle notification dropdown
window.toggleNotificationDropdown = function() {
    console.log('toggleNotificationDropdown called, notificationDropdown exists:', !!window.notificationDropdown);
    
    const dropdown = document.getElementById('notification-dropdown');
    if (!dropdown) {
        console.error('notification-dropdown element not found!');
        return;
    }
    
    // If NotificationDropdown class exists, use its method
    if (window.notificationDropdown && typeof window.notificationDropdown.toggleDropdown === 'function') {
        console.log('Using NotificationDropdown.toggleDropdown()');
        window.notificationDropdown.toggleDropdown();
    } else {
        // Fallback to direct DOM manipulation
        console.log('Using fallback DOM manipulation');
        const isVisible = dropdown.style.display === 'block';
        dropdown.style.display = isVisible ? 'none' : 'block';
        dropdown.classList.toggle('show');
        
        if (dropdown.style.display === 'block') {
            dropdown.style.pointerEvents = 'auto';
            // Try to load notifications if class exists
            if (window.notificationDropdown && typeof window.notificationDropdown.loadNotifications === 'function') {
                window.notificationDropdown.loadNotifications();
            }
        }
    }

    // Close admin dropdown if open
    const adminMenu = document.getElementById('adminDropdownMenu');
    if (adminMenu && adminMenu.style.display === 'block') {
        adminMenu.style.display = 'none';
    }
};

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    // Close admin dropdown when clicking outside
    if (!e.target.closest('.admin-dropdown')) {
        const menu = document.getElementById('adminDropdownMenu');
        if (menu && menu.style.display === 'block') {
            menu.style.display = 'none';
        }
    }

    // Close notification dropdown when clicking outside
    if (!e.target.closest('.notification-wrapper')) {
        const notifDropdown = document.getElementById('notification-dropdown');
        if (notifDropdown && notifDropdown.style.display === 'block') {
            notifDropdown.style.display = 'none';
        }
    }
});

window.addEventListener('admin-profile-updated', function (event) {
	const newAvatarUrl = event.detail.avatarUrl;
	document.querySelectorAll('.admin-avatar').forEach(img => {
		img.src = newAvatarUrl;
	});

	const placeholder = document.querySelector('.admin-avatar-placeholder');
	if (placeholder && newAvatarUrl) {
		const img = document.createElement('img');
		img.src = newAvatarUrl;
		img.alt = 'Avatar';
		img.className = 'admin-avatar';
		placeholder.replaceWith(img);
	}
});