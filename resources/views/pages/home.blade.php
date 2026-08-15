<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Beranda - LGI Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @vite(['resources/css/components/product-card.css', 'resources/css/guest/home.css', 'resources/css/components/footer.css', 'resources/js/guest/home.js', 'resources/js/guest/product-slider.js', 'resources/js/guest/product-card-carousel.js'])
</head>
<body>
    <!-- Header -->
    <x-navbar />



    <!-- Hero Section -->
    <section class="hero hero-elegant">
        <div class="hero-background">
            <img src="{{ asset('images/hero-products.png') }}" alt="Koleksi Produk Fashion" class="hero-bg-img">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content-elegant">
            <span class="hero-badge">✨ Custom Design Available</span>
            <h1 class="hero-title">Ekspresikan <span class="highlight">Gayamu</span></h1>
            <div class="hero-cta">
                <a href="{{ route('login') }}" class="btn-primary-hero">
                    <span>Mulai Belanja</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="{{ url('/all-products') }}" class="btn-secondary-hero">
                    <span>Lihat Koleksi</span>
                </a>
            </div>
            <div class="hero-features">
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Custom Design</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Kualitas Premium</span>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="new-arrivals">
        <h2 class="section-title">NEW ARRIVALS</h2>
        <div class="product-container">
            <button class="slider-nav slider-prev" data-slider="arrivals">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav slider-next" data-slider="arrivals">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="product-slider" id="arrivals-slider">
                <div class="product-grid slider-track">
                @forelse($newArrivals as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="no-products">
                        <p>Belum ada produk baru.</p>
                    </div>
                @endforelse
                </div>
            </div>
            <button class="view-all-btn">View All</button>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section">
        <h2 class="section-title">TOP SELLING</h2>
        <div class="product-container">
            <button class="slider-nav slider-prev" data-slider="selling">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav slider-next" data-slider="selling">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="product-slider" id="selling-slider">
                <div class="product-grid slider-track">
                @forelse($topSelling as $product)
                    <x-product-card :product="$product" :showRibbon="false" />
                @empty
                    <div class="no-products">
                        <p>Belum ada produk best seller.</p>
                    </div>
                @endforelse
                </div>
            </div>
        </div>
        <button class="view-all-btn top-selling-view-all">View All</button>
    </section>

    

    <!-- Footer Component -->
    <x-guest-footer />

    <!-- Unified Chatbot Popup Component - Only show for logged in customers -->
    @auth('web')
        @if(!auth()->guard('admin')->check())
            <x-unified-chatbot-popup />
        @endif
    @endauth
</body>
</html>