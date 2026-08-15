<x-admin-layout title="All Products">
    @push('styles')
        @vite(['resources/css/admin/all-products.css'])
    @endpush

<div class="all-products-container">
    <!-- Filters & Search -->
    <div class="filters-section">
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-input" placeholder="Cari produk..." />
            </div>
        </div>
        
        <div class="filters-row">
            <div class="filter-group">
                <label for="category-filter">Kategori</label>
                <select id="category-filter" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <option value="topi">Topi</option>
                    <option value="kaos">Kaos</option>
                    <option value="polo">Polo</option>
                    <option value="jaket">Jaket</option>
                    <option value="jersey">Jersey</option>
                    <option value="celana">Celana</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status-filter">Status</label>
                <select id="status-filter" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="draft">Draft</option>
                    <option value="out_of_stock">Habis</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort-filter">Urutkan</label>
                <select id="sort-filter" class="filter-select">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="name_asc">Nama A-Z</option>
                    <option value="name_desc">Nama Z-A</option>
                    <option value="price_asc">Harga Terendah</option>
                    <option value="price_desc">Harga Tertinggi</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="products-section">
        <div class="products-header">
            <div class="results-info">
                <span class="results-count" id="results-count">0 produk ditemukan</span>
            </div>
            <div class="header-actions">
                <div class="view-options">
                    <button class="view-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <button class="btn btn-primary" id="add-product-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Produk
                </button>
            </div>
        </div>

        <div class="products-grid" id="products-grid">
            <!-- Loading state -->
            <div class="loading-state" id="loading-state">
                <div class="spinner"></div>
                <p>Memuat produk...</p>
            </div>
        </div>

        <!-- Empty state -->
        <div class="empty-state" id="empty-state" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <h3>Tidak ada produk ditemukan</h3>
            <p>Coba ubah filter pencarian atau tambah produk baru</p>
            <button class="btn btn-primary" id="add-first-product-btn">
                <i class="fas fa-plus"></i>
                Tambah Produk Pertama
            </button>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" id="pagination-container" style="display: none;">
            <!-- Pagination will be rendered here -->
        </div>
    </div>
</div>

<!-- Product Card Template -->
<template id="product-card-template">
    <div class="product-card" data-id="">
        <div class="product-image-container">
            <img src="" alt="" class="main-image">
            <div class="product-overlay">
                <button class="btn-icon view-btn" title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-icon edit-btn" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon delete-btn" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="product-badges">
                <span class="badge status-badge"></span>
                <span class="badge stock-badge"></span>
                <span class="badge custom-badge" style="display:none">CUSTOM</span>
            </div>
        </div>
        <div class="product-info">
            <h3 class="product-name"></h3>
            <p class="product-category"></p>
            <div class="product-price">
                <span class="current-price"></span>
                <span class="original-price"></span>
            </div>
            <div class="product-stock">
                <span class="stock-label">Stok:</span>
                <span class="stock-value"></span>
            </div>
        </div>
    </div>
</template>

<!-- Product List Template -->
<template id="product-list-template">
    <div class="product-list-item" data-id="">
        <div class="list-image">
            <img src="" alt="" class="main-image">
            <div class="product-badges">
                <span class="badge custom-badge" style="display:none">CUSTOM</span>
            </div>
        </div>
        <div class="list-content">
            <div class="list-main">
                <h3 class="product-name"></h3>
                <p class="product-category"></p>
                <div class="product-price">
                    <span class="current-price"></span>
                    <span class="original-price"></span>
                </div>
            </div>
            <div class="list-details">
                <div class="detail-item">
                    <span class="detail-label">Stok:</span>
                    <span class="stock-value"></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="status-badge"></span>
                </div>
            </div>
            <div class="list-actions">
                <button class="btn-icon view-btn" title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-icon edit-btn" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon delete-btn" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
    @vite(['resources/js/admin/all-products.js'])
@endpush
</x-admin-layout>