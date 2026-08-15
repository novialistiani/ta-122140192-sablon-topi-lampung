<x-admin-layout title="Product Management">
    @push('styles')
        @vite(['resources/css/admin/product-management.css'])
        @vite(['resources/css/admin/modern-add-product.css'])
    @endpush

<div class="product-management-container">
    <!-- Product Management Interface -->
    <div class="controls-section">
        <!-- Tabs -->
        <div class="tabs-container">
            <button class="tab-btn active" data-status="ALL">Semua</button>
            <button class="tab-btn" data-status="ACTIVE">Aktif</button>
            <button class="tab-btn" data-status="DRAFT">Draft</button>
            <button class="tab-btn" data-status="ARCHIVED">Arsip</button>
            <button class="tab-btn" data-status="READY">Ready</button>
            <button class="tab-btn" data-status="HABIS">Habis</button>
        </div>

        <!-- Search -->
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-input" placeholder="Cari nama produk / SKU..." />
        </div>

        <!-- Category Filter -->
        <select id="category-filter" class="filter-select">
            <option value="">Semua Kategori</option>
            <option value="kaos">Kaos</option>
            <option value="jaket">Jaket</option>
            <option value="polo">Polo</option>
            <option value="jersey">Jersey</option>
            <option value="celana">Celana</option>
            <option value="topi">Topi</option>
            <option value="lainnya">Lainnya</option>
        </select>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Produk</h3>
            <div class="header-actions">
                <button id="bulk-archive-btn" class="btn btn-warning" style="display: none;">
                    <i class="fas fa-archive"></i>
                    Arsipkan (<span id="selected-count">0</span>)
                </button>
                <button id="export-btn" class="btn btn-subtle">
                    <i class="fas fa-download"></i>
                    Export
                </button>
                <button id="add-product-btn" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Produk
                </button>
            </div>
            <span class="product-count" id="product-count">0 produk</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th class="checkbox-col">
                                <input type="checkbox" id="select-all" />
                            </th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th class="actions-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        <tr>
                            <td colspan="8" class="loading-state">
                                <div class="spinner"></div>
                                <p>Memuat produk...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" id="pagination-container" style="display: none;"></div>
        </div>
    </div>
</div>

