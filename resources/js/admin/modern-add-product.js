// Modern Add Product Form JavaScript - Clean Version
class ModernAddProductManager {
    constructor() {
        this.savedColors = []; // All saved colors in DB
        this.selectedColors = []; // Selected colors for variants
        this.selectedSizes = [];
        this.variants = [];
        this.isEditMode = false;
        this.productId = null;
        this.isResizing = false;
        this.startX = 0;
        this.startWidth = 0;
        this.savedSubcategories = []; // Saved subcategories for "lainnya"

        // Subcategories mapping
        this.subcategories = {
            "topi": [
                "Topi Snapback",
                "Topi Trucker",
                "Topi Bucket",
                "Topi Baseball",
                "Topi Visor / Sport",
                "Beanie / Kupluk"
            ],
            "kaos": [
                "Kaos Polos",
                "Kaos Graphic",
                "Kaos Oversize",
                "Kaos Raglan",
                "Kaos Lengan Panjang",
                "Kaos Training / Sport"
            ],
            "polo": [
                "Polo Basic",
                "Polo Sport",
                "Polo Slim Fit",
                "Polo Lengan Panjang"
            ],
            "jaket": [
                "Hoodie",
                "Hoodie Zipper",
                "Jaket Bomber",
                "Jaket Varsity",
                "Jaket Windbreaker",
                "Jaket Running / Training",
                "Jaket Hujan / Raincoat"
            ],
            "jersey": [
                "Jersey Sepak Bola",
                "Jersey Futsal",
                "Jersey Basket",
                "Jersey Voli",
                "Jersey Badminton",
                "Jersey Running",
                "Jersey Custom Nama / Nomor"
            ],
            "celana": [
                "Celana Pendek Olahraga",
                "Celana Bola / Futsal",
                "Celana Running",
                "Celana Training Panjang",
                "Jogger Pants",
                "Legging Sport"
            ],
            "lainnya": [
                "Baby Jumper",
                "Apron",
                "Key Chain"
            ]
        };

        this.init();
    }

    init() {
        this.initResizeDrawer();
        this.loadSavedColors();
        this.loadSavedSubcategories();
        this.bindEvents();
        this.renderSavedColors();
        this.renderSelectedSizes();
        this.updateVariants();
    }

    // ============ RESIZE DRAWER FUNCTIONALITY ============
    initResizeDrawer() {
        const resizeHandle = document.getElementById('drawer-resize-handle');
        const drawer = document.getElementById('modern-product-drawer');

        if (!resizeHandle || !drawer) return;

        resizeHandle.addEventListener('mousedown', (e) => {
            this.isResizing = true;
            this.startX = e.clientX;
            this.startWidth = drawer.offsetWidth;
            drawer.classList.add('resizing');
            resizeHandle.classList.add('active');
            document.body.style.cursor = 'ew-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.isResizing) return;

            const deltaX = this.startX - e.clientX;
            const newWidth = this.startWidth + deltaX;
            const minWidth = 600;
            const maxWidth = window.innerWidth * 0.9;

            if (newWidth >= minWidth && newWidth <= maxWidth) {
                drawer.style.width = `${newWidth}px`;
            }
        });

        document.addEventListener('mouseup', () => {
            if (this.isResizing) {
                this.isResizing = false;
                drawer.classList.remove('resizing');
                resizeHandle.classList.remove('active');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            }
        });
    }

