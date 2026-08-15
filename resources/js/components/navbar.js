// Navbar Component JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Profile popup functionality
    const profileIcon = document.getElementById('profile-icon');
    const profilePopup = document.getElementById('profile-popup');

    if (profileIcon) {
        if (profilePopup) {
            // If user is authenticated (popup exists) toggle popup
            profileIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                profilePopup.classList.toggle('show');
            });

            // Close popup when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileIcon.contains(e.target) && !profilePopup.contains(e.target)) {
                    profilePopup.classList.remove('show');
                }
            });
        } else {
            // If no popup (user not authenticated), clicking profile redirects to login page
            profileIcon.style.cursor = 'pointer';
            profileIcon.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = '/login';
            });
        }
    }

    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                const targetUrl = new URL(window.location.origin + '/all-products');
                if (query) {
                    targetUrl.searchParams.set('search', query);
                }
                window.location.href = targetUrl.toString();
            }
        });
    }

    // Cart badge update (if needed)
    updateCartBadge();
});

// Function to update cart badge
function updateCartBadge() {
    const cartButton = document.querySelector('.action-button[aria-label="Buka Keranjang"]');
    if (cartButton) {
        // Check if cart badge exists, if not create it
        let badge = cartButton.querySelector('.cart-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            cartButton.appendChild(badge);
        }
        
        // Update badge count (you can implement actual cart count logic here)
        const cartCount = getCartCount(); // Implement this function based on your cart logic
        if (cartCount > 0) {
            badge.textContent = cartCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Function to get cart count (implement based on your cart logic)
function getCartCount() {
    // This is a placeholder - implement based on your actual cart system
    // You might want to get this from localStorage, sessionStorage, or an API call
    return 0;
}

// Export functions for use in other scripts
window.NavbarComponent = {
    updateCartBadge: updateCartBadge,
    getCartCount: getCartCount
};