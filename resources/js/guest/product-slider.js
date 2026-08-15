/**
 * Product Slider - Auto-slide functionality
 * Shows 4 products at a time, slides 1 product per transition
 * Example: [1,2,3,4] → [2,3,4,5] → [3,4,5,1] etc.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sliders
    initSlider('arrivals-slider', 3000); // 3 seconds per slide
    initSlider('selling-slider', 3000);

    function initSlider(sliderId, autoSlideInterval) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;

        const track = slider.querySelector('.slider-track');
        const originalCards = Array.from(track.querySelectorAll('.product-card'));
        
        const prevBtn = document.querySelector(`[data-slider="${sliderId.replace('-slider', '')}"].slider-prev`);
        const nextBtn = document.querySelector(`[data-slider="${sliderId.replace('-slider', '')}"].slider-next`);
        
        let currentIndex = 0;
        const totalCards = originalCards.length;

        if (totalCards === 0) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }

        function getVisibleCount() {
            if (window.matchMedia('(max-width: 768px)').matches) {
                return totalCards; // mobile uses scroll, slider disabled
            }
            if (window.matchMedia('(max-width: 1200px)').matches) {
                return 3;
            }
            return 4;
        }

        let autoSlideTimer;
        let isTransitioning = false;
        const mobileMedia = window.matchMedia('(max-width: 768px)');
        let isMobile = mobileMedia.matches;

        function updateControlVisibility(active) {
            if (!prevBtn || !nextBtn) return;
            const displayValue = active ? '' : 'none';
            prevBtn.style.display = displayValue;
            nextBtn.style.display = displayValue;
        }

        function ensureClones() {
            track.querySelectorAll('[data-clone="true"]').forEach(clone => clone.remove());
            if (isMobile) return;
            const cloneCount = Math.min(getVisibleCount(), totalCards);
            for (let i = 0; i < cloneCount; i++) {
                const clone = originalCards[i].cloneNode(true);
                clone.dataset.clone = 'true';
                track.appendChild(clone);
            }
        }

        function getAllCards() {
            return Array.from(track.querySelectorAll('.product-card'));
        }

        function isSliderActive() {
            return !isMobile && totalCards > getVisibleCount();
        }

        function refreshSliderState(instant = false) {
            if (currentIndex >= totalCards) {
                currentIndex = 0;
            }

            ensureClones();
            const active = isSliderActive();
            updateControlVisibility(active);

            if (!active) {
                stopAutoSlide();
                track.style.transition = 'none';
                track.style.transform = 'none';
                return;
            }

            if (instant) {
                updateSlider(true);
            } else {
                updateSlider();
            }

            startAutoSlide();
        }

        refreshSliderState(true);

        // Calculate card width including gap
        function getCardWidth() {
            const cards = getAllCards();
            if (cards.length === 0) return 0;
            const card = cards[0];
            const cardWidth = card.getBoundingClientRect().width;
            const styles = window.getComputedStyle(track);
            const gapValue = styles.columnGap || styles.gap;
            const gap = gapValue ? parseFloat(gapValue) : 0;
            return cardWidth + gap;
        }

        // Update slider position
        function updateSlider(instant = false) {
            if (!isSliderActive()) {
                track.style.transition = 'none';
                track.style.transform = 'none';
                return;
            }

            const cardWidth = getCardWidth();
            const offset = -(currentIndex * cardWidth);
            
            if (instant) {
                track.style.transition = 'none';
            } else {
                track.style.transition = 'transform 0.6s ease-in-out';
            }
            
            track.style.transform = `translateX(${offset}px)`;
        }

        // Go to next slide (move 1 card)
        function nextSlide() {
            if (isTransitioning || !isSliderActive()) return;
            
            isTransitioning = true;
            currentIndex++;
            updateSlider();
            
            setTimeout(() => {
                if (currentIndex >= totalCards) {
                    currentIndex = 0;
                    updateSlider(true);
                }
                isTransitioning = false;
            }, 600);
        }

        // Go to previous slide (move 1 card back)
        function prevSlide() {
            if (isTransitioning || !isSliderActive()) return;
            
            isTransitioning = true;
            
            if (currentIndex === 0) {
                currentIndex = totalCards;
                updateSlider(true);
                
                setTimeout(() => {
                    currentIndex--;
                    updateSlider();
                }, 50);
            } else {
                currentIndex--;
                updateSlider();
            }
            
            setTimeout(() => {
                isTransitioning = false;
            }, 600);
        }

        // Auto slide
        function startAutoSlide() {
            if (!isSliderActive() || autoSlideTimer) return;
            autoSlideTimer = setInterval(() => {
                nextSlide();
            }, autoSlideInterval);
        }

        function stopAutoSlide() {
            if (!autoSlideTimer) return;
            clearInterval(autoSlideTimer);
            autoSlideTimer = null;
        }

        function resetAutoSlide() {
            stopAutoSlide();
            startAutoSlide();
        }

        function handleMobileChange(e) {
            isMobile = e.matches;
            currentIndex = 0;
            refreshSliderState(true);
        }

        if (mobileMedia.addEventListener) {
            mobileMedia.addEventListener('change', handleMobileChange);
        } else if (mobileMedia.addListener) {
            mobileMedia.addListener(handleMobileChange);
        }

        // Event listeners
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetAutoSlide();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetAutoSlide();
            });
        }

        // Pause auto-slide on hover
        slider.addEventListener('mouseenter', stopAutoSlide);
        slider.addEventListener('mouseleave', startAutoSlide);

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                isMobile = mobileMedia.matches;
                currentIndex = 0;
                refreshSliderState(true);
            }, 250);
        });

        // Initialize
        refreshSliderState(true);

        // Handle visibility change (pause when tab is not visible)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoSlide();
            } else {
                startAutoSlide();
            }
        });
    }
});
