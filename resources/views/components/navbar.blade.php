<!-- Navbar Component -->
@vite([
    'resources/css/components/navbar.css', 
    'resources/css/components/notification-dropdown.css',
    'resources/js/components/navbar.js',
    'resources/js/components/notification-dropdown.js'
])

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $currentRoute = Route::currentRouteName();
    $currentCategory = $currentRoute === 'catalog' ? Route::current()->parameter('category') : null;
    $currentUser = auth()->user() ?? auth('admin')->user();
    $userAvatar = null; // Force using icon across all pages
    $isAdmin = auth('admin')->check();
@endphp

<div class="navbar-wrapper">

    <header class="main-navbar">
        <div class="main-navbar-inner">
            <a href="{{ route('home') }}" class="brand" aria-label="LGI Store">
                <div class="brand-logo">
                    <img src="{{ asset('images/logo-lgi-Photoroom.png') }}" alt="LGI Store Logo" class="brand-logo-img">
                </div>
                <div class="brand-text">
                    <span class="brand-name">LGI STORE</span>
                    <span class="brand-tagline">PEDULI KUALITAS, BUKAN KUANTITAS</span>
                </div>
            </a>

            <div class="navbar-center">
                <div class="search-area">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-bar" id="search-input" placeholder="Cari produk..." value="{{ request('search') }}">
                </div>
                <div class="category-strip">
                    <nav class="category-nav" aria-label="Kategori Produk">
                        <a href="{{ route('all-products') }}" class="nav-link {{ $currentRoute === 'all-products' ? 'active' : '' }}">Semua Produk</a>
                        <a href="{{ route('catalog', 'jersey') }}" class="nav-link {{ $currentCategory === 'jersey' ? 'active' : '' }}">Jersey</a>
                        <a href="{{ route('catalog', 'topi') }}" class="nav-link {{ $currentCategory === 'topi' ? 'active' : '' }}">Topi</a>
                        <a href="{{ route('catalog', 'kaos') }}" class="nav-link {{ $currentCategory === 'kaos' ? 'active' : '' }}">Kaos</a>
                        <a href="{{ route('catalog', 'polo') }}" class="nav-link {{ $currentCategory === 'polo' ? 'active' : '' }}">Polo</a>
                        <a href="{{ route('catalog', 'celana') }}" class="nav-link {{ $currentCategory === 'celana' ? 'active' : '' }}">Celana</a>
                        <a href="{{ route('catalog', 'jaket') }}" class="nav-link {{ $currentCategory === 'jaket' ? 'active' : '' }}">Jaket</a>
                        <a href="{{ route('catalog', 'lainnya') }}" class="nav-link {{ $currentCategory === 'lainnya' ? 'active' : '' }}">Lainnya</a>
                        <span class="nav-separator">|</span>
                        <a href="{{ route('about') }}" class="nav-link {{ $currentRoute === 'about' ? 'active' : '' }}">Tentang kami</a>
                    </nav>
                </div>
            </div>

            <div class="header-actions">
                <!-- Notification Bell -->
                @if(auth('web')->check() || auth('admin')->check())
                    <div class="notification-wrapper" data-user-type="{{ auth('admin')->check() ? 'admin' : 'customer' }}">
                        <a href="#" aria-label="Notifikasi" class="action-button notification-link" id="notification-bell">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
                        </a>
                        
                        <!-- Notification Dropdown -->
                        <div class="notification-dropdown" id="notification-dropdown" style="display: none;">
                            <div class="notification-dropdown__header">
                                <h3>Notifikasi</h3>
                                <button class="mark-all-read-btn" id="mark-all-read" style="display: none;">
                                    Tandai Semua Dibaca
                                </button>
                            </div>
                            
                            <div class="notification-dropdown__body" id="notification-list">
                                <div class="notification-loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Memuat notifikasi...</p>
                                </div>
                            </div>
                            
                            <div class="notification-dropdown__footer">
                                @if(auth('admin')->check())
                                    <a href="{{ route('admin.notifications.index') }}">Lihat Semua Notifikasi</a>
                                @else
                                    <a href="{{ route('notifikasi') }}">Lihat Semua Notifikasi</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <a href="#" aria-label="Notifikasi" class="action-button notification-link">
                        <i class="fas fa-bell"></i>
                    </a>
                @endif

                @if(!$isAdmin && auth()->check())
                    <a href="{{ route('keranjang') }}" aria-label="Buka Keranjang" class="action-button">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge">20</span>
                    </a>
                @endif

                <i id="profile-icon" class="fas fa-user-circle profile-icon-btn" aria-label="Profil"></i>
            </div>
        </div>
    </header>

    @if($currentUser)
        <div class="profile-popup" id="profile-popup" data-auth="true">
            <div class="profile-popup-content">
                <div class="profile-header">
                    <div class="profile-avatar" aria-hidden="true"><i class="fas fa-user"></i></div>
                    <div class="profile-info">
                        <span class="profile-name">{{ $currentUser->name }}</span>
                        <span class="profile-email">{{ $currentUser->email }}</span>
                    </div>
                </div>
                <div class="profile-menu">
                    @if($isAdmin)
                        <a href="{{ route('admin.dashboard') }}" class="profile-menu-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard Admin</span>
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}" class="profile-logout-form">
                            @csrf
                            <button type="submit">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('dashboard') }}" class="profile-menu-item">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('profile') }}" class="profile-menu-item">
                            <i class="fas fa-user"></i>
                            <span>Profil Saya</span>
                        </a>
                        <a href="{{ route('order-list') }}" class="profile-menu-item">
                            <i class="fas fa-box"></i>
                            <span>Pesanan Saya</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="profile-logout-form">
                            @csrf
                            <button type="submit">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>