<!-- Modern Drawer for Add/Edit Product (Odoo Style) -->
<div class="modern-drawer-overlay" id="modern-drawer-overlay"></div>
<div class="modern-drawer" id="modern-product-drawer">
    <div class="drawer-resize-handle" id="drawer-resize-handle"></div>
    <div class="modern-drawer-header">
        <h3 class="modern-drawer-title" id="modern-drawer-title">Tambah Produk</h3>
        <button class="modern-drawer-close" id="modern-drawer-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="modern-drawer-body">
        <form id="modern-product-form" enctype="multipart/form-data">
            <input type="hidden" id="modern-product-id" name="id">

            <!-- Top Grid -->
            <section class="form-section">
                <div class="form-grid-2">
                    <div class="field-group">
                        <label for="modern-product-name">Nama Produk <span class="required">*</span></label>
                        <input type="text" id="modern-product-name" name="name" required placeholder="Contoh: Blaster Fusion Mini Soccer" class="form-input">
                    </div>

                    <div class="field-group">
                        <label for="modern-product-category">Kategori <span class="required">*</span></label>
                        <select id="modern-product-category" name="category" required class="form-select">
                            <option value="">Pilih kategori</option>
                            <option value="topi">Topi</option>
                            <option value="kaos">Kaos</option>
                            <option value="polo">Polo</option>
                            <option value="jaket">Jaket</option>
                            <option value="jersey">Jersey</option>
                            <option value="celana">Celana</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="field-group" id="subcategory-select-group">
                        <label for="modern-product-subcategory">Sub Kategori</label>
                        <select id="modern-product-subcategory" name="subcategory" class="form-select">
                            <option value="">Pilih kategori terlebih dahulu</option>
                        </select>
                    </div>

                    <div class="field-group" id="subcategory-custom-group" style="display: none;">
                        <label for="modern-product-subcategory-lainnya">Sub Kategori (Lainnya)</label>
                        
                        <!-- Input for subcategory with autocomplete -->
                        <div style="position: relative;">
                            <input 
                                type="text" 
                                id="modern-product-subcategory-lainnya" 
                                name="subcategory"
                                placeholder="Tambah sub kategori..." 
                                class="form-input" 
                                autocomplete="off"
                                style="width: 100%;">
                            
                            <!-- Hidden input to store the slug value -->
                            <input type="hidden" id="subcategory-slug" name="subcategory_slug">
                            
                            <!-- Dropdown list for existing subcategories -->
                            <div id="subcategory-dropdown" class="subcategory-dropdown" style="display: none;">
                                <div id="subcategory-list" class="subcategory-list"></div>
                                <button type="button" id="add-new-subcategory-btn" class="subcategory-add-btn">
                                    <i class="fas fa-plus-circle"></i> Tambah Sub Kategori Baru
                                </button>
                            </div>
                        </div>
                        
                        <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">
                            Ketik untuk mencari atau klik untuk melihat daftar sub kategori yang tersedia
                        </small>
                    </div>
                </div>
            </section>

            <!-- Hidden fields for price & stock (auto-calculated from variants) -->
            <input type="hidden" id="modern-product-price" name="price" value="0">
            <input type="hidden" id="modern-product-stock" name="stock" value="0">

            <!-- Customize Color & Size -->
            <section class="form-section">
                <div class="form-grid-2">
                    <!-- COLOR CARD -->
                    <div class="custom-card">
                        <h3 class="card-label">CUSTOMIZE COLOR</h3>
                        <div class="color-stack">
                            <div class="saved-colors-container" id="modern-saved-colors-container"></div>
                            <div class="color-action-row">
                                <div class="color-picker">
                                    <button type="button" id="modern-color-swatch" class="color-swatch" style="background-color: #6b7280;"></button>
                                    <span class="color-code" id="modern-color-code">#6B7280</span>
                                    <input type="color" id="modern-color-code-input" value="#6b7280" class="sr-only">
                                </div>
                                <button type="button" id="modern-add-color-btn" class="btn-dark">SAVE</button>
                            </div>
                        </div>
                        <input type="hidden" name="colors" id="modern-colors-input">
                    </div>

                    <!-- SIZE CARD -->
                    <div class="custom-card">
                        <h3 class="card-label">CUSTOMIZE SIZE</h3>
                        <div class="size-chip-group" id="modern-sizes-group">
                            <button type="button" class="size-chip" data-value="XS">XS</button>
                            <button type="button" class="size-chip" data-value="S">S</button>
                            <button type="button" class="size-chip" data-value="M">M</button>
                            <button type="button" class="size-chip" data-value="L">L</button>
                            <button type="button" class="size-chip" data-value="XL">XL</button>
                            <button type="button" class="size-chip" data-value="XXL">XXL</button>
                        </div>
                        <input type="hidden" name="sizes" id="modern-sizes-input">
                    </div>
                </div>
            </section>

            <!-- Variants Table -->
            <section class="form-section">
                <div class="table-wrapper">
                    <div class="border-b border-zinc-200 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="card-label">TABEL VARIAN</h3>
                            <span style="font-size: 0.75rem; color: #71717a;">
                                💡 Harga Jual = harga yang dibayar customer | Harga Coret = harga sebelum diskon (opsional)
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-[800px] w-full">
                            <thead>
                                <tr>
                                    <th>VARIAN</th>
                                    <th>HARGA JUAL</th>
                                    <th>HARGA CORET</th>
                                    <th>STOK</th>
                                    <th>GAMBAR</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="modern-variants-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Description -->
            <section class="form-section">
                <div class="custom-card">
                    <label class="field-group">
                        <span style="font-size: 0.875rem; font-weight: 600; color: #18181b; margin-bottom: 0.5rem;">Deskripsi</span>
                        <textarea id="modern-product-description" name="description" rows="4" placeholder="Tulis deskripsi produk..." class="form-textarea"></textarea>
                    </label>
                </div>
            </section>

            <!-- Footer controls -->
            <section class="form-section">
                <div class="footer-controls-grid">
                    <!-- Status Toggle -->
                    <div class="control-item">
                        <label class="control-label">Status Produk</label>
                        <div class="toggle-wrapper">
                            <label class="modern-switch">
                                <input type="checkbox" id="modern-product-status" name="is_active" checked>
                                <span class="modern-slider"></span>
                            </label>
                            <span class="status-text" id="status-text">Aktif</span>
                        </div>
                    </div>

                    <!-- Custom Design Toggle -->
                    <div class="control-item">
                        <label class="control-label">Custom Design</label>
                        <div class="toggle-wrapper">
                            <label class="modern-switch">
                                <input type="checkbox" id="modern-custom-design-allowed" name="custom_design_allowed">
                                <span class="modern-slider"></span>
                            </label>
                            <span class="status-text" id="custom-design-text">Tidak Aktif</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Custom Design Price Configuration (Hidden by default) -->
            <section class="form-section" id="custom-design-price-section" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-palette"></i>
                    Konfigurasi Harga Custom Design
                </h3>
                
                <div class="custom-design-prices-container">
                    <div class="loading-state" id="custom-prices-loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading custom design options...
                    </div>
                    
                    <!-- Upload Sections Table -->
                    <div class="price-table-wrapper" id="upload-sections-table" style="display: none;">
                        <h4 class="table-subtitle">Upload Bagian</h4>
                        <table class="custom-price-table">
                            <thead>
                                <tr>
                                    <th width="60">
                                        <input type="checkbox" id="select-all-uploads" title="Pilih Semua">
                                    </th>
                                    <th>Kode</th>
                                    <th>Nama Bagian</th>
                                    <th width="200">Harga (Rp)</th>
                                    <th width="80">Status</th>
                                </tr>
                            </thead>
                            <tbody id="upload-sections-tbody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Cutting Types Table -->
                    <div class="price-table-wrapper" id="cutting-types-table" style="display: none; margin-top: 24px;">
                        <h4 class="table-subtitle">Jenis Cutting</h4>
                        <table class="custom-price-table">
                            <thead>
                                <tr>
                                    <th width="60">
                                        <input type="checkbox" id="select-all-cutting" title="Pilih Semua">
                                    </th>
                                    <th>Kode</th>
                                    <th>Jenis Cutting</th>
                                    <th width="200">Harga (Rp)</th>
                                    <th width="80">Status</th>
                                </tr>
                            </thead>
                            <tbody id="cutting-types-tbody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </form>
    </div>

    <div class="modern-drawer-footer">
        <button type="button" class="btn btn-subtle" id="modern-cancel-btn">Batal</button>
        <button type="submit" form="modern-product-form" class="btn-dark" id="modern-save-btn">
            <i class="fas fa-save"></i>
            Simpan Produk
        </button>
    </div>