    // ============ EVENT BINDING ============
    bindEvents() {
        // Color input handling
        const colorInput = document.getElementById('modern-color-code-input');
        const colorSwatch = document.getElementById('modern-color-swatch');
        const addColorBtn = document.getElementById('modern-add-color-btn');

        if (colorSwatch && colorInput) {
            colorSwatch.addEventListener('click', () => {
                colorInput.click();
            });
        }

        if (colorInput) {
            colorInput.addEventListener('input', (e) => {
                this.handleColorInput(e.target.value);
            });
        }

        if (addColorBtn) {
            addColorBtn.addEventListener('click', () => this.saveColorToDB());
        }

        // Size chips
        const sizeButtons = document.querySelectorAll('#modern-sizes-group .size-chip');
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSize(btn.dataset.value);
            });
        });

        // Category change - update subcategory options
        const categorySelect = document.getElementById('modern-product-category');
        if (categorySelect) {
            // Check initial state on page load
            if (categorySelect.value) {
                this.updateSubcategoryOptions(categorySelect.value);
                this.updateCustomDesignAvailability(categorySelect.value);
            }
            
            // Listen to changes
            categorySelect.addEventListener('change', (e) => {
                this.updateSubcategoryOptions(e.target.value);
                this.updateCustomDesignAvailability(e.target.value);
            });
        }
        
        // Subcategory autocomplete input
        const subcategoryInput = document.getElementById('modern-product-subcategory-lainnya');
        if (subcategoryInput) {
            // Show dropdown on focus
            subcategoryInput.addEventListener('focus', () => {
                this.showSubcategoryDropdown();
            });
            
            // Filter on input
            subcategoryInput.addEventListener('input', (e) => {
                this.filterSubcategories(e.target.value);
            });
            
            // Handle enter key to save new subcategory
            subcategoryInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = e.target.value.trim();
                    if (value) {
                        this.saveNewSubcategory(value);
                    }
                }
            });
        }
        
        // Add new subcategory button in dropdown
        const addNewSubcategoryBtn = document.getElementById('add-new-subcategory-btn');
        if (addNewSubcategoryBtn) {
            addNewSubcategoryBtn.addEventListener('click', () => {
                const input = document.getElementById('modern-product-subcategory-lainnya');
                if (input && input.value.trim()) {
                    this.saveNewSubcategory(input.value.trim());
                } else {
                    input.focus();
                }
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('subcategory-dropdown');
            const input = document.getElementById('modern-product-subcategory-lainnya');
            if (dropdown && input && !dropdown.contains(e.target) && e.target !== input) {
                this.hideSubcategoryDropdown();
            }
        });

        // Toggle switches
        const statusToggle = document.getElementById('modern-product-status');
        const customDesignToggle = document.getElementById('modern-custom-design-allowed');

        if (statusToggle) {
            statusToggle.addEventListener('change', () => {
                const statusText = document.getElementById('status-text');
                if (statusText) {
                    statusText.textContent = statusToggle.checked ? 'Aktif' : 'Draft';
                }
            });
        }

        if (customDesignToggle) {
            customDesignToggle.addEventListener('change', () => {
                const customText = document.getElementById('custom-design-text');
                if (customText) {
                    customText.textContent = customDesignToggle.checked ? 'Aktif' : 'Tidak Aktif';
                }
                
                // Show/hide custom design price configuration
                const priceSection = document.getElementById('custom-design-price-section');
                if (priceSection) {
                    if (customDesignToggle.checked) {
                        priceSection.style.display = 'block';
                        this.loadCustomDesignPrices();
                    } else {
                        priceSection.style.display = 'none';
                    }
                }
            });
        }

        // Form submission
        const form = document.getElementById('modern-product-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    // ============ COLOR MANAGEMENT ============
    handleColorInput(value) {
        const swatch = document.getElementById('modern-color-swatch');
        const codeDisplay = document.getElementById('modern-color-code');

        if (swatch) {
            swatch.style.backgroundColor = value;
        }
        if (codeDisplay) {
            codeDisplay.textContent = value.toUpperCase();
        }
    }

    // ============ CUSTOM DESIGN AVAILABILITY ============
    updateCustomDesignAvailability(categoryValue) {
        const customDesignToggle = document.getElementById('modern-custom-design-allowed');
        const customDesignText = document.getElementById('custom-design-text');
        const customDesignControl = customDesignToggle?.closest('.control-item');
        
        // Kategori yang tidak support custom design
        const noCustomDesignCategories = ['lainnya', 'topi', 'celana'];
        
        if (noCustomDesignCategories.includes(categoryValue)) {
            // Disable custom design
            if (customDesignToggle) {
                customDesignToggle.checked = false;
                customDesignToggle.disabled = true;
            }
            if (customDesignText) {
                customDesignText.textContent = 'Tidak Tersedia';
                customDesignText.style.color = '#9ca3af';
            }
            if (customDesignControl) {
                customDesignControl.style.opacity = '0.5';
                customDesignControl.style.pointerEvents = 'none';
            }
        } else {
            // Enable custom design
            if (customDesignToggle) {
                customDesignToggle.disabled = false;
            }
            if (customDesignText) {
                customDesignText.textContent = customDesignToggle?.checked ? 'Aktif' : 'Tidak Aktif';
                customDesignText.style.color = '';
            }
            if (customDesignControl) {
                customDesignControl.style.opacity = '1';
                customDesignControl.style.pointerEvents = '';
            }
        }
    }
    
    // ============ SUBCATEGORY UPDATE ============
    updateSubcategoryOptions(categoryValue) {
        console.log('updateSubcategoryOptions called with category:', categoryValue);
        
        const subcategorySelect = document.getElementById('modern-product-subcategory');
        const subcategorySelectGroup = document.getElementById('subcategory-select-group');
        const subcategoryCustomGroup = document.getElementById('subcategory-custom-group');
        const subcategoryCustomInput = document.getElementById('modern-product-subcategory-custom');
        
        if (!subcategorySelect) {
            console.warn('subcategorySelect not found');
            return;
        }

        // Toggle between dropdown and custom input based on category
        if (categoryValue === 'lainnya') {
            console.log('Category is "lainnya" - showing custom input');
            // Show custom input, hide dropdown
            if (subcategorySelectGroup) {
                subcategorySelectGroup.style.display = 'none';
                console.log('Hidden subcategory dropdown');
            }
            if (subcategoryCustomGroup) {
                subcategoryCustomGroup.style.display = 'block';
                console.log('Shown custom subcategory input');
            }
            
            // Clear input and hide dropdown
            const subcategoryLainnyaInput = document.getElementById('modern-product-subcategory-lainnya');
            if (subcategoryLainnyaInput) {
                subcategoryLainnyaInput.value = '';
                subcategoryLainnyaInput.required = false;
            }
            this.hideSubcategoryDropdown();
            
            // Render subcategory options for "lainnya"
            this.renderSubcategoryOptions();
            
            if (subcategorySelect) subcategorySelect.required = false;
        } else {
            console.log('Category is NOT "lainnya" - showing dropdown');
            // Show dropdown, hide custom input
            if (subcategorySelectGroup) {
                subcategorySelectGroup.style.display = 'block';
                console.log('Shown subcategory dropdown');
            }
            if (subcategoryCustomGroup) {
                subcategoryCustomGroup.style.display = 'none';
                console.log('Hidden custom subcategory input');
            }
            
            // Clear input and hide dropdown
            const subcategoryLainnyaInput = document.getElementById('modern-product-subcategory-lainnya');
            if (subcategoryLainnyaInput) {
                subcategoryLainnyaInput.value = '';
                subcategoryLainnyaInput.required = false;
            }
            this.hideSubcategoryDropdown();
            
            if (subcategorySelect) subcategorySelect.required = false;
        }

        // Clear existing options except the first (placeholder)
        subcategorySelect.innerHTML = '<option value="">Pilih sub kategori</option>';

        // Get subcategories for selected category
        const subcats = this.subcategories[categoryValue] || [];

        // Add new options
        subcats.forEach(subcat => {
            const option = document.createElement('option');
            option.value = subcat.toLowerCase().replace(/\s+/g, '-');
            option.textContent = subcat;
            subcategorySelect.appendChild(option);
        });

        // Reset selected value
        subcategorySelect.value = '';
    }

    async loadSavedColors() {
        try {
            const response = await fetch('/admin/api/colors', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            if (result.success) {
                this.savedColors = result.data || [];
                this.renderSavedColors();
            }
        } catch (error) {
            console.error('Error loading colors:', error);
            this.savedColors = [];
        }
    }
    
    async loadSavedSubcategories() {
        try {
            const response = await fetch('/admin/api/subcategories', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            if (result.success) {
                this.savedSubcategories = result.data || [];
                console.log('Loaded subcategories:', this.savedSubcategories);
                
                // Update subcategory options if category is "lainnya"
                const categorySelect = document.getElementById('modern-product-category');
                if (categorySelect && categorySelect.value === 'lainnya') {
                    this.renderSubcategoryOptions();
                }
            }
        } catch (error) {
            console.error('Error loading subcategories:', error);
            this.savedSubcategories = [];
        }
    }
    
    
    renderSubcategoryOptions(filterText = '') {
        const subcategoryList = document.getElementById('subcategory-list');
        if (!subcategoryList) return;
        
        // Clear existing items
        subcategoryList.innerHTML = '';
        
        // Combine hard-coded subcategories with saved subcategories
        const hardCodedSubcategories = this.subcategories['lainnya'] || [];
        const allSubcategories = Array.from(new Set([...hardCodedSubcategories, ...this.savedSubcategories]));
        
        // Filter subcategories based on input
        const filteredSubcategories = filterText
            ? allSubcategories.filter(name => 
                name.toLowerCase().includes(filterText.toLowerCase())
              )
            : allSubcategories;
        
        // Get current selected value
        const currentInput = document.getElementById('modern-product-subcategory-lainnya');
        const currentValue = currentInput ? currentInput.value : '';
        
        if (filteredSubcategories.length === 0) {
            subcategoryList.innerHTML = '<div class="subcategory-empty">Tidak ada sub kategori yang cocok.<br>Ketik dan tekan Enter untuk menambahkan.</div>';
            return;
        }
        
        // Add filtered subcategories
        filteredSubcategories.forEach(name => {
            const item = document.createElement('div');
            item.className = 'subcategory-item';
            if (name.toLowerCase() === currentValue.toLowerCase()) {
                item.classList.add('selected');
            }
            
            item.innerHTML = `
                <span>${name}</span>
                <i class="fas fa-check"></i>
            `;
            
            item.addEventListener('click', () => {
                this.selectSubcategory(name);
            });
            
            subcategoryList.appendChild(item);
        });
    }
    
    showSubcategoryDropdown() {
        const dropdown = document.getElementById('subcategory-dropdown');
        if (dropdown) {
            dropdown.style.display = 'flex';
            this.renderSubcategoryOptions();
        }
    }
    
    hideSubcategoryDropdown() {
        const dropdown = document.getElementById('subcategory-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }
    
    filterSubcategories(filterText) {
        this.renderSubcategoryOptions(filterText);
        const dropdown = document.getElementById('subcategory-dropdown');
        if (dropdown) {
            dropdown.style.display = 'flex';
        }
    }
    
    selectSubcategory(name) {
        const input = document.getElementById('modern-product-subcategory-lainnya');
        if (input) {
            input.value = name;
        }
        this.hideSubcategoryDropdown();
        this.renderSubcategoryOptions(); // Re-render to update selected state
    }
    
    async saveNewSubcategory(name) {
        // Check if already exists
        const hardCodedSubcategories = this.subcategories['lainnya'] || [];
        const allSubcategories = new Set([...hardCodedSubcategories, ...this.savedSubcategories]);
        
        const exists = Array.from(allSubcategories).some(
            existing => existing.toLowerCase() === name.toLowerCase()
        );
        
        if (exists) {
            this.showNotification('Sub kategori sudah ada', 'warning');
            this.selectSubcategory(name);
            return;
        }
        
        try {
            const response = await fetch('/admin/api/subcategories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.savedSubcategories.push(name);
                this.showNotification('Sub kategori berhasil ditambahkan', 'success');
                this.selectSubcategory(name);
                this.renderSubcategoryOptions();
            } else {
                this.showNotification(result.message || 'Gagal menambahkan sub kategori', 'error');
            }
        } catch (error) {
            console.error('Error adding subcategory:', error);
            this.showNotification('Gagal menambahkan sub kategori', 'error');
        }
    }
    
    getSubcategoryDisplayName(slug) {
        // Get all available subcategories
        const hardCodedSubcategories = this.subcategories['lainnya'] || [];
        const allSubcategories = [...hardCodedSubcategories, ...this.savedSubcategories];
        
        // Try to find exact match first
        const exactMatch = allSubcategories.find(name => 
            name.toLowerCase().replace(/\s+/g, '-') === slug
        );
        
        if (exactMatch) return exactMatch;
        
        // If no exact match, try to convert slug to title case
        return slug.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    async saveColorToDB() {
        const colorInput = document.getElementById('modern-color-code-input');
        if (!colorInput) return;

        const color = colorInput.value.toUpperCase();
        
        // Check if already exists in saved colors
        if (this.savedColors.includes(color)) {
            this.showNotification('Warna sudah ada di daftar', 'warning');
            return;
        }

        // Save to database
        try {
            const response = await fetch('/admin/api/colors', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ color: color })
            });

            const result = await response.json();
            
            if (result.success) {
                this.savedColors = result.data || [];
                this.renderSavedColors();
                this.showNotification('Warna berhasil disimpan', 'success');
            } else {
                this.showNotification(result.message || 'Gagal menyimpan warna', 'error');
            }
        } catch (error) {
            console.error('Error saving color:', error);
            this.showNotification('Gagal menyimpan warna ke database', 'error');
        }
    }

    toggleColorSelection(color) {
        const index = this.selectedColors.indexOf(color);
        
        if (index > -1) {
            // Remove from selection
            this.selectedColors.splice(index, 1);
        } else {
            // Add to selection
            this.selectedColors.push(color);
        }

        this.renderSavedColors();
        this.updateColorsInput();
        this.updateVariants();
    }

    async deleteColorFromDB(color, event) {
        event.stopPropagation();
        
        if (!confirm(`Hapus warna ${color} dari database?`)) return;

        try {
            const response = await fetch(`/admin/api/colors/${encodeURIComponent(color)}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                this.savedColors = result.data || [];
                // Also remove from selection if it was selected
                const selIndex = this.selectedColors.indexOf(color);
                if (selIndex > -1) {
                    this.selectedColors.splice(selIndex, 1);
                }
                this.renderSavedColors();
                this.updateColorsInput();
                this.updateVariants();
                this.showNotification('Warna berhasil dihapus', 'success');
            } else {
                this.showNotification('Gagal menghapus warna', 'error');
            }
        } catch (error) {
            console.error('Error deleting color:', error);
            this.showNotification('Gagal menghapus warna', 'error');
        }
    }

    renderSavedColors() {
        const container = document.getElementById('modern-saved-colors-container');
        if (!container) return;

        container.innerHTML = '';

        this.savedColors.forEach(color => {
            const colorItem = document.createElement('button');
            colorItem.type = 'button';
            colorItem.className = 'saved-color-item';
            colorItem.style.backgroundColor = color;
            
            // Add selected class if color is selected
            if (this.selectedColors.includes(color)) {
                colorItem.classList.add('selected');
            }
            
            // Toggle selection on click
            colorItem.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleColorSelection(color);
            });

            // Remove button (delete from DB)
            const removeBtn = document.createElement('span');
            removeBtn.className = 'remove-color';
            removeBtn.innerHTML = '×';
            removeBtn.addEventListener('click', (e) => {
                this.deleteColorFromDB(color, e);
            });
            
            colorItem.appendChild(removeBtn);
            container.appendChild(colorItem);
        });
    }

    updateColorsInput() {
        const colorsInput = document.getElementById('modern-colors-input');
        if (colorsInput) {
            colorsInput.value = JSON.stringify(this.selectedColors);
        }
    }

    // ============ SIZE MANAGEMENT ============
    toggleSize(size) {
        const index = this.selectedSizes.indexOf(size);
        
        if (index > -1) {
            // Remove size
            this.selectedSizes.splice(index, 1);
        } else {
            // Add size
            this.selectedSizes.push(size);
        }

        this.renderSelectedSizes();
        this.updateSizesInput();
        this.updateVariants();
    }

    renderSelectedSizes() {
        const sizeButtons = document.querySelectorAll('#modern-sizes-group .size-chip');
        sizeButtons.forEach(btn => {
            if (this.selectedSizes.includes(btn.dataset.value)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    updateSizesInput() {
        const sizesInput = document.getElementById('modern-sizes-input');
        if (sizesInput) {
            sizesInput.value = JSON.stringify(this.selectedSizes);
        }
    }

    // ============ VARIANTS AUTO GENERATION ============
    updateVariants() {
        // Simpan data varian yang sudah ada sebelumnya
        const existingVariants = {};
        this.variants.forEach(v => {
            const key = `${v.color}_${v.size}`;
            existingVariants[key] = {
                price: v.price,
                original_price: v.original_price,
                stock: v.stock,
                image: v.image,
                imagePreview: v.imagePreview
            };
        });

        this.variants = [];

        // Generate variants from colors x sizes
        this.selectedColors.forEach(color => {
            this.selectedSizes.forEach(size => {
                const key = `${color}_${size}`;
                const existing = existingVariants[key];
                
                this.variants.push({
                    color: color,
                    size: size,
                    price: existing ? existing.price : 0,
                    original_price: existing ? existing.original_price : 0,
                    stock: existing ? existing.stock : 0,
                    image: existing ? existing.image : null,
                    imagePreview: existing ? existing.imagePreview : null
                });
            });
        });

        this.renderVariants();
    }

    renderVariants() {
        const tbody = document.getElementById('modern-variants-tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (this.variants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-8 text-zinc-500">
                        Pilih warna dan ukuran untuk generate varian
                    </td>
                </tr>
            `;
            return;
        }

        this.variants.forEach((variant, index) => {
            const row = document.createElement('tr');
            row.className = 'variant-row';
            
            // Create image preview HTML
            let imagePreviewHTML = '';
            if (variant.imagePreview) {
                imagePreviewHTML = `
                    <div class="variant-image-preview">
                        <img src="${variant.imagePreview}" alt="Preview">
                        <button type="button" class="remove-variant-image" onclick="modernAddProductManager.removeVariantImage(${index}, event)">×</button>
                    </div>
                `;
            }
            
            row.innerHTML = `
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="width: 1.5rem; height: 1.5rem; border-radius: 0.375rem; border: 1px solid #e5e7eb; background-color: ${variant.color};"></span>
                        <div>
                            <div style="font-weight: 500; color: #18181b;">${variant.color}</div>
                            <div style="font-size: 0.75rem; color: #71717a;">Size ${variant.size}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" placeholder="Harga jual" class="variant-input" value="${variant.price}" min="0" step="1000"
                           onchange="modernAddProductManager.updateVariantPrice(${index}, this.value)" title="Harga yang dibayar customer">
                </td>
                <td>
                    <input type="number" placeholder="Harga sebelum diskon (opsional)" class="variant-input" value="${variant.original_price}" min="0" step="1000"
                           onchange="modernAddProductManager.updateVariantOriginalPrice(${index}, this.value)" title="Harga coret - kosongkan jika tidak ada diskon">
                </td>
                <td>
                    <input type="number" placeholder="0" class="variant-input" value="${variant.stock}" min="0"
                           onchange="modernAddProductManager.updateVariantStock(${index}, this.value)">
                </td>
                <td>
                    <div class="variant-image-cell">
                        ${imagePreviewHTML}
                        <input type="file" class="variant-file-input" id="variant-file-${index}" accept="image/*" style="display: none;"
                               onchange="modernAddProductManager.updateVariantImage(${index}, this)">
                        <button type="button" class="variant-upload-btn" onclick="document.getElementById('variant-file-${index}').click()">
                            ${variant.imagePreview ? 'Ganti' : 'Upload'}
                        </button>
                    </div>
                </td>
                <td>
                    <button type="button" title="Hapus" class="variant-action-btn delete" onclick="modernAddProductManager.removeVariant(${index})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1rem; height: 1rem;">
                            <path d="M3 6h18" />
                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M10 11v6M14 11v6" />
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    updateVariantPrice(index, value) {
        if (this.variants[index]) {
            this.variants[index].price = parseFloat(value) || 0;
        }
    }

    updateVariantOriginalPrice(index, value) {
        if (this.variants[index]) {
            this.variants[index].original_price = parseFloat(value) || 0;
        }
    }

    updateVariantStock(index, value) {
        if (this.variants[index]) {
            this.variants[index].stock = parseInt(value) || 0;
        }
    }

    updateVariantImage(index, input) {
        if (this.variants[index] && input.files[0]) {
            const file = input.files[0];
            
            console.log(`📸 Uploading image for variant ${index}:`, {
                name: file.name,
                size: file.size,
                type: file.type
            });
            
            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                this.showNotification('Gambar terlalu besar. Max 10MB', 'error');
                input.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.match('image.*')) {
                this.showNotification('File harus berupa gambar', 'error');
                input.value = '';
                return;
            }
            
            // Store file and create preview
            this.variants[index].image = file;
            console.log(`✅ Image stored in variant ${index}:`, this.variants[index].image);
            
            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.variants[index].imagePreview = e.target.result;
                console.log(`✅ Preview created for variant ${index}`);
                this.renderVariants();
            };
            reader.onerror = (e) => {
                console.error(`❌ Error creating preview for variant ${index}:`, e);
            };
            reader.readAsDataURL(file);
        }
    }

    removeVariantImage(index, event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (this.variants[index]) {
            this.variants[index].image = null;
            this.variants[index].imagePreview = null;
            this.renderVariants();
        }
    }

    removeVariant(index) {
        if (confirm('Hapus varian ini?')) {
            this.variants.splice(index, 1);
            this.renderVariants();
        }
    }

    // ============ FORM ACTIONS ============
    resetForm() {
        const form = document.getElementById('modern-product-form');
        if (form) form.reset();
        
        this.selectedColors = [];
        this.selectedSizes = [];
        this.variants = [];
        this.isEditMode = false;
        this.productId = null;
        
        this.renderSavedColors();
        this.renderSelectedSizes();
        this.updateVariants();

        // Reset subcategory to default
        this.updateSubcategoryOptions('');
        
        // Reset custom subcategory input
        const subcategoryLainnyaInput = document.getElementById('modern-product-subcategory-lainnya');
        if (subcategoryLainnyaInput) {
            subcategoryLainnyaInput.value = '';
        }
        this.hideSubcategoryDropdown();

        // Reset toggles
        const statusToggle = document.getElementById('modern-product-status');
        const statusText = document.getElementById('status-text');
        if (statusToggle && statusText) {
            statusToggle.checked = true;
            statusText.textContent = 'Aktif';
        }

        const customToggle = document.getElementById('modern-custom-design-allowed');
        const customText = document.getElementById('custom-design-text');
        const customDesignControl = customToggle?.closest('.control-item');
        if (customToggle && customText) {
            customToggle.checked = false;
            customToggle.disabled = false;
            customText.textContent = 'Tidak Aktif';
            customText.style.color = '';
        }
        if (customDesignControl) {
            customDesignControl.style.opacity = '1';
            customDesignControl.style.pointerEvents = '';
        }
    }

    populateForm(product) {
        // Basic info
        document.getElementById('modern-product-name').value = product.name || '';
        document.getElementById('modern-product-category').value = product.category || '';
        
        // Update subcategory options first based on category
        this.updateSubcategoryOptions(product.category || '');
        
        // Update custom design availability based on category
        this.updateCustomDesignAvailability(product.category || '');
        
        // Handle subcategory based on category type
        if (product.category === 'lainnya') {
            // For "lainnya", use lainnya input (text field, not select)
            const subcategoryLainnyaInput = document.getElementById('modern-product-subcategory-lainnya');
            if (subcategoryLainnyaInput && product.subcategory) {
                // Convert slug to display name if needed
                const subcategoryName = this.getSubcategoryDisplayName(product.subcategory);
                subcategoryLainnyaInput.value = subcategoryName;
            }
        } else {
            // For other categories, use dropdown
            document.getElementById('modern-product-subcategory').value = product.subcategory || '';
        }
        
        document.getElementById('modern-product-description').value = product.description || '';
        
        // Price & stock will be auto-calculated from variants, no need to set here
        
        // Status toggles
        const statusToggle = document.getElementById('modern-product-status');
        const statusText = document.getElementById('status-text');
        if (statusToggle && statusText) {
            statusToggle.checked = product.is_active || false;
            statusText.textContent = product.is_active ? 'Aktif' : 'Draft';
        }

        const customToggle = document.getElementById('modern-custom-design-allowed');
        const customText = document.getElementById('custom-design-text');
        if (customToggle && customText) {
            customToggle.checked = product.custom_design_allowed || false;
            customText.textContent = product.custom_design_allowed ? 'Aktif' : 'Tidak Aktif';
            
            // Show custom design price section if enabled
            if (product.custom_design_allowed) {
                const priceSection = document.getElementById('custom-design-price-section');
                if (priceSection) {
                    priceSection.style.display = 'block';
                    // Load custom design prices and then populate selected ones
                    this.loadCustomDesignPrices().then(() => {
                        if (product.custom_design_prices && Array.isArray(product.custom_design_prices)) {
                            this.populateSelectedCustomDesignPrices(product.custom_design_prices);
                        }
                    });
                }
            }
        }

        // Colors & Sizes
        if (product.colors && Array.isArray(product.colors)) {
            this.selectedColors = product.colors;
            this.renderSavedColors(); // Re-render to show selected state
            this.updateColorsInput();
        }

        if (product.sizes && Array.isArray(product.sizes)) {
            this.selectedSizes = product.sizes;
            this.renderSelectedSizes();
            this.updateSizesInput();
        }

        // Load variants from product
        if (product.variants && Array.isArray(product.variants)) {
            this.variants = product.variants.map(v => ({
                color: v.color,
                size: v.size,
                price: v.price || 0,
                original_price: v.original_price || 0,
                stock: v.stock || 0,
                image: null, // Will be set to File object if user uploads new image
                imagePreview: v.image ? `/storage/${v.image}` : null,
                existingImagePath: v.image || null // Keep track of existing image path
            }));
            console.log('📦 Loaded variants from product:', this.variants);
        } else {
            this.updateVariants();
        }

        this.renderVariants();
    }

    async handleSubmit(e) {
        e.preventDefault();

        // ALWAYS auto-calculate price & stock from variants
        const priceInput = document.getElementById('modern-product-price');
        const stockInput = document.getElementById('modern-product-stock');

        if (this.variants.length > 0) {
            // Get minimum price from variants
            const prices = this.variants.map(v => v.price).filter(p => p > 0);
            if (prices.length > 0) {
                priceInput.value = Math.min(...prices);
            } else {
                // No price set in variants, use default
                priceInput.value = 0;
            }

            // Calculate total stock from variants
            const totalStock = this.variants.reduce((sum, v) => sum + (parseInt(v.stock) || 0), 0);
            stockInput.value = totalStock;
        } else {
            // No variants, set to 0
            priceInput.value = 0;
            stockInput.value = 0;
        }

        const formData = new FormData(e.target);

        // Handle subcategory for "lainnya" category
        const categorySelect = document.getElementById('modern-product-category');
        
        if (categorySelect && categorySelect.value === 'lainnya') {
            // Use input value from subcategory-lainnya (now a text input)
            const subcategoryLainnyaInput = document.getElementById('modern-product-subcategory-lainnya');
            if (subcategoryLainnyaInput && subcategoryLainnyaInput.value) {
                // Convert to slug format
                const slug = subcategoryLainnyaInput.value.toLowerCase().replace(/\s+/g, '-');
                formData.set('subcategory', slug);
            }
        }

        // Fix is_active value (checkbox to 1/0)
        const statusToggle = document.getElementById('modern-product-status');
        if (statusToggle) {
            formData.set('is_active', statusToggle.checked ? '1' : '0');
        }

        // Fix custom_design_allowed value
        const customToggle = document.getElementById('modern-custom-design-allowed');
        if (customToggle) {
            formData.set('custom_design_allowed', customToggle.checked ? '1' : '0');
        }

        // Add variant images
        console.log('Adding variant images to FormData...');
        this.variants.forEach((variant, index) => {
            if (variant.image) {
                console.log(`Variant ${index}: Adding image`, variant.image.name, variant.image.size);
                formData.append(`variant_images[${index}]`, variant.image);
            } else {
                console.log(`Variant ${index}: No image`);
            }
        });

        // Add variants data (without image files, those are separate)
        const variantsData = this.variants.map(v => ({
            color: v.color,
            size: v.size,
            price: v.price,
            original_price: v.original_price,
            stock: v.stock
        }));
        console.log('Variants data:', variantsData);
        formData.append('variants', JSON.stringify(variantsData));

        // Add colors & sizes (as JSON strings)
        formData.set('colors', JSON.stringify(this.selectedColors));
        formData.set('sizes', JSON.stringify(this.selectedSizes));

        // Add custom design prices if custom design is enabled
        if (customToggle && customToggle.checked) {
            const customPricesData = this.getCustomDesignPricesData();
            
            // Debug logging
            console.log('=== CUSTOM DESIGN DEBUG ===');
            console.log('Custom Toggle Checked:', customToggle.checked);
            console.log('Custom Prices Data:', customPricesData);
            console.log('Data Count:', customPricesData.length);
            console.log('IDs:', customPricesData.map(p => p.custom_design_price_id));
            console.log('===========================');
            
            formData.set('custom_design_prices', JSON.stringify(customPricesData));
        } else {
            console.log('⚠️ Custom design prices NOT sent: toggle checked =', customToggle?.checked);
        }

        try {
            const url = window.currentEditId
                ? `/admin/api/products/${window.currentEditId}`
                : '/admin/api/products';

            // Debug logging
            console.log('=== SUBMIT DEBUG ===');
            console.log('currentEditId:', window.currentEditId);
            console.log('URL:', url);
            console.log('Method:', window.currentEditId ? 'PUT (via POST)' : 'POST');

            if (window.currentEditId) {
                formData.append('_method', 'PUT');
                console.log('Added _method=PUT to FormData');
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            console.log('Response:', data);

            if (data.success) {
                this.showNotification(
                    window.currentEditId ? 'Produk berhasil diperbarui' : 'Produk berhasil ditambahkan',
                    'success'
                );
                
                setTimeout(() => {
                    const closeBtn = document.getElementById('modern-drawer-close');
                    if (closeBtn) closeBtn.click();
                    
                    // Reset currentEditId after successful save
                    window.currentEditId = null;
                    console.log('Reset currentEditId to null');
                    
                    // Collapse all variant rows before reload to prevent confusion
                    document.querySelectorAll('.variant-data-row').forEach(row => {
                        row.style.display = 'none';
                    });
                    
                    // Reset chevron icons
                    document.querySelectorAll('[data-action="view"] i').forEach(icon => {
                        icon.className = 'fas fa-chevron-down';
                    });
                    
                    if (window.loadProducts) window.loadProducts();
                }, 1500);
            } else {
                this.showNotification(data.message || 'Gagal menyimpan produk', 'error');
            }
        } catch (error) {
            console.error('Error saving product:', error);
            this.showNotification('Gagal menyimpan produk', 'error');
        }
    }

    showNotification(message, type = 'info') {
        // Improved notification with better UI
        const colors = {
            success: { bg: '#10b981', icon: 'fa-check-circle' },
            error: { bg: '#ef4444', icon: 'fa-times-circle' },
            warning: { bg: '#f59e0b', icon: 'fa-exclamation-triangle' },
            info: { bg: '#3b82f6', icon: 'fa-info-circle' }
        };
        
        const config = colors[type] || colors.info;
        
        const notification = document.createElement('div');
        notification.className = 'toast-notification';
        notification.style.cssText = `
            position: fixed;
            top: 24px;
            right: 24px;
            background: white;
            color: #1f2937;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 320px;
            max-width: 420px;
            border-left: 4px solid ${config.bg};
            animation: slideInRight 0.3s ease-out;
            font-family: 'Poppins', sans-serif;
        `;
        
        notification.innerHTML = `
            <div style="
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: ${config.bg};
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            ">
                <i class="fas ${config.icon}" style="font-size: 18px;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 14px; margin-bottom: 2px;">
                    ${type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : type === 'warning' ? 'Peringatan!' : 'Info'}
                </div>
                <div style="font-size: 13px; color: #6b7280; line-height: 1.4;">
                    ${message}
                </div>
            </div>
            <button onclick="this.parentElement.remove()" style="
                background: transparent;
                border: none;
                color: #9ca3af;
                cursor: pointer;
                padding: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: all 0.2s;
            " onmouseover="this.style.background='#f3f4f6'; this.style.color='#1f2937'" onmouseout="this.style.background='transparent'; this.style.color='#9ca3af'">
                <i class="fas fa-times" style="font-size: 14px;"></i>
            </button>
        `;
        
        // Add animation styles if not exists
        if (!document.getElementById('toast-animation-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-animation-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        // Auto remove after 4 seconds with fade out animation
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }

    // ============ CUSTOM DESIGN PRICES ============
    async loadCustomDesignPrices() {
        const loading = document.getElementById('custom-prices-loading');
        const uploadTable = document.getElementById('upload-sections-table');
        const cuttingTable = document.getElementById('cutting-types-table');
        
        try {
            const response = await fetch('/api/custom-design-prices');
            const data = await response.json();
            
            if (data.success) {
                this.customDesignPrices = data.data;
                this.renderCustomDesignPrices();
                
                // Hide loading, show tables
                if (loading) loading.style.display = 'none';
                if (uploadTable) uploadTable.style.display = 'block';
                if (cuttingTable) cuttingTable.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading custom design prices:', error);
            if (loading) {
                loading.innerHTML = '<i class="fas fa-exclamation-circle"></i> Gagal memuat data harga custom design';
                loading.style.color = '#ef4444';
            }
        }
    }

    renderCustomDesignPrices() {
        const uploadTbody = document.getElementById('upload-sections-tbody');
        const cuttingTbody = document.getElementById('cutting-types-tbody');
        
        if (!uploadTbody || !cuttingTbody) return;
        
        // Render Upload Sections
        uploadTbody.innerHTML = '';
        this.customDesignPrices.upload_sections.forEach(item => {
            const row = this.createPriceRow(item);
            uploadTbody.appendChild(row);
        });
        
        // Render Cutting Types
        cuttingTbody.innerHTML = '';
        this.customDesignPrices.cutting_types.forEach(item => {
            const row = this.createPriceRow(item);
            cuttingTbody.appendChild(row);
        });
        
        // Setup select all checkboxes
        this.setupSelectAllCheckboxes();
    }

    createPriceRow(item) {
        const tr = document.createElement('tr');
        tr.dataset.priceId = item.id;
        tr.dataset.defaultPrice = item.price;
        
        // IMPORTANT: For edit mode, render all as unchecked initially
        // populateSelectedCustomDesignPrices() will check the product-specific ones
        tr.innerHTML = `
            <td>
                <input type="checkbox" 
                       class="custom-price-checkbox" 
                       data-price-id="${item.id}">
            </td>
            <td><span class="code-badge-small">${item.code}</span></td>
            <td>${item.name}</td>
            <td>
                <input type="number" 
                       class="price-input-small custom-price-input" 
                       data-price-id="${item.id}"
                       placeholder="Masukkan harga"
                       value="${item.price || ''}"
                       min="0"
                       step="1000"
                       required>
            </td>
            <td>
                <div class="toggle-switch-small" 
                     data-price-id="${item.id}"
                     onclick="window.modernAddProductManager.toggleCustomPriceStatus(this)">
                </div>
            </td>
        `;
        
        return tr;
    }

    setupSelectAllCheckboxes() {
        const selectAllUploads = document.getElementById('select-all-uploads');
        const selectAllCutting = document.getElementById('select-all-cutting');
        
        if (selectAllUploads) {
            selectAllUploads.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('#upload-sections-tbody .custom-price-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    const priceId = cb.dataset.priceId;
                    const toggle = document.querySelector(`.toggle-switch-small[data-price-id="${priceId}"]`);
                    if (toggle) {
                        if (e.target.checked) {
                            toggle.classList.add('active');
                        } else {
                            toggle.classList.remove('active');
                        }
                    }
                });
            });
        }
        
        if (selectAllCutting) {
            selectAllCutting.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('#cutting-types-tbody .custom-price-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    const priceId = cb.dataset.priceId;
                    const toggle = document.querySelector(`.toggle-switch-small[data-price-id="${priceId}"]`);
                    if (toggle) {
                        if (e.target.checked) {
                            toggle.classList.add('active');
                        } else {
                            toggle.classList.remove('active');
                        }
                    }
                });
            });
        }
        
        // IMPORTANT: Sync checkbox with toggle - use event delegation
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('custom-price-checkbox')) {
                const priceId = e.target.dataset.priceId;
                const toggle = document.querySelector(`.toggle-switch-small[data-price-id="${priceId}"]`);
                if (toggle) {
                    if (e.target.checked) {
                        toggle.classList.add('active');
                    } else {
                        toggle.classList.remove('active');
                    }
                    console.log(`📦 Checkbox ${priceId} changed to ${e.target.checked}, toggle synced`);
                }
            }
        });
    }

    toggleCustomPriceStatus(element) {
        element.classList.toggle('active');
        const priceId = element.dataset.priceId;
        const checkbox = document.querySelector(`.custom-price-checkbox[data-price-id="${priceId}"]`);
        
        if (checkbox) {
            checkbox.checked = element.classList.contains('active');
            console.log(`✅ Toggle price ${priceId}: checkbox.checked = ${checkbox.checked}`);
        } else {
            console.error(`❌ Checkbox not found for price ID: ${priceId}`);
        }
    }

    getCustomDesignPricesData() {
        const customPrices = [];
        
        document.querySelectorAll('.custom-price-checkbox:checked').forEach(checkbox => {
            const priceId = checkbox.dataset.priceId;
            const priceInput = document.querySelector(`.custom-price-input[data-price-id="${priceId}"]`);
            
            // Price is now required, use the input value or default price
            let customPrice = priceInput && priceInput.value ? parseFloat(priceInput.value) : null;
            
            // If no custom price entered, use the default price from data attribute
            if (!customPrice) {
                const row = priceInput.closest('tr');
                customPrice = parseFloat(row.dataset.defaultPrice) || 0;
            }
            
            customPrices.push({
                custom_design_price_id: priceId,
                custom_price: customPrice,
                is_active: true
            });
        });
        
        return customPrices;
    }

    // Populate selected custom design prices when editing product
    populateSelectedCustomDesignPrices(selectedPrices) {
        console.log('===== POPULATE CUSTOM DESIGN PRICES =====');
        console.log('📝 Product has', selectedPrices.length, 'custom design prices');
        console.log('Full data:', selectedPrices);
        
        if (!selectedPrices || selectedPrices.length === 0) {
            console.log('⚠️ No custom design prices to populate');
            return;
        }
        
        selectedPrices.forEach((priceItem, index) => {
            const priceId = priceItem.id;
            const customPrice = priceItem.pivot?.custom_price;
            const isActive = priceItem.pivot?.is_active !== false; // Default to true if not specified
            
            console.log(`Item ${index + 1}:`, {
                id: priceId,
                code: priceItem.code,
                name: priceItem.name,
                customPrice: customPrice,
                isActive: isActive,
                hasPivot: !!priceItem.pivot
            });
            
            // Find checkbox
            const checkbox = document.querySelector(`.custom-price-checkbox[data-price-id="${priceId}"]`);
            if (checkbox) {
                checkbox.checked = isActive;
                console.log(`  ✅ Checkbox ${priceId}: checked = ${isActive}`);
            } else {
                console.error(`  ❌ Checkbox not found for price ID: ${priceId}`);
            }
            
            // Find and populate custom price input with saved value
            const priceInput = document.querySelector(`.custom-price-input[data-price-id="${priceId}"]`);
            if (priceInput) {
                // Always show the saved custom_price (admin can edit this)
                if (customPrice) {
                    priceInput.value = customPrice;
                    console.log(`  ✅ Price input ${priceId}: value = ${customPrice}`);
                } else {
                    console.log(`  ℹ️ Price input ${priceId}: using default value`);
                }
            } else {
                console.error(`  ❌ Price input not found for price ID: ${priceId}`);
            }
            
            // Update toggle switch
            const toggle = document.querySelector(`.toggle-switch-small[data-price-id="${priceId}"]`);
            if (toggle) {
                if (isActive) {
                    toggle.classList.add('active');
                    console.log(`  ✅ Toggle ${priceId}: active`);
                } else {
                    toggle.classList.remove('active');
                    console.log(`  ⚪ Toggle ${priceId}: inactive`);
                }
            } else {
                console.error(`  ❌ Toggle not found for price ID: ${priceId}`);
            }
        });
        
        console.log('✅ Custom design prices population completed');
        console.log('=========================================');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.modernAddProductManager = new ModernAddProductManager();
});
