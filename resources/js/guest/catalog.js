document.addEventListener('DOMContentLoaded', function() {
    // Filter dropdown toggle functionality
    initializeFilterToggles();
    
    // Profile popup functionality
    const profileIcon = document.getElementById('profile-icon');
    const profilePopup = document.getElementById('profile-popup');

    function toggleProfilePopup() {
        if (!profilePopup) return;

        const isAuthenticated = profilePopup.dataset.auth === 'true';

        if (!isAuthenticated) {
            window.location.href = profilePopup.dataset.loginUrl;
            return;
        }

        const isVisible = profilePopup.style.display === 'block';
        profilePopup.style.display = isVisible ? 'none' : 'block';
    }

    function closeProfilePopup() {
        if (!profilePopup) return;
        profilePopup.style.display = 'none';
    }

    if (profileIcon) {
        profileIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleProfilePopup();
        });
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
        if (profilePopup && profilePopup.style.display === 'block') {
            if (!profilePopup.contains(e.target) && e.target !== profileIcon) {
                closeProfilePopup();
            }
        }
    });

    // Prevent popup from closing when clicking inside it
    if (profilePopup) {
        profilePopup.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Close popup on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && profilePopup && profilePopup.style.display === 'block') {
            closeProfilePopup();
        }
    });

    // Color filter selection
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });

    // Size filter selection
    const sizeOptions = document.querySelectorAll('.size-option');
    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            sizeOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Apply filter button with AJAX
    const applyFilterBtn = document.querySelector('.apply-filter');
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            applyFilters();
        });
    }

    // Function to apply filters via AJAX
    function applyFilters(page = 1) {
        const selectedColors = Array.from(document.querySelectorAll('.color-option.active'))
            .map(el => el.dataset.color);
        const selectedSizes = Array.from(document.querySelectorAll('.size-option.active'))
            .map(el => el.textContent.trim());
        const selectedCategories = Array.from(document.querySelectorAll('input[name="categories[]"]:checked'))
            .map(el => el.value);
        const searchQuery = document.getElementById('search-input')?.value || '';
        const sortBy = document.getElementById('sort-select')?.value || 'most_popular';
        
        // Get price range values
        const minPrice = document.getElementById('price-range-min')?.value || 0;
        const maxPrice = document.getElementById('price-range-max')?.value || 2500000;
        
        // Get quick filters
        const withPromo = document.querySelector('input[name="promo"]:checked') ? '1' : null;
        const readyStock = document.querySelector('input[name="ready"]:checked') ? '1' : null;
        const customDesign = document.querySelector('input[name="custom"]:checked') ? '1' : null;

        // Build query parameters
        const params = new URLSearchParams(window.location.search);
        
        if (searchQuery) {
            params.set('search', searchQuery);
        } else {
            params.delete('search');
        }
        
        if (selectedColors.length > 0) {
            params.set('colors', selectedColors.join(','));
        } else {
            params.delete('colors');
        }
        
        if (selectedSizes.length > 0) {
            params.set('sizes', selectedSizes.join(','));
        } else {
            params.delete('sizes');
        }
        
        if (selectedCategories.length > 0) {
            params.set('categories', selectedCategories.join(','));
        } else {
            params.delete('categories');
        }
        
        // Price range
        if (minPrice > 0) {
            params.set('min_price', minPrice);
        } else {
            params.delete('min_price');
        }
        
        if (maxPrice < 2500000) {
            params.set('max_price', maxPrice);
        } else {
            params.delete('max_price');
        }
        
        // Quick filters
        if (withPromo) {
            params.set('promo', withPromo);
        } else {
            params.delete('promo');
        }
        
        if (readyStock) {
            params.set('ready', readyStock);
        } else {
            params.delete('ready');
        }
        
        if (customDesign) {
            params.set('custom', customDesign);
        } else {
            params.delete('custom');
        }
        
        params.set('sort', sortBy);
        params.set('page', page);

        // Update URL without reload
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);

        // Fetch filtered products
        fetch(newUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateProductsGrid(data.products);
            updatePagination(data.pagination);
            updateProductsCount(data.pagination);
        })
        .catch(error => {
            console.error('Error fetching products:', error);
        });
    }

    // Update products grid
    function updateProductsGrid(products) {
        const grid = document.getElementById('products-grid');
        if (!grid) return;

        if (products.length === 0) {
            grid.innerHTML = `
                <div class="no-products">
                    <i class="fas fa-inbox"></i>
                    <p>Tidak ada produk dengan filter ini</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = products.map(product => {
            // Priority: variant_images[0] > product.image > placeholder
            let imageUrl = '';
            if (product.variant_images && product.variant_images.length > 0) {
                imageUrl = product.variant_images[0];
            } else if (product.image) {
                imageUrl = `/storage/${product.image}`;
            }
            
            const variantImagesJson = JSON.stringify(product.variant_images || []).replace(/'/g, '&#39;');
            const customRibbon = product.custom_design_allowed ? '<div class="product-ribbon" aria-hidden="true">CUSTOM</div>' : '';
            const imageHtml = imageUrl 
                ? `<img class="product-image" src="${imageUrl}" alt="${product.name}" onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\\'no-image-placeholder\\'><i class=\\'fas fa-image\\'></i></div>';">`
                : `<div class="no-image-placeholder"><i class="fas fa-image"></i></div>`;
            
            return `
                <div class="product-card"
                     data-product-id="${product.id}"
                     data-product-slug="${product.slug || ''}"
                     data-product-name="${product.name}"
                     data-product-price="${product.formatted_price}"
                     data-product-image="${imageUrl}"
                     data-variant-images='${variantImagesJson}'>
                    <div class="product-image-container" data-product-id="${product.id}">
                        ${imageHtml}
                        ${customRibbon}
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">${product.name}</h3>
                        <p class="product-price">Rp ${product.formatted_price}</p>
                        <div class="product-actions" role="group" aria-label="Aksi produk">
                            <button class="action-btn action-chat" type="button" aria-label="Chat tentang produk">
                                <i class="fas fa-comments" aria-hidden="true"></i>
                            </button>
                            <button class="action-btn action-cart" type="button" aria-label="Tambahkan ke keranjang" data-product-id="${product.id}">
                                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Re-initialize carousels and click handlers using product-card-carousel.js functions
        if (typeof window.initializeProductCarousels === 'function') {
            window.initializeProductCarousels();
        }
        if (typeof window.initializeProductCardClicks === 'function') {
            window.initializeProductCardClicks();
        }
    }

    // Update pagination
    function updatePagination(pagination) {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        if (pagination.last_page <= 1) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'flex';
        
        let paginationHTML = '';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHTML += `<a href="#" class="pagination-btn pagination-link" data-page="${pagination.current_page - 1}">
                <i class="fas fa-chevron-left"></i>
                Sebelumnya
            </a>`;
        } else {
            paginationHTML += `<button class="pagination-btn" disabled>
                <i class="fas fa-chevron-left"></i>
                Sebelumnya
            </button>`;
        }
        
        // Page numbers
        paginationHTML += '<div class="pagination-numbers">';
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                paginationHTML += `<button class="pagination-number active">${i}</button>`;
            } else {
                paginationHTML += `<a href="#" class="pagination-number pagination-link" data-page="${i}">${i}</a>`;
            }
        }
        paginationHTML += '</div>';
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHTML += `<a href="#" class="pagination-btn pagination-link" data-page="${pagination.current_page + 1}">
                Selanjutnya
                <i class="fas fa-chevron-right"></i>
            </a>`;
        } else {
            paginationHTML += `<button class="pagination-btn" disabled>
                Selanjutnya
                <i class="fas fa-chevron-right"></i>
            </button>`;
        }
        
        container.innerHTML = paginationHTML;
        
        // Attach pagination click handlers
        attachPaginationHandlers();
    }

    // Update products count
    function updateProductsCount(pagination) {
        const countElement = document.getElementById('products-count');
        if (countElement) {
            countElement.textContent = `Menampilkan ${pagination.from || 0}-${pagination.to || 0} dari ${pagination.total} Produk`;
        }
    }

    // Attach pagination handlers
    function attachPaginationHandlers() {
        const paginationLinks = document.querySelectorAll('.pagination-link');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.dataset.page;
                applyFilters(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    // Note: Product card click is handled by product-card-carousel.js
    // But we still need action button handlers here for chat and cart functionality
    document.addEventListener('click', function(e) {
        const chatBtn = e.target.closest('.action-chat');
        const cartBtn = e.target.closest('.action-cart');
        
        if (chatBtn) {
            e.stopPropagation(); // Prevent card click
            window.location.href = '/chatbot';
        } else if (cartBtn) {
            e.stopPropagation(); // Prevent card click
            const productId = cartBtn.getAttribute('data-product-id') || 
                             cartBtn.closest('.product-card')?.getAttribute('data-product-id');
            if (productId) {
                // Add to cart logic here
                console.log('Add to cart:', productId);
                alert(`Produk ditambahkan ke keranjang! (ID: ${productId})`);
            }
        }
    });

    // Pagination click handlers
    const pageNumbers = document.querySelectorAll('.pagination-number');
    pageNumbers.forEach(btn => {
        btn.addEventListener('click', function() {
            pageNumbers.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });

    // Search functionality with auto-apply
    const searchBox = document.getElementById('search-input');
    if (searchBox) {
        searchBox.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    }

    // Sort by change handler with auto-apply
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Price range slider functionality
    const priceRangeMin = document.getElementById('price-range-min');
    const priceRangeMax = document.getElementById('price-range-max');
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    
    function formatRupiah(value) {
        return 'Rp ' + parseInt(value).toLocaleString('id-ID');
    }
    
    function parseRupiah(value) {
        return parseInt(value.replace(/[^0-9]/g, '')) || 0;
    }
    
    if (priceRangeMin && priceRangeMax && minPriceInput && maxPriceInput) {
        // Update input fields when sliders change
        priceRangeMin.addEventListener('input', function() {
            const minVal = parseInt(this.value);
            const maxVal = parseInt(priceRangeMax.value);
            
            if (minVal > maxVal - 50000) {
                this.value = maxVal - 50000;
            }
            
            minPriceInput.value = formatRupiah(this.value);
        });
        
        priceRangeMax.addEventListener('input', function() {
            const minVal = parseInt(priceRangeMin.value);
            const maxVal = parseInt(this.value);
            
            if (maxVal < minVal + 50000) {
                this.value = minVal + 50000;
            }
            
            maxPriceInput.value = formatRupiah(this.value);
        });
        
        // Apply filters when sliders are released
        priceRangeMin.addEventListener('change', function() {
            applyFilters();
        });
        
        priceRangeMax.addEventListener('change', function() {
            applyFilters();
        });
        
        // Update sliders when input fields change
        minPriceInput.addEventListener('blur', function() {
            const value = parseRupiah(this.value);
            const maxVal = parseInt(priceRangeMax.value);
            
            if (value < 0) this.value = formatRupiah(0);
            if (value > maxVal - 50000) this.value = formatRupiah(maxVal - 50000);
            
            priceRangeMin.value = parseRupiah(this.value);
            applyFilters();
        });
        
        maxPriceInput.addEventListener('blur', function() {
            const value = parseRupiah(this.value);
            const minVal = parseInt(priceRangeMin.value);
            
            if (value > 2500000) this.value = formatRupiah(2500000);
            if (value < minVal + 50000) this.value = formatRupiah(minVal + 50000);
            
            priceRangeMax.value = parseRupiah(this.value);
            applyFilters();
        });
    }
    
    // Category and quick filter checkboxes - auto-apply
    const filterCheckboxes = document.querySelectorAll('input[name="categories[]"], input[name="promo"], input[name="ready"], input[name="custom"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Initial pagination handlers (if exists on page load)
    attachPaginationHandlers();

    // Filter section toggles
    const filterSections = document.querySelectorAll('.filter-header');
    filterSections.forEach(section => {
        section.addEventListener('click', function() {
            const container = this.nextElementSibling;
            if (!container) return;

            const icon = this.querySelector('i');
            const isCollapsed = container.classList.toggle('collapsed');

            if (icon) {
                icon.classList.toggle('fa-chevron-up', !isCollapsed);
                icon.classList.toggle('fa-chevron-down', isCollapsed);
            }

            container.style.display = isCollapsed ? 'none' : '';
        });
    });
});

// Initialize filter toggles
function initializeFilterToggles() {
    const filterTitleRows = document.querySelectorAll('.filter-title-row');
    
    filterTitleRows.forEach(row => {
        row.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('collapsed');
        });
    });
}