</div>

    @push('styles')
    <style>
        /* Custom Design Price Configuration Styles */
        .custom-design-prices-container {
            margin-top: 16px;
        }

        .loading-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .price-table-wrapper {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
        }

        .table-subtitle {
            font-size: 16px;
            font-weight: 600;
            color: #0a1d37;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-price-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .custom-price-table thead {
            background: #0a1d37;
            color: white;
        }

        .custom-price-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        .custom-price-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-price-table tbody tr:hover {
            background: #f9fafb;
        }

        .custom-price-table tbody tr:last-child td {
            border-bottom: none;
        }

        .code-badge-small {
            display: inline-block;
            background: #fbbf24;
            color: #0a1d37;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .price-input-small {
            width: 130px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
        }

        .price-input-small:focus {
            outline: none;
            border-color: #fbbf24;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
        }

        .price-input-small:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .toggle-switch-small {
            position: relative;
            width: 40px;
            height: 20px;
            background: #d1d5db;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-block;
        }

        .toggle-switch-small.active {
            background: #10b981;
        }

        .toggle-switch-small::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-switch-small.active::after {
            transform: translateX(20px);
        }

        /* Subcategory Dropdown Styles */
        .subcategory-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-top: 4px;
            z-index: 1000;
            max-height: 300px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .subcategory-list {
            flex: 1;
            overflow-y: auto;
            padding: 4px;
        }

        .subcategory-item {
            padding: 10px 12px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            color: #374151;
        }

        .subcategory-item:hover {
            background: #f3f4f6;
        }

        .subcategory-item.selected {
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 500;
        }

        .subcategory-item i {
            color: #9ca3af;
            font-size: 12px;
        }

        .subcategory-add-btn {
            width: 100%;
            padding: 12px;
            background: #f9fafb;
            border: none;
            border-top: 1px solid #e5e7eb;
            color: #2563eb;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .subcategory-add-btn:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .subcategory-add-btn i {
            font-size: 16px;
        }

        /* Empty state */
        .subcategory-empty {
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }
    </style>
    @endpush

    @push('scripts')
        @vite(['resources/js/admin/product-management.js'])
        @vite(['resources/js/admin/modern-add-product.js'])
    @endpush
</x-admin-layout>
