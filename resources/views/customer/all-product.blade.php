<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LGI Store - Semua Produk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/guest/catalog.css', 'resources/css/components/footer.css', 'resources/css/customer/all-product.css', 'resources/css/components/product-card.css', 'resources/js/guest/catalog.js', 'resources/js/guest/product-card-carousel.js'])
</head>
<body>
    @php
        $selectedCategories = $appliedFilters['categories'] ?? [];
        $minPriceValue = $appliedFilters['min_price'] ?? null;
        $maxPriceValue = $appliedFilters['max_price'] ?? null;
        $minPriceDisplay = 'Rp ' . number_format($minPriceValue ?? 0, 0, ',', '.');
        $maxPriceDisplay = 'Rp ' . number_format($maxPriceValue ?? 2500000, 0, ',', '.');
        $promoChecked = $appliedFilters['promo'] ?? false;
        $readyChecked = $appliedFilters['ready'] ?? false;
        $customChecked = $appliedFilters['custom'] ?? false;
    @endphp
    <x-navbar />

    <section class="catalog-breadcrumb-section">
        <div class="catalog-inner-container">
            <nav aria-label="breadcrumb" class="catalog-breadcrumb-nav">
                <nav class="breadcrumb">
                    <a href="{{ route('home') }}">
                        <i class="fas fa-chevron-left"></i>
                        Kembali ke beranda
                    </a>
                </nav>
            </nav>
        </div>
    </section>

    <section class="catalog-content-section">
        <div class="catalog-inner-container">
            <div class="catalog-layout">
                <aside class="sidebar">
                    <div class="filters-card filters-section">
                        <div class="filter-header">
                            <h5>Filters</h5>
                            <i class="fas fa-sliders-h"></i>
                        </div>

                        <!-- Promo dan lainnya Section -->
                        <div class="filter-checkbox-section">
                            <div class="checkbox-item">
                                <input type="checkbox" id="promo-diskon" name="promo" {{ $promoChecked ? 'checked' : '' }}>
                                <label for="promo-diskon">Dengan diskon</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ready-stok" name="ready" {{ $readyChecked ? 'checked' : '' }}>
                                <label for="ready-stok">Ready stok</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="kustomisasi" name="custom" {{ $customChecked ? 'checked' : '' }}>
                                <label for="kustomisasi">Kustomisasi</label>
                            </div>
                        </div>

                        <!-- Kategori Section -->
                        <div class="filter-group-section">
                            <div class="filter-group-title">
                                <h4>Kategori</h4>
                            </div>
                            <div class="filter-checkbox-list">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-topi" name="categories[]" value="topi" {{ in_array('topi', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-topi">Topi</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-celana" name="categories[]" value="celana" {{ in_array('celana', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-celana">Celana</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-polo" name="categories[]" value="polo" {{ in_array('polo', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-polo">Polo</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-jaket" name="categories[]" value="jaket" {{ in_array('jaket', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-jaket">Jaket</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-jersey" name="categories[]" value="jersey" {{ in_array('jersey', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-jersey">Jersey</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-kaos" name="categories[]" value="kaos" {{ in_array('kaos', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-kaos">Kaos</label>
                                </div>
                                <!-- <div class="checkbox-item">
                                    <input type="checkbox" id="kategori-lainlain" name="categories[]" value="lain-lain" {{ in_array('lain-lain', $selectedCategories, true) ? 'checked' : '' }}>
                                    <label for="kategori-lainlain">Lain-lain</label>
                                </div> -->
                            </div>
                        </div>

                        <!-- Harga Section -->
                        <div class="filter-group-section">
                            <div class="filter-group-title">
                                <h4>Harga</h4>
                            </div>
                            <div class="price-range-wrapper">
                                <div class="price-inputs">
                                    <input type="text" class="price-input" id="min-price" placeholder="Rp 0" value="{{ $minPriceDisplay }}">
                                    <span class="price-separator">-</span>
                                    <input type="text" class="price-input" id="max-price" placeholder="Rp 2.500.000" value="{{ $maxPriceDisplay }}">
                                </div>
                                <div class="price-slider-container">
                                    <input type="range" id="price-range-min" min="0" max="2500000" value="{{ $minPriceValue ?? 0 }}">
                                    <input type="range" id="price-range-max" min="0" max="2500000" value="{{ $maxPriceValue ?? 2500000 }}">
                                    <div class="price-slider-track"></div>
                                    <div class="price-slider-range"></div>
                                </div>
                            </div>
                        </div>

                        <button class="apply-filter-btn">Apply Filter</button>
                    </div>
                </aside>

                <main class="products-main">
                    <div class="products-header">
                        <div class="header-center">
                            <h2>Semua Produk</h2>
                            <small class="products-count" id="products-count">Menampilkan {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} Produk</small>
                        </div>
                        
                        <div class="header-right">
                            <label for="sort-select">Urut berdasarkan:</label>
                            <div class="select-wrapper">
                                <select id="sort-select">
                                    <option value="most_popular" {{ request('sort') == 'most_popular' ? 'selected' : '' }}>Paling Populer</option>
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="products-grid" id="products-grid">
                        @forelse($products as $product)
                            <x-product-card :product="$product" />
                        @empty
                            <div class="no-products" style="grid-column: 1 / -1;">
                                <i class="fas fa-inbox"></i>
                                <p>Produk tidak ditemukan</p>
                            </div>
                        @endforelse
                    </div>

                    @if($products->hasPages())
                    <div class="pagination" id="pagination-container">
                        @if ($products->onFirstPage())
                            <button class="pagination-btn prev" disabled>
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </button>
                        @else
                            <a href="{{ $products->previousPageUrl() }}" class="pagination-btn prev pagination-link">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                        @endif

                        <div class="pagination-numbers">
                            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                @if ($page == $products->currentPage())
                                    <button class="page-number active">{{ $page }}</button>
                                @else
                                    <a href="{{ $url }}" class="page-number pagination-link">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>

                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="pagination-btn next pagination-link">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="pagination-btn next" disabled>
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                    @endif
                </main>
            </div>
        </div>
    </section>

    <x-guest-footer />



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Checkbox functionality
            const checkboxes = document.querySelectorAll('.checkbox-item input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    console.log(`${this.id} is ${this.checked ? 'checked' : 'unchecked'}`);
                });
            });

            // Price slider functionality
            const minPriceInput = document.getElementById('min-price');
            const maxPriceInput = document.getElementById('max-price');
            const minRange = document.getElementById('price-range-min');
            const maxRange = document.getElementById('price-range-max');
            const priceSliderTrack = document.querySelector('.price-slider-track');
            const priceSliderRange = document.querySelector('.price-slider-range');

            // Initialize slider values
            let minValue = parseInt(minRange.value) || 0;
            let maxValue = parseInt(maxRange.value) || 2500000;

            // Function to update slider track
            function updateSliderTrack() {
                const percent1 = (minValue / 2500000) * 100;
                const percent2 = (maxValue / 2500000) * 100;
                priceSliderRange.style.left = percent1 + '%';
                priceSliderRange.style.width = (percent2 - percent1) + '%';
            }

            // Update input values when slider changes
            minRange.addEventListener('input', function() {
                minValue = parseInt(this.value);
                if (minValue > maxValue - 10000) {
                    minValue = maxValue - 10000;
                    this.value = minValue;
                }
                minPriceInput.value = 'Rp ' + minValue.toLocaleString('id-ID');
                updateSliderTrack();
            });

            maxRange.addEventListener('input', function() {
                maxValue = parseInt(this.value);
                if (maxValue < minValue + 10000) {
                    maxValue = minValue + 10000;
                    this.value = maxValue;
                }
                maxPriceInput.value = 'Rp ' + maxValue.toLocaleString('id-ID');
                updateSliderTrack();
            });

            // Update slider when input changes
            minPriceInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value) {
                    minValue = parseInt(value);
                    if (minValue > maxValue - 10000) minValue = maxValue - 10000;
                    minRange.value = minValue;
                    e.target.value = 'Rp ' + minValue.toLocaleString('id-ID');
                    updateSliderTrack();
                }
            });

            maxPriceInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value) {
                    maxValue = parseInt(value);
                    if (maxValue < minValue + 10000) maxValue = minValue + 10000;
                    maxRange.value = maxValue;
                    e.target.value = 'Rp ' + maxValue.toLocaleString('id-ID');
                    updateSliderTrack();
                }
            });

            // Handle input blur
            minPriceInput.addEventListener('blur', function(e) {
                if (!e.target.value || e.target.value === 'Rp ') {
                    minValue = 0;
                    minRange.value = 0;
                    e.target.value = 'Rp 0';
                    updateSliderTrack();
                }
            });

            maxPriceInput.addEventListener('blur', function(e) {
                if (!e.target.value || e.target.value === 'Rp ') {
                    maxValue = 2500000;
                    maxRange.value = 2500000;
                    e.target.value = 'Rp 2.500.000';
                    updateSliderTrack();
                }
            });

            // Initialize slider track on page load
            updateSliderTrack();

            // Apply Filter Button
            const applyFilterBtn = document.querySelector('.apply-filter-btn');
            applyFilterBtn.addEventListener('click', function() {
                const selectedFilters = {
                    promo: document.getElementById('promo-diskon')?.checked || false,
                    ready: document.getElementById('ready-stok')?.checked || false,
                    custom: document.getElementById('kustomisasi')?.checked || false,
                    categories: [],
                    min_price: document.getElementById('price-range-min')?.value || 0,
                    max_price: document.getElementById('price-range-max')?.value || 2500000,
                    sort: document.getElementById('sort-select')?.value || 'most_popular'
                };

                // Get selected categories
                const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]:checked');
                categoryCheckboxes.forEach(cb => {
                    selectedFilters.categories.push(cb.value);
                });

                console.log('Applying filters:', selectedFilters);

                // Show loading state
                applyFilterBtn.textContent = 'Memuat...';
                applyFilterBtn.disabled = true;

                // Build query string from selected filters
                const params = new URLSearchParams();

                if (selectedFilters.promo) params.append('promo', '1');
                if (selectedFilters.ready) params.append('ready', '1');
                if (selectedFilters.custom) params.append('custom', '1');

                if (selectedFilters.categories.length > 0) {
                    params.append('categories', selectedFilters.categories.join(','));
                }

                if (selectedFilters.min_price) {
                    params.append('min_price', selectedFilters.min_price);
                }

                if (selectedFilters.max_price) {
                    params.append('max_price', selectedFilters.max_price);
                }

                if (selectedFilters.sort) {
                    params.append('sort', selectedFilters.sort);
                }

                // Make AJAX request to filter products
                fetch(`/all-products?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    updateProductsGrid(data);
                    updatePagination(data);
                    updateProductsCount(data);
                })
                .catch(error => {
                    console.error('Error applying filters:', error);
                    // Only show alert for actual errors, not for successful responses
                    if (error.message.includes('HTTP error')) {
                        alert('Terjadi kesalahan saat menerapkan filter. Silakan coba lagi.');
                    }
                })
                .finally(() => {
                    // Reset button state
                    applyFilterBtn.textContent = 'Apply Filter';
                    applyFilterBtn.disabled = false;
                });
            });

            // Function to update products grid
            function updateProductsGrid(data) {
                const productsGrid = document.getElementById('products-grid');
                const products = data.products;

                if (products.length === 0) {
                    productsGrid.innerHTML = `
                        <div class="no-products" style="grid-column: 1 / -1;">
                            <i class="fas fa-inbox"></i>
                            <p>Produk tidak ditemukan</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                products.forEach(product => {
                    // Priority: variant_images[0] > product.image > placeholder
                    let imageUrl = '';
                    if (product.variant_images && product.variant_images.length > 0) {
                        imageUrl = product.variant_images[0];
                    } else if (product.image) {
                        // Detect if it's already a full URL or needs to be converted to storage path
                        if (product.image.startsWith('http://') || product.image.startsWith('https://')) {
                            imageUrl = product.image;
                        } else {
                            // Use proper path for Hostinger production
                            if (window.location.hostname.includes('sablontopilampung.com') || window.location.hostname.includes('production')) {
                                imageUrl = `/public/storage/${product.image}`;
                            } else {
                                imageUrl = `/storage/${product.image}`;
                            }
                        }
                    }
                    
                    const variantImagesJson = JSON.stringify(product.variant_images || []).replace(/'/g, '&#39;');
                    const customRibbon = product.custom_design_allowed ? '<div class="product-ribbon" aria-hidden="true">CUSTOM</div>' : '';
                    const imageHtml = imageUrl 
                        ? `<img class="product-image" src="${imageUrl}" alt="${product.name}" onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\\'no-image-placeholder\\'><i class=\\'fas fa-image\\'></i></div>';">`
                        : `<div class="no-image-placeholder"><i class="fas fa-image"></i></div>`;
                    
                    const formattedPrice = product.formatted_price || 'Rp ' + parseInt(product.price).toLocaleString('id-ID');

                    html += `
                        <div class="product-card"
                             data-product-id="${product.id}"
                             data-product-slug="${product.slug || ''}"
                             data-product-name="${product.name}"
                             data-product-price="${formattedPrice}"
                             data-product-image="${imageUrl}"
                             data-variant-images='${variantImagesJson}'>
                            <div class="product-image-container" data-product-id="${product.id}">
                                ${imageHtml}
                                ${customRibbon}
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">${product.name}</h3>
                                <p class="product-price">${formattedPrice}</p>
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
                });

                productsGrid.innerHTML = html;
                
                // Re-initialize carousels and click handlers using product-card-carousel.js functions
                if (typeof window.initializeProductCarousels === 'function') {
                    window.initializeProductCarousels();
                }
                if (typeof window.initializeProductCardClicks === 'function') {
                    window.initializeProductCardClicks();
                }
            }

            // Function to update pagination
            function updatePagination(data) {
                const paginationContainer = document.getElementById('pagination-container');
                const pagination = data.pagination;

                if (!pagination || pagination.total <= pagination.per_page) {
                    paginationContainer.style.display = 'none';
                    return;
                }

                paginationContainer.style.display = 'flex';

                // Update pagination links (simplified version)
                // You might need to implement full pagination logic here
                // For now, we'll just update the current page info
            }

            // Function to update products count
            function updateProductsCount(data) {
                const productsCount = document.getElementById('products-count');
                const pagination = data.pagination;

                if (pagination) {
                    productsCount.textContent = `Menampilkan ${pagination.from || 0}-${pagination.to || 0} dari ${pagination.total} Produk.`;
                }
            }

            // Sort select functionality (existing code)
            const sortSelect = document.getElementById('sort-select');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const currentUrl = new URL(window.location);
                    if (this.value) {
                        currentUrl.searchParams.set('sort', this.value);
                    }
                    window.location.href = currentUrl.toString();
                });
            }
        });
    </script>

    <!-- Unified Chatbot Popup Component - Only show for logged in customers -->
    @auth('web')
        @if(!auth()->guard('admin')->check())
            <x-unified-chatbot-popup />
        @endif
    @endauth
</body>
</html>
