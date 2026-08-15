// Admin-specific JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin dropdown
    setupAdminDropdown();
    
    // Initialize sidebar toggle for mobile
    setupMobileSidebar();
});

// Admin dropdown functionality
function setupAdminDropdown() {
    const dropdownBtn = document.querySelector('.admin-dropdown__btn');
    const dropdownMenu = document.getElementById('adminDropdownMenu');
    
    if (!dropdownBtn || !dropdownMenu) return;
    
    // Toggle dropdown menu
    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isExpanded);
        dropdownMenu.style.display = isExpanded ? 'none' : 'block';
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownBtn.setAttribute('aria-expanded', 'false');
            dropdownMenu.style.display = 'none';
        }
    });
}

// Mobile sidebar toggle functionality
function setupMobileSidebar() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (!sidebarToggle || !sidebar) return;
    
    sidebarToggle.addEventListener('click', function() {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isExpanded);
        document.body.classList.toggle('sidebar-collapsed');
        
        // Update the icon
        const icon = this.querySelector('i');
        if (icon) {
            icon.className = isExpanded ? 'fas fa-bars' : 'fas fa-times';
        }
    });
}

// Make the toggleAdminDropdown function available globally
window.toggleAdminDropdown = function() {
    const dropdownMenu = document.getElementById('adminDropdownMenu');
    if (!dropdownMenu) return;
    
    const isVisible = dropdownMenu.style.display === 'block';
    dropdownMenu.style.display = isVisible ? 'none' : 'block';
    
    const dropdownBtn = document.querySelector('.admin-dropdown__btn');
    if (dropdownBtn) {
        dropdownBtn.setAttribute('aria-expanded', !isVisible);
    }
};
