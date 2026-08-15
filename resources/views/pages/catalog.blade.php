<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $categoryName }} - LGI Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @vite(['resources/css/guest/catalog.css', 'resources/css/guest/catalog-inline.css', 'resources/css/components/footer.css', 'resources/css/components/product-card.css', 'resources/js/guest/catalog.js', 'resources/js/guest/product-card-carousel.js'])
</head>
<body>
    @php
        $selectedColors = array_unique($currentFilters['colors'] ?? []);
        $selectedSizes = array_unique($currentFilters['sizes'] ?? []);
        $selectedSubcategories = array_unique($currentFilters['subcategories'] ?? []);
        $isPromo = $currentFilters['promo'] ?? false;
        $isReady = $currentFilters['ready'] ?? false;
        $isCustom = $currentFilters['custom'] ?? false;
        $minPriceValue = request('min_price', 0);
        $maxPriceValue = request('max_price', 2500000);
        $minPriceDisplay = 'Rp ' . number_format($minPriceValue, 0, ',', '.');
        $maxPriceDisplay = 'Rp ' . number_format($maxPriceValue, 0, ',', '.');
    @endphp
    <x-navbar />

    <section class="catalog-breadcrumb">
        <div class="breadcrumb-container">
            <a href="{{ route('home') }}" class="breadcrumb-back">
                <i class="fas fa-chevron-left"></i>
                Kembali ke beranda
            </a>
        </div>
    </section>

    <section class="catalog-section">
        <div class="container">
            <div class="content-wrapper-new">
                <aside class="sidebar">
                    <div class="filter-container">
                        <!-- Quick Filters -->
                        <div class="filter-section">
                            <div class="filter-checkbox-list">
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="promo" id="promo-filter" {{ $isPromo ? 'checked' : '' }}>
                                    <span>Dengan diskon</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="ready" id="ready-filter" {{ $isReady ? 'checked' : '' }}>
                                    <span>Ready stok</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="custom" id="custom-filter" {{ $isCustom ? 'checked' : '' }}>
                                    <span>Kustomisasi</span>
                                </label>
                            </div>
                        </div>

                        <!-- Subcategory Filter -->
                        <div class="filter-section">
                            <div class="filter-title-row">
                                <span class="filter-title">Sub Kategori</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="filter-checkbox-list">
                                @forelse ($availableSubcategories as $subcategory)
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="subcategories[]" value="{{ $subcategory }}" {{ in_array($subcategory, $selectedSubcategories) ? 'checked' : '' }}>
                                        <span>{{ ucwords(str_replace('-', ' ', $subcategory)) }}</span>
                                    </label>
                                @empty
                                    <span class="no-filter-option">Tidak ada sub kategori tersedia</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Colors Filter -->
                        <div class="filter-section">
                            <div class="filter-title-row">
                                <span class="filter-title">Colors</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="color-options">
                                @forelse ($availableColors as $color)
                                    @php
                                        $normalizedColor = strtolower($color);
                                        $needsBorder = in_array($normalizedColor, ['#fff', '#ffffff', 'white']);
                                    @endphp
                                    <button type="button"
                                        class="color-btn{{ in_array($color, $selectedColors, true) ? ' active' : '' }}"
                                        data-color="{{ $color }}"
                                        style="background-color: {{ $color }};{{ $needsBorder ? ' border:1px solid #e5e7eb;' : '' }}">
                                    </button>
                                @empty
                                    <span class="no-filter-option">Tidak ada warna tersedia</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Size Filter -->
                        <div class="filter-section">
                            <div class="filter-title-row">
                                <span class="filter-title">Size</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="size-options">
                                @forelse ($availableSizes as $size)
                                    <button type="button"
                                        class="size-btn{{ in_array($size, $selectedSizes, true) ? ' active' : '' }}"
                                        data-size="{{ $size }}">
                                        {{ $size }}
                                    </button>
                                @empty
                                    <span class="no-filter-option">Tidak ada ukuran tersedia</span>
                                @endforelse
                            </div>
                        </div>



                        <!-- Harga Section -->
                        <div class="filter-section">
                            <div class="filter-title-row">
                                <span class="filter-title">Harga</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="price-range-wrapper">
                                <div class="price-inputs">
                                    <input type="text" class="price-input" id="min-price" placeholder="Rp 0" value="Rp 0">
                                    <span class="price-separator">-</span>
                                    <input type="text" class="price-input" id="max-price" placeholder="Rp 2.500.000" value="Rp 2.500.000">
                                </div>
                                <div class="price-slider-container">
                                    <input type="range" id="price-range-min" min="0" max="2500000" value="0">
                                    <input type="range" id="price-range-max" min="0" max="2500000" value="2500000">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="apply-filter-btn">Apply Filter</button>
                    </div>
                </aside>

                <main class="products-section">
                    <div class="products-header-inline">
                        <div class="header-title-section">
                            <h1 class="page-title-inline">{{ $categoryName }}</h1>
                            <span class="products-count-inline" id="products-count">Menampilkan {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $totalProducts }} Produk</span>
                        </div>
                        <div class="header-sort-section">
                            <label for="sort-select">Urut berdasarkan:</label>
                            <select class="sort-select" id="sort-select">
                                <option value="most_popular" {{ request('sort') == 'most_popular' ? 'selected' : '' }}>Paling Populer</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                            </select>
                        </div>
                    </div>

                    <div class="products-grid" id="products-grid">
                        @forelse($products as $product)
                            <x-product-card :product="$product" />
                        @empty
                            <div class="no-products">
                                <i class="fas fa-inbox"></i>
                                <p>Tidak ada produk dalam kategori ini</p>
                            </div>
                        @endforelse
                    </div>

                    @if($products->hasPages())
                        <div class="pagination" id="pagination-container">
                            @if ($products->onFirstPage())
                                <button class="pagination-btn" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                    Sebelumnya
                                </button>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="pagination-btn pagination-link">
                                    <i class="fas fa-chevron-left"></i>
                                    Sebelumnya
                                </a>
                            @endif

                            <div class="pagination-numbers">
                                @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                    @if ($page == $products->currentPage())
                                        <button class="pagination-number active">{{ $page }}</button>
                                    @else
                                        <a href="{{ $url }}" class="pagination-number pagination-link">{{ $page }}</a>
                                    @endif
                                @endforeach
                            </div>

                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="pagination-btn pagination-link">
                                    Selanjutnya
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <button class="pagination-btn" disabled>
                                    Selanjutnya
                                    <i class="fas fa-chevron-right"></i>
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
        document.addEventListener('DOMContentLoaded', () => {
            const colorButtons = document.querySelectorAll('.color-btn');
            const sizeButtons = document.querySelectorAll('.size-btn');
            const subcategoryCheckboxes = document.querySelectorAll('input[name="subcategories[]"]');
            const quickFilters = {
                promo: document.getElementById('promo-filter'),
                ready: document.getElementById('ready-filter'),
                custom: document.getElementById('custom-filter')
            };
            const applyFilterBtn = document.querySelector('.apply-filter-btn');
            const sortSelect = document.getElementById('sort-select');

            const colorSelections = new Set(@json($selectedColors));
            const sizeSelections = new Set(@json($selectedSizes));
            
            // Price slider functionality
            const minPriceInput = document.getElementById('min-price');
            const maxPriceInput = document.getElementById('max-price');
            const minRange = document.getElementById('price-range-min');
            const maxRange = document.getElementById('price-range-max');
            const priceSliderRange = document.querySelector('.price-slider-range');

            // Initialize slider values
            let minValue = parseInt(minRange.value) || 0;
            let maxValue = parseInt(maxRange.value) || 2500000;

            // Function to update slider track
            function updateSliderTrack() {
                if (!priceSliderRange) return;
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



            applyFilterBtn.addEventListener('click', function() {
                const selectedFilters = {
                    promo: document.getElementById('promo-filter')?.checked || false,
                    ready: document.getElementById('ready-filter')?.checked || false,
                    custom: document.getElementById('custom-filter')?.checked || false,
                    subcategories: [],
                    min_price: document.getElementById('price-range-min')?.value || 0,
                    max_price: document.getElementById('price-range-max')?.value || 2500000,
                    sort: document.getElementById('sort-select')?.value || 'most_popular'
                };

                // Get selected subcategories
                const subcategoryCheckboxes = document.querySelectorAll('input[name="subcategories[]"]:checked');
                subcategoryCheckboxes.forEach(cb => {
                    selectedFilters.subcategories.push(cb.value);
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
                fetch(`${window.location.pathname}?${params.toString()}`, {
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
                    // Update products grid
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

                    // Update pagination
                    // Update products count
                    const productsCount = document.getElementById('products-count');
                    if (data.pagination) {
                        productsCount.textContent = `Menampilkan ${data.pagination.from || 0}-${data.pagination.to || 0} dari ${data.pagination.total} Produk.`;
                    }

                    // Re-initialize carousels and click handlers
                    if (typeof window.initializeProductCarousels === 'function') {
                        window.initializeProductCarousels();
                    }
                    if (typeof window.initializeProductCardClicks === 'function') {
                        window.initializeProductCardClicks();
                    }
                })
                .catch(error => {
                    console.error('Error applying filters:', error);
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

            sortSelect?.addEventListener('change', () => {
                const url = new URL(window.location.href);
                const params = url.searchParams;
                params.set('sort', sortSelect.value);
                params.delete('page');
                window.location.href = `${url.pathname}?${params.toString()}`;
            });
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