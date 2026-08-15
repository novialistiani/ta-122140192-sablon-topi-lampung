<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Semua Produk - LGI STORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/guest/catalog.css', 'resources/css/guest/catalog-inline.css', 'resources/css/components/footer.css', 'resources/css/components/product-card.css', 'resources/js/guest/catalog.js', 'resources/js/guest/product-card-carousel.js'])
</head>
<body>
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
                        <!-- Quick Filters First -->
                        <div class="filter-section">
                            <div class="filter-checkbox-list">
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="promo" value="1">
                                    <span>Dengan diskon</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="ready" value="1">
                                    <span>Ready stok</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="custom" value="1">
                                    <span>Kustomisasi</span>
                                </label>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="filter-section">
                            <div class="filter-title-row">
                                <span class="filter-title">Kategori</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="filter-checkbox-list">
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="categories[]" value="topi">
                                    <span>Topi</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="categories[]" value="celana">
                                    <span>Celana</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="categories[]" value="polo">
                                    <span>Polo</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="categories[]" value="jaket">
                                    <span>Jaket</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="categories[]" value="jersey">
                                    <span>Jersey</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="categories[]" value="kaos">
                                    <span>Kaos</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Range -->
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
                    </div>
                </aside>

                <main class="products-section">
                    <div class="products-header-inline">
                        <div class="header-title-section">
                            <h1 class="page-title-inline">Semua Produk</h1>
                            <span class="products-count-inline" id="products-count">Menampilkan 1-{{ $products->count() }} dari {{ $products->total() }} Produk</span>
                        </div>
                        <div class="header-sort-section">
                            <label for="sort-select">Urut berdasarkan:</label>
                            <select class="sort-select" id="sort-select">
                                <option value="most_popular">Paling Populer</option>
                                <option value="newest">Terbaru</option>
                                <option value="price_asc">Harga Terendah</option>
                                <option value="price_desc">Harga Tertinggi</option>
                            </select>
                        </div>
                    </div>

                    <div class="products-grid" id="products-grid">
                        @forelse($products as $product)
                            <x-product-card :product="$product" />
                        @empty
                            <div class="no-products">
                                <i class="fas fa-inbox"></i>
                                <p>Tidak ada produk ditemukan</p>
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

    <!-- Unified Chatbot Popup Component - Only show for logged in customers -->
    @auth('web')
        @if(!auth()->guard('admin')->check())
            <x-unified-chatbot-popup />
        @endif
    @endauth
</body>
</html>