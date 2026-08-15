/**
 * Product Card Image Carousel
 * Handles automatic image sliding for product cards with multiple variant images
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeProductCarousels();
    initializeProductCardClicks();
    
    // Re-initialize on dynamic content updates
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && (node.classList.contains('product-card') || node.querySelector('.product-card'))) {
                        initializeProductCarousels();
                        initializeProductCardClicks();
                    }
                });
            }
        });
    });
    
    // Observe product grids
    const productGrids = document.querySelectorAll('.products-grid, .product-grid, .slider-track');
    productGrids.forEach(grid => {
        observer.observe(grid, { childList: true, subtree: true });
    });
});

function initializeProductCarousels() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Skip if already initialized
        if (card.dataset.carouselInitialized === 'true') {
            return;
        }
        
        const variantImagesAttr = card.dataset.variantImages;
        if (!variantImagesAttr) return;
        
        let variantImages;
        try {
            variantImages = JSON.parse(variantImagesAttr);
        } catch (e) {
            console.error('Error parsing variant images:', e);
            return;
        }
        
        // Only initialize carousel if there are multiple images
        if (!variantImages || variantImages.length <= 1) {
            return;
        }
        
        const imageContainer = card.querySelector('.product-image-container');
        const image = card.querySelector('.product-image');
        
        if (!image || !imageContainer) return;
        
        let currentIndex = 0;
        let carouselInterval;
        let isPaused = false;
        
        // Set up image transition
        image.style.transition = 'opacity 0.4s ease-in-out';
        
        // Carousel update function
        function updateCarousel() {
            if (isPaused) return;
            
            currentIndex = (currentIndex + 1) % variantImages.length;
            
            // Fade out
            image.style.opacity = '0';
            
            setTimeout(() => {
                // Change image
                image.src = variantImages[currentIndex];
                
                // Fade in
                image.style.opacity = '1';
            }, 400);
        }
        
        // Start carousel
        function startCarousel() {
            if (carouselInterval) return;
            carouselInterval = setInterval(updateCarousel, 2500);
        }
        
        // Stop carousel
        function stopCarousel() {
            if (carouselInterval) {
                clearInterval(carouselInterval);
                carouselInterval = null;
            }
        }
        
        // Pause/resume on hover
        card.addEventListener('mouseenter', () => {
            isPaused = true;
        });
        
        card.addEventListener('mouseleave', () => {
            isPaused = false;
        });
        
        // Pause when tab is not visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopCarousel();
            } else {
                startCarousel();
            }
        });
        
        // Start the carousel
        startCarousel();
        
        // Mark as initialized
        card.dataset.carouselInitialized = 'true';
        
        // Store interval for cleanup
        card.dataset.carouselIntervalId = carouselInterval;
    });
}

// Initialize click handlers for product cards
function initializeProductCardClicks() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Skip if already has click handler
        if (card.dataset.clickInitialized === 'true') {
            return;
        }
        
        const productId = card.dataset.productId;
        if (!productId) return;
        
        // Add click handler to card (but not on buttons)
        card.addEventListener('click', function(e) {
            // Don't redirect if clicking on action buttons
            if (e.target.closest('.action-btn') || e.target.closest('.product-actions')) {
                return;
            }
            
            // For recommendation cards, use the link instead
            if (card.classList.contains('recommendation-card')) {
                const link = card.querySelector('.recommendation-link');
                if (link) {
                    link.click();
                }
                return;
            }
            
            // Redirect to product detail page with ID parameter
            window.location.href = `/product-detail?id=${productId}`;
        });
        
        // Add hover effect
        card.style.cursor = 'pointer';
        
        // Mark as initialized
        card.dataset.clickInitialized = 'true';
    });
}

// Export functions to global scope for use in other scripts
window.initializeProductCarousels = initializeProductCarousels;
window.initializeProductCardClicks = initializeProductCardClicks;

// Cleanup function
window.cleanupProductCarousels = function() {
    const productCards = document.querySelectorAll('.product-card[data-carousel-initialized="true"]');
    productCards.forEach(card => {
        const intervalId = card.dataset.carouselIntervalId;
        if (intervalId) {
            clearInterval(parseInt(intervalId));
            delete card.dataset.carouselIntervalId;
        }
        card.dataset.carouselInitialized = 'false';
    });
};
