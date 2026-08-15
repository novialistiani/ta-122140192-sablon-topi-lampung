// Product Management JavaScript
let currentFilters = {
    status: 'ALL',
    search: '',
    category: '',
    page: 1,
    perPage: 10
};

let selectedProducts = new Set();
let currentEditId = null;

// Close menus when clicking outside
document.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.remove('show');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadProducts();
});

function initializeEventListeners() {
    // Tab filters
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilters.status = this.dataset.status;
            currentFilters.page = 1;
            loadProducts();
        });
    });

    // Search
    const searchInput = document.getElementById('search-input');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = this.value;
            currentFilters.page = 1;
            loadProducts();
        }, 500);
    });

    // Category filter
    document.getElementById('category-filter').addEventListener('change', function() {
        currentFilters.category = this.value;
        currentFilters.page = 1;
        loadProducts();
    });

    // Select all checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedProducts.add(parseInt(cb.value));
            } else {
                selectedProducts.delete(parseInt(cb.value));
            }
        });
        updateBulkActions();
    });

    // Bulk archive button
    document.getElementById('bulk-archive-btn').addEventListener('click', bulkArchiveProducts);

    // Add product button - open modern drawer
    document.getElementById('add-product-btn').addEventListener('click', openModernDrawer);

    // Export button
    document.getElementById('export-btn').addEventListener('click', exportProducts);

    // Modern Drawer controls
    const modernDrawerClose = document.getElementById('modern-drawer-close');
    const modernDrawerOverlay = document.getElementById('modern-drawer-overlay');
    const modernCancelBtn = document.getElementById('modern-cancel-btn');
    
    if (modernDrawerClose) modernDrawerClose.addEventListener('click', closeModernDrawer);
    if (modernDrawerOverlay) modernDrawerOverlay.addEventListener('click', closeModernDrawer);
    if (modernCancelBtn) modernCancelBtn.addEventListener('click', closeModernDrawer);
}


