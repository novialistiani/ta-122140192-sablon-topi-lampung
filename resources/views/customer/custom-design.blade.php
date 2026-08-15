<x-customer-layout title="Desain Kustom" active="custom-design">
    @vite(['resources/css/customer/shared.css', 'resources/css/guest/custom-design.css', 'resources/css/app.css'])
    @php
        // Prefer server-provided product (from controller) for safety; fallback to request params
        $selectedName = isset($product) ? $product->name : (request('name') ?: 'One Life Graphic T-shirt');
        
        // Image priority: variant->image > product->image > placeholder
        $selectedImage = null;
        
        // First priority: Check for variant image
        if (isset($variant) && !empty($variant->image)) {
            $imagePath = $variant->image;
            // Check if it already starts with http (full URL)
            if (str_starts_with($imagePath, 'http')) {
                $selectedImage = $imagePath;
            } else {
                // Build asset URL
                $selectedImage = asset('storage/' . $imagePath);
            }
        }
        
        // Second priority: Check for product image if variant image not available
        if (!$selectedImage && isset($product) && !empty($product->image)) {
            $imagePath = $product->image;
            // Check if it already starts with http (full URL)
            if (str_starts_with($imagePath, 'http')) {
                $selectedImage = $imagePath;
            } else {
                // Build asset URL
                $selectedImage = asset('storage/' . $imagePath);
            }
        }
        
        // Set default placeholder if no image
        if (!$selectedImage) {
            $selectedImage = 'https://via.placeholder.com/400x400/0a1f44/ffffff?text=' . urlencode($selectedName);
        }
        
        // Use variant price if available, otherwise use product price
        $displayPrice = isset($variant) && !empty($variant->price) ? $variant->price : (isset($product) ? $product->price : 0);
        $estimatedPrice = number_format((float) $displayPrice, 0, ',', '.');
        
        // Add variant info to product name if variant exists
        if (isset($variant)) {
            $variantInfo = [];
            if (!empty($variant->color)) {
                $variantInfo[] = $variant->color;
            }
            if (!empty($variant->size)) {
                $variantInfo[] = 'Size: ' . $variant->size;
            }
            if (!empty($variantInfo)) {
                $selectedName .= ' (' . implode(', ', $variantInfo) . ')';
            }
        }
            
        // Debug: Log image info
        if (isset($product)) {
            \Log::info('Custom Design Image Debug:', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->image,
                'variant_id' => isset($variant) ? $variant->id : null,
                'variant_image' => isset($variant) ? $variant->image : null,
                'variant_color' => isset($variant) ? $variant->color : null,
                'variant_size' => isset($variant) ? $variant->size : null,
                'selected_image' => $selectedImage
            ]);
        }
    @endphp

    <main class="flex-1 overflow-y-auto min-h-0">
                <div class="main-content">
                    <div class="breadcrumb">
                        <a href="{{ url()->previous() }}">
                            <i class="fas fa-chevron-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Area Cetak Section -->
                    <div class="content-card">
                        <div class="area-cetak-section">
                            <h3 class="area-cetak-title">AREA CETAK</h3>
                            <div class="area-image-container">
                                <img src="https://s.alicdn.com/@sc04/kf/H18f96e8d35554c9e96dcd6ebff3676096/223366470/H18f96e8d35554c9e96dcd6ebff3676096.png" alt="Area Cetak" class="area-image">
                            </div>
                        </div>
                    </div>

                    <!-- Main Form Section -->
                    <div class="content-card">
                        <!-- Two Column Layout -->
                        <div class="custom-design-grid">
                            <!-- Left Column: Form -->
                            <div class="form-column">
                                <p class="section-label">Item yang dipilih:</p>
                                <h2 class="product-title">{{ $selectedName }}</h2>
                                
                                <!-- Quantity Selector -->
                                <div class="quantity-selector">
                                    <label class="quantity-label">Jumlah:</label>
                                    <div class="quantity-controls">
                                        <button type="button" class="qty-btn" id="decreaseQty" onclick="updateQuantity(-1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="99" class="qty-input" onchange="updateQuantityFromInput()" readonly>
                                        <button type="button" class="qty-btn" id="increaseQty" onclick="updateQuantity(1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Upload Bagian Dropdown -->
                                <div class="dropdown-section">
                                    <div class="dropdown-header" onclick="toggleDropdown('uploadBagian')">
                                        <span>Upload bagian</span>
                                        <i class="fas fa-chevron-down dropdown-icon"></i>
                                    </div>
                                    <div class="dropdown-content" id="uploadBagian">
                                        <!-- Will be populated dynamically -->
                                        <div class="text-center py-4 text-gray-500">
                                            <i class="fas fa-spinner fa-spin"></i> Memuat...
                                        </div>
                                    </div>
                                </div>
                                <input type="file" id="uploadBagianFileInput" accept="image/*" hidden>
                                <div id="uploaded-bagian-list"></div>

                                <!-- Jenis Cutting Dropdown -->
                                <div class="dropdown-section">
                                    <div class="dropdown-header" onclick="toggleDropdown('cutting')">
                                        <span>Jenis Cutting</span>
                                        <i class="fas fa-chevron-down dropdown-icon"></i>
                                    </div>
                                    <div class="dropdown-content" id="cutting">
                                        <!-- Will be populated dynamically -->
                                        <div class="text-center py-4 text-gray-500">
                                            <i class="fas fa-spinner fa-spin"></i> Memuat...
                                        </div>
                                    </div>
                                </div>
                                <div class="selected-items" id="selected-cutting"></div>

                                <!-- Description Box -->
                                <div class="description-box">
                                    <label class="description-label">Deskripsi tambahan:</label>
                                    <textarea class="description-text" name="additional_description" placeholder="Tampak depan: diberi logo seperti gambar 1, turun di tengah Tampak belakang: polosian sesuai kaju katalog Lengan kiri: tambahkan logo gambar link tahun" rows="5"></textarea>
                                </div>

                                <!-- Submit Button -->
                                <form id="customDesignForm" enctype="multipart/form-data">
                                    <input type="hidden" name="product_id" value="{{ isset($product) ? $product->id : request('id') }}">
                                    <input type="hidden" name="variant_id" value="{{ isset($variant) ? $variant->id : '' }}">
                                    <input type="hidden" id="cutting_type_input" name="cutting_type">
                                    <input type="hidden" id="special_materials_input" name="special_materials">
                                    <input type="hidden" id="additional_description_input" name="additional_description">
                                    <button type="submit" class="buy-button">Beli sekarang!</button>
                                </form>
                            </div>

                            <!-- Right Column: Product Image & Price -->
                            <div class="image-column">
                                <!-- Product Image -->
                                <div class="product-preview-wrapper">
                                    <img 
                                        id="productImage"
                                        src="{{ $selectedImage }}" 
                                        alt="{{ $selectedName }}" 
                                        class="product-image-large" 
                                        onerror="handleImageError(this, '{{ addslashes($selectedName) }}');"
                                        onload="console.log('Image loaded successfully:', this.src);"
                                        title="Klik untuk memperbesar">
                                </div>

                                <!-- Price Breakdown -->
                                <div class="price-breakdown-section">
                                    <h4 class="breakdown-title">Rincian Harga:</h4>
                                    <div class="breakdown-list">
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Harga Produk (per pcs)</span>
                                            <span class="breakdown-value" id="base-price-display">Rp {{ number_format($displayPrice, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Jumlah Produk</span>
                                            <span class="breakdown-value" id="quantity-display">1 pcs</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Total Bagian Design</span>
                                            <span class="breakdown-value" id="upload-count-display">0 bagian</span>
                                        </div>
                                        <div id="upload-sections-breakdown"></div>
                                        <div id="cutting-type-breakdown"></div>
                                    </div>
                                </div>

                                <!-- Total Price -->
                                <div class="total-price-section">
                                    <span class="total-label">Harga Total:</span>
                                    <span class="total-amount" id="main-total">Rp {{ $estimatedPrice }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="fixed inset-0 hidden items-center justify-center z-50" onclick="closeImagePreview()">
        <div class="absolute inset-0 bg-black/80"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full mx-4 p-6" onclick="event.stopPropagation()">
            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl font-bold" onclick="closeImagePreview()">
                <i class="fas fa-times"></i>
            </button>
            <div class="text-center">
                <h3 id="imagePreviewTitle" class="text-xl font-bold text-navy-900 mb-4">Preview Produk</h3>
                <div class="max-h-[70vh] overflow-auto">
                    <img id="imagePreviewImg" src="" alt="Preview" class="max-w-full h-auto mx-auto rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </div>

    <!-- Success Notification Modal -->
    <div id="successModal" class="fixed inset-0 hidden items-center justify-center z-50">
        <div class="absolute inset-0 bg-black/40" onclick="closeSuccessModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full mx-4 p-10 text-center">
            <div class="mx-auto mb-6 w-20 h-20 rounded-full border-4 border-navy-900 text-navy-900 flex items-center justify-center text-4xl font-bold">✓</div>
            <h2 class="text-2xl font-extrabold text-navy-900 mb-2">Pesanan sudah masuk!</h2>
            <p class="text-gray-600">Silahkan cek notifikasi untuk pemberitahuan lebih lanjut</p>
            <button class="mt-10 bg-yellow-400 hover:bg-yellow-500 text-navy-900 font-bold px-8 py-3 rounded-full" onclick="goToHome()">Kembali ke beranda</button>
        </div>
    </div>

    <script>
        // Handle image loading error with proper fallback
        let imageRetryCount = 0;
        const MAX_RETRY = 1;
        
        function handleImageError(img, productName) {
            if (img.src.includes('via.placeholder.com')) {
                // Already showing placeholder, don't retry
                console.log('Placeholder is already displayed');
                return;
            }
            
            console.error('Image failed to load:', img.src);
            
            // Retry once by adding cache buster
            if (imageRetryCount < MAX_RETRY) {
                imageRetryCount++;
                const separator = img.src.includes('?') ? '&' : '?';
                img.src = img.src.split('?')[0] + separator + '_retry=' + Date.now();
                console.log('Retrying image load (attempt ' + imageRetryCount + ')...');
                return;
            }
            
            // After max retries, show placeholder
            console.log('Max retries reached, showing placeholder');
            const encodedName = encodeURIComponent(productName);
            img.src = `https://via.placeholder.com/400x400/0a1f44/ffffff?text=${encodedName}`;
            
            // Prevent infinite loop
            img.onerror = null;
        }
        
        // Store selected options and prices
        const selectedOptions = {
            cutting: [],
            material: []
        };
        
        let designPrices = {
            upload_sections: {},
            cutting_types: {}
        };
        
        let activeUploadSections = [];
        let activeCuttingTypes = [];
        
        let baseProductPrice = {{ $displayPrice }};
        
        // Load prices and render dynamic options from API (product-specific)
        async function loadPrices() {
            console.log('🔄 Starting loadPrices...');
            
            try {
                // Get product ID from URL or form
                const productId = {{ isset($product) ? $product->id : 'new URLSearchParams(window.location.search).get("id")' }};
                
                console.log('🆔 Product ID:', productId);
                
                if (!productId) {
                    throw new Error('Product ID not found');
                }
                
                // Fetch product-specific custom design prices
                console.log('📡 Fetching prices from API...');
                const response = await fetch(`/api/product-custom-design-prices/${productId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('📥 Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('📦 Data received:', data);
                
                if (data.success) {
                    // Store active upload sections (only enabled for this product)
                    activeUploadSections = data.data.upload_sections || [];
                    (data.data.upload_sections || []).forEach(item => {
                        // Use custom_price from pivot table (product-specific price)
                        const price = item.pivot?.custom_price || item.price;
                        designPrices.upload_sections[item.code] = parseFloat(price);
                    });
                    
                    // Store active cutting types (only enabled for this product)
                    activeCuttingTypes = data.data.cutting_types || [];
                    (data.data.cutting_types || []).forEach(item => {
                        // Use custom_price from pivot table (product-specific price)
                        const price = item.pivot?.custom_price || item.price;
                        designPrices.cutting_types[item.code] = parseFloat(price);
                    });
                    
                    console.log('✅ Product-specific prices loaded:', designPrices);
                    console.log('📦 Available upload sections:', activeUploadSections.length);
                    console.log('✂️ Available cutting types:', activeCuttingTypes.length);
                    
                    // Render dynamic dropdowns (only admin-enabled options)
                    renderUploadSections();
                    renderCuttingTypes();
                    
                    calculateTotal();
                    
                    console.log('✅ loadPrices completed successfully');
                } else {
                    throw new Error(data.message || 'Failed to load prices');
                }
            } catch (error) {
                console.error('❌ Error loading prices:', error);
                
                // Show error message and replace loading spinners
                const uploadContainer = document.getElementById('uploadBagian');
                if (uploadContainer) {
                    uploadContainer.innerHTML = '<div class="text-center py-4 text-red-500"><i class="fas fa-exclamation-circle"></i><br>Gagal memuat data custom design</div>';
                }
                
                const cuttingContainer = document.getElementById('cutting');
                if (cuttingContainer) {
                    cuttingContainer.innerHTML = '<div class="text-center py-4 text-red-500"><i class="fas fa-exclamation-circle"></i><br>Gagal memuat data</div>';
                }
                
                // Still allow page to function with base price
                calculateTotal();
            }
        }
        
        // Render upload sections dropdown dynamically
        function renderUploadSections() {
            const container = document.getElementById('uploadBagian');
            if (!container) return;
            
            if (activeUploadSections.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-gray-500">Tidak ada bagian upload yang tersedia</div>';
                return;
            }
            
            container.innerHTML = '';
            activeUploadSections.forEach(section => {
                const div = document.createElement('div');
                div.className = 'dropdown-item option-item';
                div.dataset.optionGroup = 'uploadBagian';
                div.dataset.optionValue = section.name;
                div.innerHTML = `
                    <span>${section.name}</span>
                    <button type="button" class="add-btn" aria-label="Tambah ${section.name}">+</button>
                `;
                container.appendChild(div);
            });
            
            // Bind add button handlers
            bindUploadBagianHandlers();
        }
        
        // Render cutting types dropdown dynamically
        function renderCuttingTypes() {
            const container = document.getElementById('cutting');
            if (!container) return;
            
            if (activeCuttingTypes.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-gray-500">Tidak ada jenis cutting yang tersedia</div>';
                return;
            }
            
            container.innerHTML = '';
            activeCuttingTypes.forEach(cutting => {
                const div = document.createElement('div');
                div.className = 'dropdown-item';
                div.innerHTML = `<span>${cutting.name}</span>`;
                div.onclick = function() {
                    selectOption('cutting', cutting.name, this);
                };
                container.appendChild(div);
            });
        }
        
        // Quantity handlers
        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            let newValue = parseInt(input.value) + change;
            newValue = Math.max(1, Math.min(99, newValue));
            input.value = newValue;
            updateQuantityFromInput();
        }
        
        function updateQuantityFromInput() {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value) || 1;
            value = Math.max(1, Math.min(99, value));
            input.value = value;
            calculateTotal();
        }
        
        // Calculate total price and update breakdown
        function calculateTotal() {
            const quantity = parseInt(document.getElementById('quantity')?.value) || 1;
            
            let perItemPrice = baseProductPrice;
            let uploadSectionsTotal = 0;
            let cuttingTypePrice = 0;
            
            // Calculate upload sections prices and build breakdown
            const uploadBreakdownContainer = document.getElementById('upload-sections-breakdown');
            let uploadBreakdownHTML = '';
            
            uploadedSections.forEach((value, key) => {
                // Extract code from name like "A (Dada depan...)"
                const code = key.match(/^([A-J])\s/)?.[1];
                if (code && designPrices.upload_sections[code]) {
                    const price = designPrices.upload_sections[code];
                    uploadSectionsTotal += price;
                    perItemPrice += price;
                    
                    // Add to breakdown display
                    uploadBreakdownHTML += `
                        <div class="breakdown-item">
                            <span class="breakdown-label">+ ${key.substring(0, 30)}${key.length > 30 ? '...' : ''}</span>
                            <span class="breakdown-value" style="color: #10b981;">+ Rp ${new Intl.NumberFormat('id-ID').format(price)}</span>
                        </div>
                    `;
                }
            });
            
            if (uploadBreakdownContainer) {
                uploadBreakdownContainer.innerHTML = uploadBreakdownHTML;
            }
            
            // Update upload count display
            const uploadCountDisplay = document.getElementById('upload-count-display');
            if (uploadCountDisplay) {
                uploadCountDisplay.textContent = `${uploadedSections.size} bagian`;
            }
            
            // Calculate cutting type price and build breakdown
            const cuttingBreakdownContainer = document.getElementById('cutting-type-breakdown');
            let cuttingBreakdownHTML = '';
            
            if (selectedOptions.cutting.length > 0) {
                const cuttingName = selectedOptions.cutting[0];
                let cuttingCode = '';
                
                // Match cutting name to code
                const cuttingMatch = activeCuttingTypes.find(ct => ct.name === cuttingName);
                if (cuttingMatch) {
                    cuttingCode = cuttingMatch.code;
                }
                
                if (cuttingCode && designPrices.cutting_types[cuttingCode]) {
                    cuttingTypePrice = designPrices.cutting_types[cuttingCode];
                    perItemPrice += cuttingTypePrice;
                    
                    cuttingBreakdownHTML = `
                        <div class="breakdown-item">
                            <span class="breakdown-label">+ Jenis Cutting: ${cuttingName}</span>
                            <span class="breakdown-value" style="color: #10b981;">+ Rp ${new Intl.NumberFormat('id-ID').format(cuttingTypePrice)}</span>
                        </div>
                    `;
                }
            }
            
            if (cuttingBreakdownContainer) {
                cuttingBreakdownContainer.innerHTML = cuttingBreakdownHTML;
            }
            
            // Calculate total with quantity
            const total = perItemPrice * quantity;
            
            // Update quantity display
            const quantityDisplay = document.getElementById('quantity-display');
            if (quantityDisplay) {
                quantityDisplay.textContent = `${quantity} pcs`;
            }
            
            // Update total display (both in breakdown and main display)
            const totalElements = document.querySelectorAll('.total-amount');
            totalElements.forEach(element => {
                element.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            });
        }
        
        // Load prices when page loads
        loadPrices();

        function toggleDropdown(id) {
            const content = document.getElementById(id);
            const header = content ? content.previousElementSibling : null;
            if (content && header) {
                const willActivate = !content.classList.contains('active');
                content.classList.toggle('active', willActivate);
                header.classList.toggle('active', willActivate);
            }
        }
        function closeDropdown(id) {
            const content = document.getElementById(id);
            const header = content ? content.previousElementSibling : null;
            if (content && header) {
                content.classList.remove('active');
                header.classList.remove('active');
            }
        }

        function selectOption(type, value, element) {
            if (type === 'cutting') {
                selectedOptions[type] = [value];
                document.querySelectorAll(`#${type} .dropdown-item`).forEach(it => it.classList.remove('option-item-active'));
                element.classList.add('option-item-active');
            } else {
                const index = selectedOptions[type].indexOf(value);
                if (index > -1) {
                    selectedOptions[type].splice(index, 1);
                    element.classList.remove('option-item-active');
                } else {
                    selectedOptions[type].push(value);
                    element.classList.add('option-item-active');
                }
            }
            updateSelectedDisplay(type);
            if (type === 'cutting') {
                closeDropdown(type);
            }
            calculateTotal();
        }

        function updateSelectedDisplay(type) {
            const container = document.getElementById(`selected-${type}`);
            if (!container) return;
            container.innerHTML = '';
            selectedOptions[type].forEach(option => {
                const row = document.createElement('div');
                row.className = 'file-uploaded uploaded-item selection-item';
                row.innerHTML = `
                    <span class="selection-text">${option}</span>
                    <button type="button" class="remove-file" onclick="removeOption('${type}', '${option}')">⊗</button>
                `;
                container.appendChild(row);
            });
        }

        function removeOption(type, value) {
            const index = selectedOptions[type].indexOf(value);
            if (index > -1) {
                selectedOptions[type].splice(index, 1);
                updateSelectedDisplay(type);
                calculateTotal();
                document.querySelectorAll(`#${type} .dropdown-item`).forEach(it => {
                    const text = it.querySelector('span')?.textContent?.trim();
                    if (text === value) it.classList.remove('option-item-active');
                });
            }
        }


        // Handle form submit
        document.getElementById('customDesignForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            // Set hidden inputs
            document.getElementById('cutting_type_input').value = selectedOptions.cutting[0] || '';
            document.getElementById('special_materials_input').value = JSON.stringify(selectedOptions.material);
            document.getElementById('additional_description_input').value = document.querySelector('.description-text').value;

            const form = e.target;
            const formData = new FormData(form);
            
            // Append quantity
            const quantity = document.getElementById('quantity').value;
            formData.append('quantity', quantity);

            // Append uploads
            let uploads = [];
            uploadedSections.forEach(({ file }, section_name) => {
                uploads.push({ section_name, file });
            });
            // Laravel expects uploads as uploads[0][section_name], uploads[0][file], ...
            uploads.forEach((item, idx) => {
                formData.append(`uploads[${idx}][section_name]`, item.section_name);
                formData.append(`uploads[${idx}][file]`, item.file);
            });

            try {
                const response = await fetch("{{ route('custom-design.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                    },
                    body: formData
                });
                
                const data = await response.json();
                console.log('Response:', data);
                
                if (data.success) {
                    // Redirect to order-list page
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.href = '{{ route("order-list") }}';
                    }
                } else {
                    alert(data.message || 'Gagal menyimpan pesanan custom desain.');
                }
            } catch (err) {
                console.error('Submit error:', err);
                alert('Terjadi kesalahan saat mengirim data: ' + err.message);
            }
        });

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function goToHome() {
            window.location.href = '/';
        }

        // Image Preview Modal Functions
        function openImagePreview(imageSrc, title) {
            const modal = document.getElementById('imagePreviewModal');
            const img = document.getElementById('imagePreviewImg');
            const titleElement = document.getElementById('imagePreviewTitle');
            
            if (modal && img && titleElement) {
                img.src = imageSrc;
                titleElement.textContent = title || 'Preview Produk';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImagePreview() {
            const modal = document.getElementById('imagePreviewModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        }

    // Upload Bagian logic
        const uploadedSections = new Map();
        let pendingUploadSection = null;

        // bind add buttons in Upload Bagian (called after dynamic render)
        function bindUploadBagianHandlers() {
            document.querySelectorAll('#uploadBagian .add-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const item = btn.closest('.dropdown-item');
                    const label = item?.querySelector('span')?.textContent?.trim() || '';
                    if (!label) return;
                    
                    // Check if this section has already been uploaded
                    if (uploadedSections.has(label)) {
                        alert('Bagian ini sudah diupload. Hapus terlebih dahulu jika ingin menggantinya.');
                        return;
                    }
                    
                    pendingUploadSection = { label, button: btn };
                    document.getElementById('uploadBagianFileInput').click();
                });
            });
        }

        // handle file pick
        const uploadInput = document.getElementById('uploadBagianFileInput');
        uploadInput.addEventListener('change', () => {
            const file = uploadInput.files && uploadInput.files[0];
            if (!file || !pendingUploadSection) return;
            if (!file.type.startsWith('image/')) {
                alert('Hanya file gambar yang diperbolehkan.');
                uploadInput.value = '';
                pendingUploadSection = null;
                return;
            }
            // Save
            uploadedSections.set(pendingUploadSection.label, { file, name: file.name });
            // Mark button
            pendingUploadSection.button.classList.add('option-select-active');
            // Render
            renderUploadedList();
            // Calculate total
            calculateTotal();
            // Close the upload bagian dropdown
            closeDropdown('uploadBagian');
            // Reset
            uploadInput.value = '';
            pendingUploadSection = null;
        });

        function renderUploadedList() {
            const container = document.getElementById('uploaded-bagian-list');
            if (!container) return;
            container.innerHTML = '';
            uploadedSections.forEach(({ name }, label) => {
                const row = document.createElement('div');
                row.className = 'file-uploaded uploaded-item';
                row.dataset.sectionLabel = label;
                row.innerHTML = `
                    <span><strong>${label}:</strong> ${name}</span>
                    <button type="button" class="remove-file" aria-label="Hapus file" title="Hapus file">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                `;
                row.querySelector('.remove-file').addEventListener('click', () => {
                    uploadedSections.delete(label);
                    // unmark the + button for this label
                    const item = [...document.querySelectorAll('#uploadBagian .dropdown-item')]
                        .find(it => it.querySelector('span')?.textContent?.trim() === label);
                    const addBtn = item?.querySelector('.add-btn');
                    if (addBtn) addBtn.classList.remove('option-select-active');
                    renderUploadedList();
                    calculateTotal();
                });
                container.appendChild(row);
            });
        }
        
        // Initialize image click handler after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎯 DOM Content Loaded - Initializing image...');
            
            const productImage = document.getElementById('productImage');
            
            if (productImage) {
                console.log('🖼️ Product image element found');
                console.log('📍 Image src:', productImage.src);
                console.log('📐 Image dimensions:', {
                    width: productImage.width,
                    height: productImage.height,
                    naturalWidth: productImage.naturalWidth,
                    naturalHeight: productImage.naturalHeight,
                    complete: productImage.complete
                });
                
                // Add click handler
                productImage.addEventListener('click', function() {
                    const imgSrc = this.src;
                    const imgAlt = this.alt;
                    console.log('🖱️ Image clicked, opening preview for:', imgSrc);
                    openImagePreview(imgSrc, imgAlt);
                });
            } else {
                console.error('❌ Product image element not found');
            }
        });
    </script>

    @stack('scripts')
</x-customer-layout>