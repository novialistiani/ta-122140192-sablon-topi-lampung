@props(['product', 'showRibbon' => true])

<div class="product-card"
     data-product-id="{{ $product->id }}"
     data-product-slug="{{ $product->slug }}"
     data-product-name="{{ $product->name }}"
     data-product-price="{{ $product->formatted_price }}"
     data-product-image="{{ image_url($product->image) }}"
     data-variant-images="{{ json_encode($product->variant_images ?? []) }}">
    
    <!-- Product Image Container -->
    <div class="product-image-container" 
         data-product-id="{{ $product->id }}">
        
        @php
            // Priority: variant_images[0] > product->image > placeholder
            $displayImage = null;
            if (!empty($product->variant_images) && is_array($product->variant_images) && count($product->variant_images) > 0) {
                $displayImage = $product->variant_images[0];
            } elseif ($product->image) {
                $displayImage = $product->image;
            }
        @endphp
        
        @if($displayImage)
            <img class="product-image" 
                 src="{{ $displayImage }}" 
                 alt="{{ $product->name }}"
                 loading="lazy"
                 onload="console.log('✅ Image loaded:', this.src);"
                 onerror="console.error('❌ Image failed:', this.src); if(!this.dataset.errorHandled) { this.dataset.errorHandled='1'; this.src='https://via.placeholder.com/400x400/e0e0e0/666666?text=No+Image'; }">
        @else
            <div class="no-image-placeholder">
                <i class="fas fa-image"></i>
            </div>
        @endif
        
        <!-- CUSTOM Ribbon (Hidden by default) -->
        @if($showRibbon && !empty($product->custom_design_allowed) && $product->custom_design_allowed)
            <div class="product-ribbon" aria-hidden="true">
                CUSTOM
            </div>
        @endif
        
    </div>
    
    <!-- Product Info -->
    <div class="product-info">
        <h3 class="product-title">
            {{ $product->name }}
        </h3>
        <p class="product-price">
            Rp {{ $product->formatted_price }}
        </p>
        
        <!-- Action Buttons - Only functional for customers, not admin -->
        @php
            $isAdmin = auth()->guard('admin')->check();
            $isCustomer = auth('web')->check() && !$isAdmin;
        @endphp
        
        <div class="product-actions" role="group" aria-label="Aksi produk">
            @if($isCustomer)
                {{-- Customer: buttons are clickable --}}
                @php
                    $productImage = null;
                    if (!empty($product->variant_images) && is_array($product->variant_images) && count($product->variant_images) > 0) {
                        $productImage = $product->variant_images[0];
                    } elseif ($product->image) {
                        $productImage = $product->image;
                    }
                @endphp
                <button class="action-btn action-chat" 
                        type="button" 
                        aria-label="Chat tentang produk" 
                        onclick="event.stopPropagation(); openUnifiedChatbotWithProduct({
                            id: {{ $product->id }},
                            name: '{{ addslashes($product->name) }}',
                            slug: '{{ $product->slug }}',
                            price: {{ $product->price ?? 0 }},
                            formatted_price: '{{ $product->formatted_price }}',
                            image: '{{ $productImage }}',
                            custom_allowed: {{ $product->custom_design_allowed ? 'true' : 'false' }},
                            category: '{{ addslashes($product->category->name ?? '') }}'
                        })">
                    <i class="fas fa-comments" aria-hidden="true"></i>
                </button>
                <button class="action-btn action-cart" type="button" aria-label="Tambahkan ke keranjang" data-product-id="{{ $product->id }}">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                </button>
            @elseif($isAdmin)
                {{-- Admin: buttons are disabled --}}
                <button class="action-btn action-chat disabled" 
                        type="button" 
                        aria-label="Chat tidak tersedia untuk admin"
                        disabled
                        title="Admin tidak dapat menggunakan fitur chat"
                        onclick="event.stopPropagation();">
                    <i class="fas fa-comments" aria-hidden="true"></i>
                </button>
                <button class="action-btn action-cart disabled" 
                        type="button" 
                        aria-label="Keranjang tidak tersedia untuk admin"
                        disabled
                        title="Admin tidak dapat menambah ke keranjang"
                        onclick="event.stopPropagation();">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                </button>
            @else
                {{-- Guest: show buttons but redirect to login --}}
                <a href="{{ route('login') }}" class="action-btn action-chat" 
                   onclick="event.stopPropagation();"
                   aria-label="Login untuk chat">
                    <i class="fas fa-comments" aria-hidden="true"></i>
                </a>
                <a href="{{ route('login') }}" class="action-btn action-cart"
                   onclick="event.stopPropagation();"
                   aria-label="Login untuk tambah keranjang">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>
</div>