async function loadProducts() {
    const tbody = document.getElementById('products-tbody');
    
    // Cleanup existing carousels
    cleanupProductCarousels();
    
    tbody.innerHTML = '<tr><td colspan="8" class="loading-state"><div class="spinner"></div><p>Memuat produk...</p></td></tr>';

    try {
        const params = new URLSearchParams({
            status: currentFilters.status,
            search: currentFilters.search,
            category: currentFilters.category,
            page: currentFilters.page,
            perPage: currentFilters.perPage
        });

        const response = await fetch(`/admin/api/products?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            renderProducts(result.data);
            renderPagination(result.pagination);
            updateProductCount(result.pagination.total);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error loading products:', error);
        tbody.innerHTML = `<tr><td colspan="8" class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error: ${error.message}</p></td></tr>`;
    }
}

function renderProducts(products) {
    const tbody = document.getElementById('products-tbody');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada produk</p></td></tr>';
        return;
    }

    // Build HTML string without complex nested quotes
    let html = '';
    
    products.forEach(product => {
        // Main product row
        html += `<tr data-product-id="${product.id}">
            <td>
                <input type="checkbox" class="product-checkbox" value="${product.id}" ${selectedProducts.has(product.id) ? 'checked' : ''}>
            </td>
            <td>
                <div class="product-image-cell" data-product-id="${product.id}">`;
        
        // Image handling - use simple if/else
        if (product.variant_images && product.variant_images.length > 0) {
            html += `<img class="product-carousel-img" src="${product.variant_images[0]}" alt="${product.name}">`;
            if (product.variant_images.length > 1) {
                html += `<div class="image-count-badge">+${product.variant_images.length - 1}</div>`;
            }
        } else if (product.image) {
            html += `<img src="${product.image}" alt="${product.name}">`;
        } else {
            html += '<div class="no-image"><i class="fas fa-image"></i></div>';
        }
        
        html += `</div>
            </td>
            <td>
                <div class="product-name-cell">
                    <div class="product-name">${product.name}</div>
                    <div class="product-id">ID: ${product.id}</div>
                </div>
            </td>
            <td>${capitalizeFirst(product.category)}</td>
            <td>
                <div class="price-variants-cell">
                    <div>${product.price_range || 'Rp ' + formatPrice(product.price)}</div>
                    ${product.variants && product.variants.length > 0 ? `<small>${product.variants.length} varian</small>` : ''}
                </div>
            </td>
            <td>
                <span class="stock-number ${(product.total_stock || product.stock) > 0 ? 'in-stock' : 'out-of-stock'}">
                    ${product.total_stock || product.stock}
                </span>
            </td>
            <td>
                <div class="status-cell">
                    <span class="badge ${product.is_active ? 'badge-success' : 'badge-warning'}">
                        ${product.is_active ? 'Aktif' : 'Draft'}
                    </span>
                    <label class="switch">
                        <input type="checkbox" ${product.is_active ? 'checked' : ''} onchange="toggleProductStatus(${product.id})">
                        <span class="slider"></span>
                    </label>
                </div>
            </td>
            <td>
                <div class="dropdown-actions">
                    <button class="btn-menu" data-product-id="${product.id}">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu" id="menu-${product.id}">
                        ${product.variants && product.variants.length > 0 ? `
                            <a href="#" data-action="view" data-product-id="${product.id}">
                                <i class="fas fa-chevron-down"></i> View Varian
                            </a>
                        ` : ''}
                        <a href="#" data-action="edit" data-product-id="${product.id}">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="#" data-action="delete" data-product-id="${product.id}" class="danger">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            </td>
        </tr>`;
        
        // Variant rows
        if (product.variants && product.variants.length > 0) {
            groupVariantsByColor(product.variants).forEach(group => {
                group.variants.forEach(v => {
                    html += `<tr class="variant-data-row" id="variants-${product.id}-item" style="display: none;">
                        <td style="padding-left: 1rem;">
                            <input type="checkbox" disabled style="opacity: 0.3;">
                        </td>
                        <td>
                            <div class="product-image-cell">`;
                    
                    if (v.image) {
                        html += `<img src="${v.image}" alt="Variant">`;
                    } else {
                        html += '<div class="no-image"><i class="fas fa-image"></i></div>';
                    }
                    
                    html += `</div>
                        </td>
                        <td>
                            <div class="product-name-cell">
                                <div class="product-name">${getColorName(v.color)} / ${v.size}</div>
                            </div>
                        </td>
                        <td><span style="color: #9ca3af;">-</span></td>
                        <td>Rp ${formatPrice(v.price)}</td>
                        <td>
                            <span class="stock-number ${v.stock > 0 ? 'in-stock' : 'out-of-stock'}">${v.stock}</span>
                        </td>
                        <td>
                            <span class="badge ${v.stock > 0 ? 'badge-success' : 'badge-danger'}">
                                ${v.stock > 0 ? 'Tersedia' : 'Habis'}
                            </span>
                        </td>
                        <td><span style="color: #9ca3af;">-</span></td>
                    </tr>`;
                });
            });
        }
    });
    
    tbody.innerHTML = html;

    // Add image error handling
    document.querySelectorAll('.product-image-cell img').forEach(img => {
        img.addEventListener('error', function() {
            // Hide image and show placeholder
            this.style.display = 'none';
            const parent = this.parentElement;
            if (!parent.querySelector('.no-image')) {
                const placeholder = document.createElement('div');
                placeholder.className = 'no-image';
                placeholder.innerHTML = '<i class="fas fa-image"></i>';
                parent.appendChild(placeholder);
            }
        });
        img.addEventListener('load', function() {
            // Ensure image is visible when loaded
            this.style.display = 'block';
        });
    });

    // Attach checkbox listeners
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                selectedProducts.add(parseInt(this.value));
            } else {
                selectedProducts.delete(parseInt(this.value));
            }
            updateBulkActions();
        });
    });

    // Attach menu button listeners
    document.querySelectorAll('.btn-menu').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const productId = this.dataset.productId;
            
            // Close all other menus
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu.id !== `menu-${productId}`) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current menu
            const menu = document.getElementById(`menu-${productId}`);
            menu.classList.toggle('show');
        });
    });

    // Attach menu item listeners
    document.querySelectorAll('.dropdown-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const action = this.dataset.action;
            const productId = parseInt(this.dataset.productId);
            
            // Close menu
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            
            // Execute action
            if (action === 'view') {
                toggleVariantsView(productId);
            } else if (action === 'edit') {
                editProduct(productId);
            } else if (action === 'delete') {
                deleteProduct(productId);
            }
        });
    });

    // Initialize image carousels for products with multiple variant images
    initializeProductImageCarousels(products);
}

// Initialize carousel for product images with multiple variants
function initializeProductImageCarousels(products) {
    products.forEach(product => {
        if (product.variant_images && product.variant_images.length > 1) {
            const imageCell = document.querySelector(`.product-image-cell[data-product-id="${product.id}"]`);
            const img = imageCell?.querySelector('.product-carousel-img');
            
            if (img) {
                let currentIndex = 0;
                const images = product.variant_images;
                
                img.style.transition = 'opacity 0.3s ease';
                
                // Create carousel
                const carouselInterval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    
                    img.style.opacity = '0';
                    setTimeout(() => {
                        img.src = images[currentIndex];
                        img.style.opacity = '1';
                    }, 300);
                }, 2500);
                
                // Store interval for cleanup
                imageCell.dataset.carouselInterval = carouselInterval;
            }
        }
    });
}

// Cleanup carousel intervals
function cleanupProductCarousels() {
    const imageCells = document.querySelectorAll('.product-image-cell[data-carousel-interval]');
    imageCells.forEach(cell => {
        const intervalId = cell.dataset.carouselInterval;
        if (intervalId) {
            clearInterval(parseInt(intervalId));
            delete cell.dataset.carouselInterval;
        }
    });
}

// Toggle variants view (expand/collapse)
function toggleVariantsView(productId) {
    // Toggle all related rows
    const allVariantRows = document.querySelectorAll(`[id^="variants-${productId}"]`);
    
    // Check if currently visible
    const firstRow = allVariantRows[0];
    const isVisible = firstRow && firstRow.style.display !== 'none';
    
    if (isVisible) {
        // Hide all variant rows
        allVariantRows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Change icon to down
        const viewLink = document.querySelector(`[data-action="view"][data-product-id="${productId}"]`);
        if (viewLink) {
            const icon = viewLink.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-chevron-down';
            }
        }
    } else {
        // Show all variant rows
        allVariantRows.forEach(row => {
            row.style.display = 'table-row';
        });
        
        // Change icon to up
        const viewLink = document.querySelector(`[data-action="view"][data-product-id="${productId}"]`);
        if (viewLink) {
            const icon = viewLink.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-chevron-up';
            }
        }
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    
    if (pagination.last_page <= 1) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';
    container.innerHTML = `
        <div class="pagination-info">
            Showing ${pagination.from || 0} - ${pagination.to || 0} of ${pagination.total}
        </div>
        <div class="pagination-buttons">
            <button class="page-btn" onclick="changePage(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            ${generatePageNumbers(pagination).join('')}
            <button class="page-btn" onclick="changePage(${pagination.current_page + 1})" ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;
}

function generatePageNumbers(pagination) {
    const pages = [];
    for (let i = 1; i <= pagination.last_page; i++) {
        pages.push(`
            <button class="page-btn ${i === pagination.current_page ? 'active' : ''}" onclick="changePage(${i})">
                ${i}
            </button>
        `);
    }
    return pages;
}

function changePage(page) {
    currentFilters.page = page;
    loadProducts();
}

function updateProductCount(total) {
    document.getElementById('product-count').textContent = `${total} produk`;
}

function updateBulkActions() {
    const count = selectedProducts.size;
    const bulkBtn = document.getElementById('bulk-archive-btn');
    const countSpan = document.getElementById('selected-count');
    
    if (count > 0) {
        bulkBtn.style.display = 'inline-flex';
        countSpan.textContent = count;
    } else {
        bulkBtn.style.display = 'none';
    }
}

function openModernDrawer() {
    currentEditId = null;
    window.currentEditId = null;  // IMPORTANT: Reset global variable
    console.log('=== ADD NEW PRODUCT ===');
    console.log('Reset currentEditId to null');
    
    // Reset form jika ada manager
    if (window.modernAddProductManager) {
        window.modernAddProductManager.resetForm();
    }
    document.getElementById('modern-drawer-title').textContent = 'Tambah Produk';
    document.getElementById('modern-drawer-overlay').classList.add('active');
    document.getElementById('modern-product-drawer').classList.add('active');
    
    // Trigger initial subcategory check after drawer is opened
    setTimeout(() => {
        const categorySelect = document.getElementById('modern-product-category');
        if (categorySelect && window.modernAddProductManager) {
            window.modernAddProductManager.updateSubcategoryOptions(categorySelect.value);
        }
    }, 50);
}

function closeModernDrawer() {
    // Reset both local and global edit IDs
    currentEditId = null;
    window.currentEditId = null;
    console.log('Close drawer - Reset currentEditId to null');
    
    document.getElementById('modern-drawer-overlay').classList.remove('active');
    document.getElementById('modern-product-drawer').classList.remove('active');
    if (window.modernAddProductManager) {
        window.modernAddProductManager.resetForm();
    }
}

async function editProduct(id) {
    try {
        console.log('=== EDIT PRODUCT ===');
        console.log('Editing product ID:', id);
        
        const response = await fetch(`/admin/api/products/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            currentEditId = id;
            window.currentEditId = id;  // Also set on window for global access
            console.log('Set currentEditId:', currentEditId);
            console.log('Set window.currentEditId:', window.currentEditId);
            
            // Open modern drawer
            document.getElementById('modern-drawer-title').textContent = `Edit: ${result.data.name}`;
            document.getElementById('modern-drawer-overlay').classList.add('active');
            document.getElementById('modern-product-drawer').classList.add('active');
            
            // Populate form using manager
            if (window.modernAddProductManager) {
                window.modernAddProductManager.populateForm(result.data);
            }
        } else {
            alert('Failed to load product');
        }
    } catch (error) {
        console.error('Error loading product:', error);
        alert('Error loading product');
    }
}


async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        const response = await fetch(`/admin/api/products/${id}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            selectedProducts.delete(id);
            loadProducts();
        } else {
            alert(result.message || 'Failed to delete product');
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        alert('Error deleting product');
    }
}

async function toggleProductStatus(id) {
    try {
        const response = await fetch(`/admin/api/products/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            loadProducts();
        } else {
            alert(result.message || 'Failed to toggle status');
        }
    } catch (error) {
        console.error('Error toggling status:', error);
        alert('Error toggling status');
    }
}

async function bulkArchiveProducts() {
    if (selectedProducts.size === 0) return;
    if (!confirm(`Archive ${selectedProducts.size} product(s)?`)) return;

    try {
        const response = await fetch('/admin/api/products/bulk-archive', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                ids: Array.from(selectedProducts)
            })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            selectedProducts.clear();
            updateBulkActions();
            loadProducts();
        } else {
            alert(result.message || 'Failed to archive products');
        }
    } catch (error) {
        console.error('Error archiving products:', error);
        alert('Error archiving products');
    }
}


// Export products
function exportProducts() {
    const filters = new URLSearchParams({
        status: currentFilters.status,
        search: currentFilters.search,
        category: currentFilters.category,
        export: 'csv'
    });

    // Create temporary link to download
    const link = document.createElement('a');
    link.href = `/admin/api/products/export?${filters.toString()}`;
    link.download = `products_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Group variants by color
function groupVariantsByColor(variants) {
    const groups = {};
    
    variants.forEach(variant => {
        const color = variant.color.toLowerCase();
        if (!groups[color]) {
            groups[color] = {
                color: variant.color,
                variants: []
            };
        }
        groups[color].variants.push(variant);
    });
    
    return Object.values(groups);
}

// Get color name from hex
function getColorName(hex) {
    const colorMap = {
        '#000000': 'Black',
        '#ffffff': 'White',
        '#ff0000': 'Red',
        '#00ff00': 'Green',
        '#0000ff': 'Blue',
        '#ffff00': 'Yellow',
        '#ff00ff': 'Magenta',
        '#00ffff': 'Cyan',
        '#808080': 'Gray',
        '#800000': 'Maroon',
        '#808000': 'Olive',
        '#008000': 'Dark Green',
        '#800080': 'Purple',
        '#008080': 'Teal',
        '#000080': 'Navy',
        '#ffa500': 'Orange',
        '#ffc0cb': 'Pink',
        '#a52a2a': 'Brown',
        '#f0e68c': 'Khaki',
        '#4b5320': 'Army'
    };
    
    const lowerHex = hex.toLowerCase();
    return colorMap[lowerHex] || hex;
}

// Generate SKU
function generateSKU(productId, color, size) {
    const colorCode = color.substring(1, 4).toUpperCase();
    return `OL-${colorCode}-${size.toUpperCase()}`;
}

// Global functions for onclick handlers
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;
window.toggleProductStatus = toggleProductStatus;
window.changePage = changePage;
window.loadProducts = loadProducts;
window.toggleVariantsView = toggleVariantsView;
