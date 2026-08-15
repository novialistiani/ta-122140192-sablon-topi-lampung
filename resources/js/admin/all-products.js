// All Products Page JavaScript
class AllProductsManager {
    constructor() {
        this.currentPage = 1;
        this.perPage = 12;
        this.currentView = 'grid';
        this.filters = {
            search: '',
            category: '',
            status: '',
            sort: 'newest'
        };
        this.products = [];
        this.selectedProducts = new Set();
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadProducts();
    }

    bindEvents() {
        // Search input
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.filters.search = e.target.value;
                    this.currentPage = 1;
                    this.loadProducts();
                }, 500);
            });
        }

        // Filter selects
        const filterSelects = ['category-filter', 'status-filter', 'sort-filter'];
        filterSelects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                select.addEventListener('change', (e) => {
                    const filterKey = selectId.replace('-filter', '');
                    this.filters[filterKey] = e.target.value;
                    this.currentPage = 1;
                    this.loadProducts();
                });
            }
        });

        // View toggle buttons
        const viewButtons = document.querySelectorAll('.view-btn[data-view]');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.currentTarget.dataset.view;
                this.setView(view);
            });
        });

        // Add product buttons
        const addProductBtns = document.querySelectorAll('#add-product-btn, #add-first-product-btn');
        addProductBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.openProductForm();
            });
        });

        // Product actions (will be bound dynamically)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.view-btn')) {
                const productId = e.target.closest('[data-id]').dataset.id;
                this.viewProduct(productId);
            } else if (e.target.closest('.edit-btn')) {
                const productId = e.target.closest('[data-id]').dataset.id;
                this.editProduct(productId);
            } else if (e.target.closest('.delete-btn')) {
                const productId = e.target.closest('[data-id]').dataset.id;
                this.deleteProduct(productId);
            }
        });
    }

    async loadProducts() {
        this.showLoading();
        
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...this.filters
            });

            const response = await fetch(`/admin/api/products?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.products = data.data;
                // Debug: Log first product to check variant_images
                if (this.products.length > 0) {
                    console.log('Sample product data:', this.products[0]);
                    console.log('Has variant_images:', this.products[0].variant_images);
                }
                this.renderProducts();
                this.updateResultsCount(data.pagination.total);
                this.renderPagination(data.pagination);
            } else {
                this.showError(data.message || 'Failed to load products');
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showError('Failed to load products. Please try again.');
        }
    }

    renderProducts() {
        const container = document.getElementById('products-grid');
        if (!container) return;

        if (this.products.length === 0) {
            this.showEmptyState();
            return;
        }

        this.hideEmptyState();
        
        // Clean up existing carousel intervals
        this.cleanupCarousels(container);
        
        const template = this.currentView === 'grid' 
            ? document.getElementById('product-card-template')
            : document.getElementById('product-list-template');

        if (!template) return;

        container.innerHTML = '';

        this.products.forEach(product => {
            const clone = template.content.cloneNode(true);
            this.populateProductTemplate(clone, product);
            container.appendChild(clone);
        });
    }

    cleanupCarousels(container) {
        // Clear all carousel intervals before re-rendering
        const imageContainers = container.querySelectorAll('[data-carousel-interval]');
        imageContainers.forEach(container => {
            const intervalId = container.dataset.carouselInterval;
            if (intervalId) {
                clearInterval(parseInt(intervalId));
                delete container.dataset.carouselInterval;
            }
        });
    }

    populateProductTemplate(template, product) {
        const element = template.querySelector('[data-id]');
        if (!element) return;

        element.dataset.id = product.id;

        // Product image with carousel for variant images
        const imageContainer = element.querySelector('.product-image-container') || element.querySelector('.list-image');
        const image = element.querySelector('.main-image');
        
        if (image && imageContainer) {
            // Check if product has variant images
            if (product.variant_images && product.variant_images.length > 1) {
                // Multiple variant images - create carousel
                let currentIndex = 0;
                const images = product.variant_images;
                
                // Set initial image
                image.src = images[0];
                image.alt = product.name;
                image.style.transition = 'opacity 0.3s ease';
                
                // Add indicator dots
                const dotsContainer = document.createElement('div');
                dotsContainer.className = 'carousel-dots';
                const dots = [];
                images.forEach((_, index) => {
                    const dot = document.createElement('span');
                    dot.className = 'carousel-dot' + (index === 0 ? ' active' : '');
                    dotsContainer.appendChild(dot);
                    dots.push(dot);
                });
                imageContainer.appendChild(dotsContainer);
                
                // Carousel function
                const updateCarousel = () => {
                    currentIndex = (currentIndex + 1) % images.length;
                    
                    // Fade out
                    image.style.opacity = '0';
                    
                    setTimeout(() => {
                        // Change image
                        image.src = images[currentIndex];
                        
                        // Update dots
                        dots.forEach((dot, i) => {
                            dot.classList.toggle('active', i === currentIndex);
                        });
                        
                        // Fade in
                        image.style.opacity = '1';
                    }, 300);
                };
                
                // Start carousel
                const carouselInterval = setInterval(updateCarousel, 2500);
                
                // Store interval ID for cleanup
                imageContainer.dataset.carouselInterval = carouselInterval;
                
            } else if (product.variant_images && product.variant_images.length === 1) {
                // Single variant image
                image.src = product.variant_images[0];
                image.alt = product.name;
            } else if (product.image) {
                // Fallback to default product image
                image.src = `/storage/${product.image}`;
                image.alt = product.name;
            } else {
                // No image available - use placeholder
                image.src = '/images/placeholder-product.svg';
                image.alt = product.name;
                image.onerror = () => {
                    // If placeholder also fails, show icon
                    image.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = 'no-image-placeholder';
                    placeholder.innerHTML = '<i class="fas fa-image"></i>';
                    imageContainer.appendChild(placeholder);
                };
            }
        }

        // Product name
        const nameElement = element.querySelector('.product-name');
        if (nameElement) {
            nameElement.textContent = product.name;
        }

        // Product category
        const categoryElement = element.querySelector('.product-category');
        if (categoryElement) {
            categoryElement.textContent = product.category;
        }

        // Product price
        const currentPriceElement = element.querySelector('.current-price');
        if (currentPriceElement) {
            currentPriceElement.textContent = this.formatPrice(product.price);
        }

        const originalPriceElement = element.querySelector('.original-price');
        if (originalPriceElement) {
            if (product.original_price && product.original_price > product.price) {
                originalPriceElement.textContent = this.formatPrice(product.original_price);
                originalPriceElement.style.display = 'inline';
            } else {
                originalPriceElement.style.display = 'none';
            }
        }

        // Stock
        const stockElement = element.querySelector('.stock-value');
        if (stockElement) {
            stockElement.textContent = product.total_stock || product.stock;
        }

        // Status badge
        const statusBadge = element.querySelector('.status-badge');
        if (statusBadge) {
            const totalStock = product.total_stock || product.stock;
            if (totalStock === 0) {
                statusBadge.textContent = 'Habis';
                statusBadge.className = 'badge status-badge out-of-stock';
            } else if (product.is_active) {
                statusBadge.textContent = 'Aktif';
                statusBadge.className = 'badge status-badge';
            } else {
                statusBadge.textContent = 'Draft';
                statusBadge.className = 'badge status-badge draft';
            }
        }

        // Stock badge
        const stockBadge = element.querySelector('.stock-badge');
        if (stockBadge) {
            const totalStock = product.total_stock || product.stock;
            if (totalStock === 0) {
                stockBadge.textContent = 'Habis';
                stockBadge.className = 'badge stock-badge out';
            } else if (totalStock <= 10) {
                stockBadge.textContent = 'Stok Rendah';
                stockBadge.className = 'badge stock-badge low';
            } else {
                stockBadge.textContent = 'Tersedia';
                stockBadge.className = 'badge stock-badge';
            }
        }

        // Custom design badge
        const customBadge = element.querySelector('.custom-badge');
        if (customBadge) {
            if (product.custom_design_allowed) {
                customBadge.style.display = 'inline-block';
                customBadge.textContent = 'CUSTOM';
            } else {
                customBadge.style.display = 'none';
            }
        }
    }

    setView(view) {
        this.currentView = view;
        
        // Update view buttons
        document.querySelectorAll('.view-btn[data-view]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });

        // Update grid container class
        const container = document.getElementById('products-grid');
        if (container) {
            container.classList.toggle('list-view', view === 'list');
        }

        // Re-render products
        this.renderProducts();
    }

    showLoading() {
        const loadingState = document.getElementById('loading-state');
        const emptyState = document.getElementById('empty-state');
        const container = document.getElementById('products-grid');
        
        if (loadingState) loadingState.style.display = 'flex';
        if (emptyState) emptyState.style.display = 'none';
        if (container) container.innerHTML = '<div class="loading-state" id="loading-state"><div class="spinner"></div><p>Memuat produk...</p></div>';
    }

    showEmptyState() {
        const emptyState = document.getElementById('empty-state');
        const container = document.getElementById('products-grid');
        
        if (emptyState) emptyState.style.display = 'flex';
        if (container) container.innerHTML = '';
    }

    hideEmptyState() {
        const emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.style.display = 'none';
    }

    showError(message) {
        const container = document.getElementById('products-grid');
        if (container) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Terjadi Kesalahan</h3>
                    <p>${message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-refresh"></i>
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    }

    updateResultsCount(total) {
        const countElement = document.getElementById('results-count');
        if (countElement) {
            countElement.textContent = `${total} produk ditemukan`;
        }
    }

    renderPagination(pagination) {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        if (pagination.last_page <= 1) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'flex';
        
        let paginationHTML = '<div class="pagination">';
        
        // Previous button
        const prevDisabled = pagination.current_page <= 1 ? 'disabled' : '';
        paginationHTML += `
            <button class="pagination-btn ${prevDisabled}" 
                    onclick="allProductsManager.goToPage(${pagination.current_page - 1})"
                    ${prevDisabled ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
                Previous
            </button>
        `;

        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        if (startPage > 1) {
            paginationHTML += `<button class="pagination-btn" onclick="allProductsManager.goToPage(1)">1</button>`;
            if (startPage > 2) {
                paginationHTML += `<span class="pagination-info">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === pagination.current_page ? 'active' : '';
            paginationHTML += `
                <button class="pagination-btn ${activeClass}" 
                        onclick="allProductsManager.goToPage(${i})">
                    ${i}
                </button>
            `;
        }

        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                paginationHTML += `<span class="pagination-info">...</span>`;
            }
            paginationHTML += `<button class="pagination-btn" onclick="allProductsManager.goToPage(${pagination.last_page})">${pagination.last_page}</button>`;
        }

        // Next button
        const nextDisabled = pagination.current_page >= pagination.last_page ? 'disabled' : '';
        paginationHTML += `
            <button class="pagination-btn ${nextDisabled}" 
                    onclick="allProductsManager.goToPage(${pagination.current_page + 1})"
                    ${nextDisabled ? 'disabled' : ''}>
                Next
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

        paginationHTML += '</div>';
        
        // Pagination info
        paginationHTML += `
            <div class="pagination-info">
                Menampilkan ${pagination.from || 0} - ${pagination.to || 0} dari ${pagination.total} produk
            </div>
        `;

        container.innerHTML = paginationHTML;
    }

    goToPage(page) {
        if (page < 1 || page > Math.ceil(this.products.length / this.perPage)) return;
        
        this.currentPage = page;
        this.loadProducts();
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    formatPrice(price) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    viewProduct(productId) {
        // Redirect to product detail page
        window.location.href = `/admin/all-products/detail/${productId}`;
    }

    editProduct(productId) {
        // Redirect to product edit page or open edit modal
        window.location.href = `/admin/management-product?edit=${productId}`;
    }

    async deleteProduct(productId) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/api/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Produk berhasil dihapus', 'success');
                this.loadProducts();
            } else {
                this.showNotification(data.message || 'Gagal menghapus produk', 'error');
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            this.showNotification('Gagal menghapus produk. Silakan coba lagi.', 'error');
        }
    }

    openProductForm() {
        // Redirect to product management page with add form
        window.location.href = '/admin/management-product?add=true';
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Remove notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.allProductsManager = new AllProductsManager();
});

// Add notification styles
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 16px 20px;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification-success {
        border-left: 4px solid #10b981;
    }

    .notification-error {
        border-left: 4px solid #ef4444;
    }

    .notification-info {
        border-left: 4px solid #3b82f6;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        color: #374151;
    }

    .notification-content i {
        font-size: 16px;
    }

    .notification-success .notification-content i {
        color: #10b981;
    }

    .notification-error .notification-content i {
        color: #ef4444;
    }

    .notification-info .notification-content i {
        color: #3b82f6;
    }
`;

// Add styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);