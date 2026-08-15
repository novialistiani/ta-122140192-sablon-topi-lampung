document.addEventListener('DOMContentLoaded', () => {
    // ===== PROFILE POPUP =====
    const profileIcon = document.getElementById('profile-icon');
    const profilePopup = document.getElementById('profile-popup');

    function toggleProfilePopup() {
        if (!profilePopup) return;
        const isAuthenticated = profilePopup.dataset.auth === 'true';
        if (!isAuthenticated) {
            const loginUrl = profilePopup.dataset.loginUrl;
            if (loginUrl) {
                window.location.href = loginUrl;
            }
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
        profileIcon.addEventListener('click', event => {
            event.stopPropagation();
            toggleProfilePopup();
        });
    }

    document.addEventListener('click', event => {
        if (!profilePopup || profilePopup.style.display !== 'block') return;
        if (!profilePopup.contains(event.target) && event.target !== profileIcon) {
            closeProfilePopup();
        }
    });

    if (profilePopup) {
        profilePopup.addEventListener('click', event => {
            event.stopPropagation();
        });
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && profilePopup && profilePopup.style.display === 'block') {
            closeProfilePopup();
        }
    });

    // ===== PRODUCT VARIANTS SYSTEM =====
    // Load variants data from JSON script tag
    const variantsDataElement = document.getElementById('variantsData');
    const variants = variantsDataElement ? JSON.parse(variantsDataElement.textContent) : [];
    
    console.log('=== PRODUCT VARIANTS LOADED ===');
    console.log('Total variants:', variants.length);
    console.log('Variants data:', variants);
    
    // State management
    let selectedColor = null;
    let selectedSize = null;
    let currentVariant = null;

    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');
    const colorOptions = document.querySelectorAll('.color-swatch');
    const sizeOptions = document.querySelectorAll('.size-option');
    const cartColorInput = document.getElementById('cartColorInput');
    const cartSizeInput = document.getElementById('cartSizeInput');
    const cartVariantIdInput = document.getElementById('cartVariantIdInput');
    const productPrice = document.getElementById('productPrice');
    const productStock = document.getElementById('productStock');

    // Function to find matching variant
    function findVariant(color, size) {
        return variants.find(v => v.color === color && v.size === size);
    }

    // Function to update product info based on selected variant  
    function updateProductInfo() {
        console.log('updateProductInfo called');
        console.log('selectedColor:', selectedColor);
        console.log('selectedSize:', selectedSize);
        
        if (!selectedColor || !selectedSize) {
            if (productStock) {
                productStock.textContent = 'Pilih warna dan ukuran untuk melihat stok';
                productStock.style.color = '#666';
            }
            console.log('Waiting for both color and size selection');
            return;
        }

        currentVariant = findVariant(selectedColor, selectedSize);
        console.log('currentVariant:', currentVariant);
        
        if (currentVariant) {
            // Update price
            if (productPrice) {
                const formattedPrice = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentVariant.price);
                
                // Update or create current price element
                let currentPriceElement = productPrice.querySelector('.current-price');
                if (!currentPriceElement) {
                    currentPriceElement = document.createElement('span');
                    currentPriceElement.className = 'current-price';
                    productPrice.appendChild(currentPriceElement);
                }
                currentPriceElement.textContent = formattedPrice;
                
                // Handle original price (harga coret)
                let originalPriceElement = productPrice.querySelector('.original-price');
                // Convert to numbers for proper comparison
                const variantPrice = parseFloat(currentVariant.price);
                const variantOriginalPrice = parseFloat(currentVariant.original_price);
                
                console.log('Price comparison:');
                console.log('  variant.price (raw):', currentVariant.price);
                console.log('  variant.original_price (raw):', currentVariant.original_price);
                console.log('  variantPrice (parsed):', variantPrice);
                console.log('  variantOriginalPrice (parsed):', variantOriginalPrice);
                console.log('  Should show strikethrough?', variantOriginalPrice > variantPrice);
                
                if (variantOriginalPrice && !isNaN(variantOriginalPrice) && variantOriginalPrice > variantPrice) {
                    const formattedOriginalPrice = 'Rp ' + new Intl.NumberFormat('id-ID').format(variantOriginalPrice);
                    
                    if (!originalPriceElement) {
                        originalPriceElement = document.createElement('span');
                        originalPriceElement.className = 'original-price';
                        originalPriceElement.id = 'originalPrice';
                        // Insert before current price to show on the left
                        productPrice.insertBefore(originalPriceElement, currentPriceElement);
                    }
                    originalPriceElement.textContent = formattedOriginalPrice;
                } else {
                    // Remove original price if not applicable
                    if (originalPriceElement) {
                        originalPriceElement.remove();
                    }
                }
            }
            
            // Update stock
            if (productStock) {
                if (currentVariant.stock > 0) {
                    productStock.innerHTML = `<span class="text-green-600 font-medium"><i class="fas fa-check-circle"></i> Stok tersedia: ${currentVariant.stock} item</span>`;
                } else {
                    productStock.innerHTML = `<span class="text-red-600 font-bold"><i class="fas fa-times-circle"></i> Stok habis</span>`;
                }
            }
            
            // Enable/Disable purchase buttons based on stock
            const buyNowBtn = document.querySelector('.buy-now-btn');
            const addToCartBtn = document.querySelector('.add-to-cart-btn');
            const quantityBtns = document.querySelectorAll('.quantity-btn');
            
            if (currentVariant.stock <= 0) {
                // Disable buttons when out of stock
                if (buyNowBtn) {
                    buyNowBtn.disabled = true;
                    buyNowBtn.textContent = 'Stok Habis';
                    buyNowBtn.style.opacity = '0.5';
                    buyNowBtn.style.cursor = 'not-allowed';
                }
                if (addToCartBtn) {
                    addToCartBtn.disabled = true;
                    addToCartBtn.style.opacity = '0.5';
                    addToCartBtn.style.cursor = 'not-allowed';
                }
                quantityBtns.forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                });
            } else {
                // Enable buttons when stock available
                if (buyNowBtn) {
                    buyNowBtn.disabled = false;
                    buyNowBtn.textContent = 'Beli Sekarang';
                    buyNowBtn.style.opacity = '1';
                    buyNowBtn.style.cursor = 'pointer';
                }
                if (addToCartBtn) {
                    addToCartBtn.disabled = false;
                    addToCartBtn.style.opacity = '1';
                    addToCartBtn.style.cursor = 'pointer';
                }
                quantityBtns.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });
            }
            
            // Update hidden form inputs
            if (cartVariantIdInput) cartVariantIdInput.value = currentVariant.id;
            if (cartColorInput) cartColorInput.value = selectedColor;
            if (cartSizeInput) cartSizeInput.value = selectedSize;
            
            // Update main image if variant has image
            if (currentVariant.image && mainImage) {
                mainImage.src = currentVariant.image;
                
                // Update thumbnail active state
                thumbnails.forEach(thumb => {
                    const thumbColor = thumb.dataset.color;
                    const thumbSize = thumb.dataset.size;
                    if (thumbColor === selectedColor && thumbSize === selectedSize) {
                        thumbnails.forEach(t => {
                            t.classList.remove('active');
                            t.setAttribute('aria-selected', 'false');
                        });
                        thumb.classList.add('active');
                        thumb.setAttribute('aria-selected', 'true');
                    }
                });
            }
        } else {
            if (productStock) {
                productStock.textContent = 'Kombinasi warna dan ukuran tidak tersedia';
                productStock.style.color = '#ef4444';
            }
        }
    }

    // Function to select variant options by color and size (for thumbnail sync)
    function selectVariantByColorAndSize(color, size) {
        // Select color
        if (color) {
            const colorOption = document.querySelector(`.color-swatch[data-color="${color}"]`);
            if (colorOption) {
                colorOptions.forEach(item => {
                    item.classList.remove('active');
                    item.setAttribute('aria-pressed', 'false');
                });
                colorOption.classList.add('active');
                colorOption.setAttribute('aria-pressed', 'true');
                selectedColor = color;
            }
        }
        
        // Select size
        if (size) {
            const sizeOption = document.querySelector(`.size-option[data-size="${size}"]`);
            if (sizeOption) {
                sizeOptions.forEach(item => {
                    item.classList.remove('active');
                    item.setAttribute('aria-pressed', 'false');
                });
                sizeOption.classList.add('active');
                sizeOption.setAttribute('aria-pressed', 'true');
                selectedSize = size;
            }
        }
        
        // Update product info
        updateProductInfo();
    }

    // Thumbnail click handler with variant sync
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', () => {
            const newImageSrc = thumbnail.dataset.image;
            const thumbColor = thumbnail.dataset.color;
            const thumbSize = thumbnail.dataset.size;
            
            if (!newImageSrc || !mainImage) return;
            
            // Update thumbnail active state
            thumbnails.forEach(item => {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });
            thumbnail.classList.add('active');
            thumbnail.setAttribute('aria-selected', 'true');
            
            // Update main image
            mainImage.src = newImageSrc;
            
            // Auto-select variant based on thumbnail data
            if (thumbColor && thumbSize) {
                selectVariantByColorAndSize(thumbColor, thumbSize);
            }
        });
    });

    // Color selection handler
    colorOptions.forEach(option => {
        option.addEventListener('click', () => {
            colorOptions.forEach(item => {
                item.classList.remove('active');
                item.setAttribute('aria-pressed', 'false');
            });
            option.classList.add('active');
            option.setAttribute('aria-pressed', 'true');
            selectedColor = option.dataset.color || '';
            updateProductInfo();
        });
    });

    // Size selection handler
    sizeOptions.forEach(option => {
        option.addEventListener('click', () => {
            sizeOptions.forEach(item => {
                item.classList.remove('active');
                item.setAttribute('aria-pressed', 'false');
            });
            option.classList.add('active');
            option.setAttribute('aria-pressed', 'true');
            selectedSize = option.dataset.size || option.textContent.trim();
            updateProductInfo();
        });
    });

    const quantitySelector = document.querySelector('.quantity-selector');
    const quantityValue = document.getElementById('quantityValue');
    const quantityWarning = document.getElementById('quantityWarning');
    const cartQuantityInput = document.getElementById('cartQuantityInput');
    let quantity = 1;

    function updateQuantity(newQuantity, showWarning = false) {
        // Get max quantity based on variant stock
        let maxQuantity = 99;
        if (currentVariant && currentVariant.stock > 0) {
            maxQuantity = Math.min(currentVariant.stock, 99);
        }
        
        // Check if exceeds stock
        if (newQuantity > maxQuantity && showWarning) {
            if (quantityWarning) {
                quantityWarning.classList.add('show');
                setTimeout(() => {
                    quantityWarning.classList.remove('show');
                }, 3000);
            }
        } else if (quantityWarning) {
            quantityWarning.classList.remove('show');
        }
        
        quantity = Math.max(1, Math.min(newQuantity, maxQuantity));
        if (quantityValue) {
            quantityValue.value = quantity.toString();
        }
        if (cartQuantityInput) {
            cartQuantityInput.value = quantity.toString();
        }
    }

    if (quantitySelector && quantityValue) {
        // Handle button clicks
        quantitySelector.addEventListener('click', event => {
            const action = event.target.dataset.quantityAction;
            if (action === 'increase') {
                // Check if we can increase
                let maxQuantity = 99;
                if (currentVariant && currentVariant.stock > 0) {
                    maxQuantity = Math.min(currentVariant.stock, 99);
                }
                
                if (quantity >= maxQuantity) {
                    if (quantityWarning) {
                        quantityWarning.classList.add('show');
                        setTimeout(() => {
                            quantityWarning.classList.remove('show');
                        }, 3000);
                    }
                    return;
                }
                updateQuantity(quantity + 1);
            } else if (action === 'decrease') {
                updateQuantity(quantity - 1);
            }
        });
        
        // Handle direct input
        quantityValue.addEventListener('input', event => {
            const inputValue = parseInt(event.target.value) || 0;
            if (inputValue > 0) {
                updateQuantity(inputValue, true);
            }
        });
        
        // Handle blur to ensure valid value
        quantityValue.addEventListener('blur', event => {
            const inputValue = parseInt(event.target.value) || 1;
            updateQuantity(inputValue > 0 ? inputValue : 1);
        });
    }
    if (cartQuantityInput) {
        cartQuantityInput.value = quantity.toString();
    }

    const addToCartButton = document.querySelector('.add-to-cart-btn');
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartButton && addToCartForm) {
        addToCartButton.addEventListener('click', () => {
            const activeColor = document.querySelector('.color-swatch.active');
            const activeSize = document.querySelector('.size-option.active');
            if (cartColorInput) {
                cartColorInput.value = activeColor ? activeColor.dataset.color || '' : '';
            }
            if (cartSizeInput) {
                cartSizeInput.value = activeSize ? activeSize.dataset.size || activeSize.textContent.trim() : '';
            }
            if (cartQuantityInput) {
                cartQuantityInput.value = quantity.toString();
            }
            
            // Check if variant is selected
            if (!selectedColor || !selectedSize) {
                alert('Silakan pilih warna dan ukuran terlebih dahulu');
                return;
            }
            
            if (!currentVariant) {
                alert('Kombinasi warna dan ukuran tidak tersedia');
                return;
            }
            
            // Check stock availability
            if (currentVariant.stock <= 0) {
                alert('Maaf, stok produk habis. Tidak dapat menambahkan ke keranjang.');
                return;
            }
            
            // Check if quantity exceeds stock
            if (quantity > currentVariant.stock) {
                alert(`Maaf, stok tersedia hanya ${currentVariant.stock} item.`);
                return;
            }
            
            addToCartForm.submit();
        });
    }
    
    // Chat button handler - Skip if inline handler already set (productChatBtn has inline handler in blade)
    // This is a fallback for pages without inline handler
    const chatBtn = document.querySelector('.chat-btn:not(#productChatBtn)');
    if (chatBtn) {
        chatBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if unified chatbot popup function exists
            if (typeof window.openUnifiedChatbotWithProduct === 'function') {
                // Get product data from page
                const productData = {
                    id: document.querySelector('[data-product-id]')?.dataset.productId || null,
                    name: document.querySelector('h1')?.textContent?.trim() || 'Produk',
                    price: document.querySelector('.product-price')?.textContent?.trim() || '-',
                    image: document.querySelector('.main-product-image img')?.src || null,
                    // Get selected variant info
                    selectedColor: document.querySelector('.color-option.selected')?.dataset.color || null,
                    selectedSize: document.querySelector('.size-option.selected')?.dataset.size || null,
                    // Get available variants
                    availableColors: Array.from(document.querySelectorAll('.color-option')).map(el => el.dataset.color).filter(Boolean),
                    availableSizes: Array.from(document.querySelectorAll('.size-option')).map(el => el.dataset.size).filter(Boolean)
                };
                
                window.openUnifiedChatbotWithProduct(productData);
            } else {
                // Fallback: just open chatbot popup without product context
                if (typeof window.openUnifiedChatbot === 'function') {
                    window.openUnifiedChatbot();
                } else {
                    // Last resort: redirect
                    window.location.href = '/chatbot';
                }
            }
        });
    }

    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const navigateUrl = button.dataset.navigate;
            if (navigateUrl) {
                window.location.href = navigateUrl;
                return;
            }

            const target = button.dataset.tab;
            if (!target) return;
            tabButtons.forEach(item => {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
                panel.hidden = true;
            });
            button.classList.add('active');
            button.setAttribute('aria-selected', 'true');
            const activePanel = document.querySelector(`[data-tab-panel="${target}"]`);
            if (activePanel) {
                activePanel.classList.add('active');
                activePanel.hidden = false;
            }
        });
    });

    tabPanels.forEach((panel, index) => {
        if (!panel.classList.contains('active')) {
            panel.hidden = true;
        } else if (index === 0) {
            panel.hidden = false;
        }
    });

    const recommendationCards = document.querySelectorAll('.recommendation-card');
    recommendationCards.forEach(card => {
        card.addEventListener('click', () => {
            const productId = card.dataset.productId;
            const productName = card.dataset.productName;
            const productPrice = card.dataset.productPrice;
            const productImage = card.dataset.productImage;
            if (!productId || !productName || !productPrice || !productImage) return;
            const query = new URLSearchParams({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
            });
            window.location.href = `/product-detail?${query.toString()}`;
        });

        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.click();
            }
        });
    });

    // ===== CUSTOM DESIGN BUTTON =====
    const customDesignBtn = document.querySelector('.custom-design-btn');
    if (customDesignBtn) {
        customDesignBtn.addEventListener('click', () => {
            const productId = customDesignBtn.dataset.productId;
            const productData = document.querySelector('.product-page');
            
            if (!productId) {
                alert('ID Produk tidak ditemukan');
                return;
            }

            // Check if variant is selected
            if (!currentVariant) {
                alert('Silakan pilih varian (warna dan ukuran) terlebih dahulu');
                return;
            }

            // Redirect to custom design page with product ID and variant ID
            window.location.href = `/custom-design?id=${productId}&variant_id=${currentVariant.id}`;
        });
    }

    // ===== BUY NOW BUTTON =====
    const buyNowBtn = document.querySelector('.buy-now-btn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', async () => {
            // Check if user is logged in
            const profilePopup = document.getElementById('profile-popup');
            const isAuthenticated = profilePopup && profilePopup.dataset.auth === 'true';
            
            if (!isAuthenticated) {
                alert('Anda harus login terlebih dahulu');
                window.location.href = '/login';
                return;
            }

            // Check if variant is selected
            if (!selectedColor || !selectedSize) {
                alert('Silakan pilih warna dan ukuran terlebih dahulu');
                return;
            }

            if (!currentVariant) {
                alert('Kombinasi warna dan ukuran tidak tersedia');
                return;
            }

            // Check stock
            if (currentVariant.stock <= 0) {
                alert('Maaf, stok produk ini habis');
                return;
            }
            
            // Check if quantity exceeds stock
            if (quantity > currentVariant.stock) {
                alert(`Maaf, stok tersedia hanya ${currentVariant.stock} item.`);
                return;
            }

            // Disable button and show loading state
            buyNowBtn.disabled = true;
            const originalText = buyNowBtn.textContent;
            buyNowBtn.textContent = 'Memproses...';

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token tidak ditemukan');
                }

                // Get product ID from form
                const productId = document.querySelector('input[name="product_id"]')?.value;
                if (!productId) {
                    throw new Error('Product ID tidak ditemukan');
                }

                // Prepare data
                const orderData = {
                    product_id: productId,
                    variant_id: currentVariant.id,
                    quantity: quantity,
                    color: selectedColor,
                    size: selectedSize,
                };

                console.log('Sending buy now request:', orderData);

                // Send request to server
                const response = await fetch('/buy-now', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(orderData),
                });

                const data = await response.json();
                console.log('Buy now response:', data);

                if (data.success) {
                    // Show success message
                    alert(data.message || 'Pesanan berhasil dibuat!');
                    
                    // Redirect to order list
                    window.location.href = data.redirect_url || '/order-list';
                } else {
                    throw new Error(data.message || 'Gagal membuat pesanan');
                }

            } catch (error) {
                console.error('Buy now error:', error);
                alert(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
                
                // Re-enable button
                buyNowBtn.disabled = false;
                buyNowBtn.textContent = originalText;
            }
        });
    }
});
