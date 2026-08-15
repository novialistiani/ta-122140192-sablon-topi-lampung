<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product['name'] ?? 'Detail Produk' }} - LGI Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/guest/product-detail.css', 'resources/css/components/navbar.css', 'resources/css/components/footer.css', 'resources/js/guest/product-detail.js', 'resources/js/components/navbar.js'])
</head>
<body class="product-detail-page">
    <x-navbar />

    @php
        $primaryImage = $product['image'] ?? 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500&h=500&fit=crop';
        $gallery = $product['gallery'] ?? [];
        $variants = $product['variants'] ?? [];
        
        // Gallery is now an array of objects with url, color, size
        if (empty($gallery)) {
            $gallery = [['url' => $primaryImage, 'color' => null, 'size' => null]];
        }

        $colors = $product['colors'] ?? [];
        if (is_string($colors)) {
            $decodedColors = json_decode($colors, true);
            if (is_array($decodedColors)) {
                $colors = $decodedColors;
            }
        }
        if (empty($colors)) {
            $colors = [];
        }

        $sizes = $product['sizes'] ?? [];
        if (is_string($sizes)) {
            $decodedSizes = json_decode($sizes, true);
            if (is_array($decodedSizes)) {
                $sizes = $decodedSizes;
            }
        }
        if (empty($sizes)) {
            $sizes = [];
        }

        $price = $product['price'] ?? '0';
        $priceMin = $product['price_min'] ?? $price;
        $priceMax = $product['price_max'] ?? $price;
        
        // Display price range if variants have different prices
        if ($priceMin != $priceMax) {
            $priceDisplay = 'Rp ' . number_format((float) $priceMin, 0, ',', '.') . ' - Rp ' . number_format((float) $priceMax, 0, ',', '.');
        } else {
            $priceDisplay = 'Rp ' . number_format((float) $priceMin, 0, ',', '.');
        }
        
        // Harga coret tidak ditampilkan di awal karena per variant
        // Akan ditampilkan setelah user memilih variant melalui JavaScript
        $originalPriceDisplay = null;

        $description = $product['description'] ?? 'Produk ini dibuat dengan material berkualitas tinggi yang nyaman digunakan sepanjang hari.';
        $category = $product['category'] ?? 'Umum';
        $stock = $product['stock'] ?? 0;
        $customAllowed = (bool)($product['custom_design_allowed'] ?? false);

        // ===== PERBAIKAN: Format colors dan sizes untuk chatbot =====
    $chatbotColors = [];
    $chatbotSizes = [];

    // Process colors untuk chatbot
    foreach ($colors as $color) {
        if (is_array($color)) {
            $colorValue = $color['value'] ?? $color['hex'] ?? $color['name'] ?? '#000000';
            $colorLabel = $color['label'] ?? $color['name'] ?? $colorValue;
        } else {
            $colorValue = $color;
            $colorLabel = $color;
        }
        $chatbotColors[] = [
            'value' => $colorValue,
            'label' => $colorLabel
        ];
    }

    // Process sizes untuk chatbot  
    foreach ($sizes as $size) {
        if (is_array($size)) {
            $sizeValue = $size['value'] ?? $size['name'] ?? $size;
            $sizeLabel = $size['label'] ?? $size['name'] ?? $sizeValue;
        } else {
            $sizeValue = $size;
            $sizeLabel = $size;
        }
        $chatbotSizes[] = [
            'value' => $sizeValue,
            'label' => $sizeLabel
        ];
    }

    // Jika tidak ada colors/sizes dari database, gunakan default
    if (empty($chatbotColors)) {
        $chatbotColors = [['value' => 'default', 'label' => 'Standar']];
    }
    if (empty($chatbotSizes)) {
        $chatbotSizes = [['value' => 'default', 'label' => 'Standar']];
    }
    @endphp

    <nav class="breadcrumb">
        <div class="breadcrumb-inner">
            <a href="{{ route('home') }}" class="breadcrumb-link">
                <span aria-hidden="true">&lt;</span>
                <span>Beranda</span>
            </a>
            <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
            <span class="breadcrumb-current">{{ $product['name'] ?? 'Produk' }}</span>
        </div>
    </nav>

    <main class="product-page" data-product-id="{{ $product['id'] ?? '' }}">
        <section class="product-hero">
            <div class="product-gallery">
                <div class="thumbnail-list" role="tablist" aria-label="Galeri produk">
                    @foreach($gallery as $index => $item)
                        @php
                            $imageUrl = is_array($item) ? $item['url'] : $item;
                            $imageColor = is_array($item) ? ($item['color'] ?? '') : '';
                            $imageSize = is_array($item) ? ($item['size'] ?? '') : '';
                        @endphp
                        <button type="button" class="thumbnail{{ $loop->first ? ' active' : '' }}" 
                                data-image="{{ $imageUrl }}" 
                                data-color="{{ $imageColor }}" 
                                data-size="{{ $imageSize }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}" 
                                aria-label="Gambar {{ $loop->iteration }}">
                            <img src="{{ $imageUrl }}" alt="Thumbnail {{ $loop->iteration }} {{ $product['name'] ?? 'Produk' }}" loading="lazy" decoding="async" width="110" height="110">
                        </button>
                    @endforeach
                </div>
                <div class="main-image">
                    @php
                        $firstImage = is_array($gallery[0]) ? $gallery[0]['url'] : $gallery[0];
                    @endphp
                    <img id="mainImage" src="{{ $firstImage }}" alt="{{ $product['name'] ?? 'Produk' }}" loading="lazy" decoding="async">
                </div>
            </div>

            <div class="product-info">
                <h1 class="product-title">{{ $product['name'] ?? 'Produk Tanpa Nama' }}</h1>
                @if(!empty($product['subcategory']))
                <p class="product-subcategory">{{ ucwords(str_replace('-', ' ', $product['subcategory'])) }}</p>
                @endif
                <div class="product-price" id="productPrice" data-base-price="{{ $priceMin }}">
                    @if($originalPriceDisplay)
                        <span class="original-price" id="originalPrice">{{ $originalPriceDisplay }}</span>
                    @endif
                    <span class="current-price">{{ $priceDisplay }}</span>
                </div>
                @php
                    $hasVariants = !empty($variants) && count($variants) > 0;
                    $baseStock = $product['stock'] ?? 0;
                @endphp
                <p class="product-stock" id="productStock">
                    @if($hasVariants)
                        Pilih varian untuk melihat stok
                    @else
                        @if($baseStock > 0)
                            <span class="text-green-600 font-medium"><i class="fas fa-check-circle"></i> Stok tersedia: {{ $baseStock }} item</span>
                        @else
                            <span class="text-red-600 font-bold"><i class="fas fa-times-circle"></i> Stok habis</span>
                        @endif
                    @endif
                </p>
                <p class="product-description">{{ $description }}</p>

                @if(!empty($colors) || !empty($sizes))
                <div class="options-row">
                    @if(!empty($colors))
                    <div class="option-group">
                        <h2 class="option-label">Pilih Warna</h2>
                        <div class="color-options" role="radiogroup" aria-label="Pilihan warna">
                            @foreach($colors as $index => $color)
                                @php
                                    $colorValue = is_array($color) ? ($color['value'] ?? $color['hex'] ?? '#000000') : $color;
                                    $colorLabel = is_array($color) ? ($color['label'] ?? $colorValue) : $colorValue;
                                @endphp
                                <button type="button" class="color-swatch" style="--swatch-color: {{ $colorValue }}" data-color="{{ $colorValue }}" aria-label="Warna {{ $colorLabel }}" aria-pressed="false" title="{{ $colorLabel }}"></button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($sizes))
                    <div class="option-group">
                        <h2 class="option-label">Pilih Ukuran</h2>
                        <div class="size-options" role="radiogroup" aria-label="Pilihan ukuran">
                            @foreach($sizes as $size)
                                <button type="button" class="size-option" data-size="{{ $size }}" aria-pressed="false">{{ $size }}</button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <div class="purchase-actions">
                    <form id="addToCartForm" method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product['id'] ?? '' }}">
                        <input type="hidden" name="variant_id" id="cartVariantIdInput" value="">
                        <input type="hidden" name="quantity" id="cartQuantityInput" value="1">
                        <input type="hidden" name="color" id="cartColorInput" value="">
                        <input type="hidden" name="size" id="cartSizeInput" value="">
                    </form>
                    
                    <!-- Hidden variants data for JavaScript -->
                    <script id="variantsData" type="application/json">
                        {!! json_encode($variants) !!}
                    </script>

                    <div class="button-row-top">
                        @if(!auth()->guard('admin')->check())
                            {{-- Only show purchase buttons to customers, not admin --}}
                            @php
                                // Disable buttons if no stock (for products without variants)
                                $isOutOfStock = !$hasVariants && $baseStock <= 0;
                                $disableAttr = $isOutOfStock ? 'disabled' : '';
                            @endphp
                            
                            @if($isOutOfStock)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 text-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl mb-2"></i>
                                    <p class="text-red-800 font-bold">Stok Habis</p>
                                    <p class="text-red-600 text-sm mt-1">Produk ini sedang tidak tersedia.</p>
                                </div>
                            @endif
                            
                            <div class="quantity-wrapper">
                                <div class="quantity-selector" aria-label="Pilih kuantitas">
                                    <button type="button" class="quantity-btn" data-quantity-action="decrease" aria-label="Kurangi jumlah" {{ $disableAttr }}>−</button>
                                    <input type="number" class="quantity-input" id="quantityValue" value="1" min="1" max="99" aria-live="polite" {{ $disableAttr }}>
                                    <button type="button" class="quantity-btn" data-quantity-action="increase" aria-label="Tambah jumlah" {{ $disableAttr }}>+</button>
                                </div>
                                <div class="quantity-warning" id="quantityWarning">
                                    <i class="fas fa-exclamation-triangle"></i> Jumlah produk tidak mencukupi
                                </div>
                            </div>

                            <button type="button" class="buy-now-btn" data-product-name="{{ $product['name'] ?? '' }}" {{ $disableAttr }}>
                                {{ $isOutOfStock ? 'Stok Habis' : 'Beli Sekarang' }}
                            </button>
                            
                            <button type="button" class="custom-design-btn" data-product-id="{{ $product['id'] ?? '' }}" title="Custom Design" @if(!$customAllowed) disabled @endif>
                                <i class="fas fa-palette"></i>
                            </button>

                            <button type="button" class="chat-btn" id="productChatBtn" title="Chat" 
                                data-product-id="{{ $product['id'] ?? '' }}"
                                data-product-name="{{ $product['name'] ?? '' }}"
                                data-product-price="{{ $product['price'] ?? 0 }}"
                                data-custom-allowed="{{ $customAllowed ? 'true' : 'false' }}">
                                <i class="fas fa-comment-dots"></i>
                            </button>
                            
                            <button type="button" class="add-to-cart-btn" data-product-name="{{ $product['name'] ?? '' }}" title="Keranjang" {{ $disableAttr }}>
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        @else
                            {{-- Show message for admin --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <i class="fas fa-info-circle text-yellow-600 text-2xl mb-2"></i>
                                <p class="text-yellow-800 font-medium">Anda login sebagai Admin</p>
                                <p class="text-yellow-600 text-sm mt-1">Admin tidak dapat melakukan pemesanan produk.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="recommendations">
            <h2 class="recommendations-title">Mungkin Kamu Juga Suka</h2>
            <div class="recommendation-grid">
                @foreach($recommendations as $item)
                    @php
                        $recId = $item['id'] ?? ($item->id ?? null);
                        $recName = $item['name'] ?? ($item->name ?? 'Produk');
                        $recPrice = $item['price'] ?? ($item->formatted_price ?? '0');
                        $recImage = $item['image'] ?? (isset($item->image) ? asset('storage/'.$item->image) : 'https://via.placeholder.com/300');
                    @endphp
                    <div class="recommendation-card" data-product-id="{{ $recId }}" tabindex="0">
                        <a href="{{ route('product.detail', ['id' => $recId, 'name' => $recName, 'price' => $recPrice, 'image' => $recImage]) }}" class="recommendation-link" aria-label="Lihat {{ $recName }}">
                            <div class="recommendation-image">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" width="200" height="160" onerror="this.src='https://via.placeholder.com/200x160?text=No+Image'">
                                @if(!empty($item['custom_design_allowed']) && $item['custom_design_allowed'])
                                    <div class="product-ribbon small" aria-hidden="true">CUSTOM</div>
                                @endif
                            </div>
                            <div class="recommendation-info">
                                <h3 class="recommendation-name">{{ $item['name'] }}</h3>
                                <p class="recommendation-price">Rp {{ $item['price'] }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Unified Chatbot Popup - Only show for logged in customers -->
        @auth('web')
            @if(!auth()->guard('admin')->check())
                <x-unified-chatbot-popup />
            @endif
        @endauth
    </main>

    <x-guest-footer />
    <!-- <script>
        // Product data for chatbot
        const productData = {
            id: {{ $product['id'] ?? 'null' }},
            name: "{{ $product['name'] ?? 'Produk' }}",
            price: {{ $product['price'] ?? 0 }},
            price_min: {{ $priceMin }},
            price_max: {{ $priceMax }},
            stock: {{ $stock }},
            colors: {!! json_encode($colors) !!},
            sizes: {!! json_encode($sizes) !!},
            custom_allowed: {{ $customAllowed ? 'true' : 'false' }},
            description: `{{ $description }}`,
            category: "{{ $category }}"
        };

        console.log('Product Data:', productData);
    </script> -->
    <script>
        // Product data for chatbot - WITH PROPER COLOR AND SIZE DATA
        const productData = {
            id: {{ $product['id'] ?? 'null' }},
            name: "{{ addslashes($product['name'] ?? 'Produk') }}",
            price: {{ $product['price'] ?? 0 }},
            price_min: {{ $priceMin }},
            price_max: {{ $priceMax }},
            stock: {{ $stock }},
            colors: {!! json_encode($chatbotColors) !!},
            sizes: {!! json_encode($chatbotSizes) !!},
            custom_allowed: {{ $customAllowed ? 'true' : 'false' }},
            description: `{{ addslashes($description) }}`,
            category: "{{ addslashes($category) }}",
            formatted_price: "{{ number_format($product['price'] ?? 0, 0, ',', '.') }}"
        };

        // Handle chat button click - open unified chatbot with product + selected variant
        document.addEventListener('DOMContentLoaded', function() {
            const chatBtn = document.getElementById('productChatBtn');
            if (chatBtn) {
                chatBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get selected color
                    const selectedColorEl = document.querySelector('.color-btn.selected');
                    let selectedColor = null;
                    if (selectedColorEl) {
                        selectedColor = {
                            value: selectedColorEl.dataset.color || selectedColorEl.style.backgroundColor,
                            label: selectedColorEl.getAttribute('title') || selectedColorEl.dataset.colorName || 'Warna dipilih'
                        };
                    }
                    
                    // Get selected size
                    const selectedSizeEl = document.querySelector('.size-btn.selected');
                    let selectedSize = null;
                    if (selectedSizeEl) {
                        selectedSize = {
                            value: selectedSizeEl.dataset.size,
                            label: selectedSizeEl.textContent.trim()
                        };
                    }
                    
                    // Get quantity
                    const quantityInput = document.getElementById('quantityValue');
                    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
                    
                    // Build product data with selected variants
                    const chatProductData = {
                        ...productData,
                        selected_color: selectedColor,
                        selected_size: selectedSize,
                        quantity: quantity,
                        formatted_price: formatPrice(productData.price)
                    };
                    
                    console.log('Opening chat with product data:', chatProductData);
                    
                    if (typeof window.openUnifiedChatbotWithProduct === 'function') {
                        window.openUnifiedChatbotWithProduct(chatProductData);
                    } else {
                        console.error('openUnifiedChatbotWithProduct not found');
                        // Fallback - just open the chatbot popup
                        const popup = document.getElementById('unifiedChatbotPopup');
                        const trigger = document.getElementById('unifiedChatbotTrigger');
                        if (popup) popup.classList.add('active');
                        if (trigger) trigger.classList.add('hidden');
                    }
                });
            }
            
            // Helper function to format price
            function formatPrice(price) {
                return new Intl.NumberFormat('id-ID').format(price);
            }
        });
    </script>

    
</body>
</html>