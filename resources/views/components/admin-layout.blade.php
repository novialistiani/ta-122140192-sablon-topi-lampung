<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{ $title ?? 'Admin Dashboard' }} - LGI STORE</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    @vite([
        'resources/css/admin/dashboard.css',
        'resources/css/components/notification-dropdown.css',
        'resources/js/components/notification-dropdown.js',
        'resources/js/admin/layout.js'
    ])
    <style>
        /* Admin Dropdown Styles */
        .admin-dropdown {
            position: relative;
            display: inline-block;
            margin-left: 15px;
        }

        .admin-dropdown__btn {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .admin-dropdown__btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .admin-avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .admin-avatar-placeholder i {
            font-size: 16px;
            color: #fff;
        }

        .admin-info {
            display: flex;
            flex-direction: column;
            text-align: left;
            margin-right: 8px;
        }

        .admin-role {
            font-size: 11px;
            opacity: 0.8;
            line-height: 1.2;
        }

        .admin-name {
            font-size: 13px;
            font-weight: 500;
            line-height: 1.2;
            margin-top: 2px;
        }

        .admin-dropdown__icon {
            font-size: 12px;
            margin-left: 5px;
            transition: transform 0.3s ease;
        }

        .admin-dropdown__menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            z-index: 1000;
            display: none;
            overflow: hidden;
            margin-top: 5px;
        }

        .admin-dropdown__menu.show {
            display: block;
        }

        .admin-dropdown__form {
            width: 100%;
        }

        .admin-dropdown__item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 10px 15px;
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
            color: #333;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .admin-dropdown__item:hover {
            background-color: #f5f5f5;
        }

        .admin-dropdown__item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .admin-dropdown__item--danger {
            color: #ef4444;
        }

        .admin-dropdown__item--danger:hover {
            background-color: #fee2e2;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" aria-label="Toggle Sidebar" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>

    <!-- TOP NAVBAR (Above everything) -->
    <div class="top-navbar">
        <div class="top-navbar__left">
            <div class="top-navbar__logo-container">
                <div class="top-navbar__logo">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="top-navbar__brand">
                    <h1 class="top-navbar__title">LGI STORE</h1>
                    <p class="top-navbar__subtitle">Produk Eksklusif Kaos Berkualitas</p>
                </div>
            </div>
        </div>
        <div class="top-navbar__right">
            <!-- Notification Bell for Admin -->
            <div class="notification-wrapper" data-user-type="admin" style="margin-right: 20px; display: inline-flex; align-items: center; position: relative; z-index: 1002; pointer-events: auto;">
                <a href="#" aria-label="Notifikasi" class="action-button notification-link" id="notification-bell" onclick="event.preventDefault(); event.stopPropagation(); toggleNotificationDropdown();" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); padding: 10px 12px; border-radius: 8px; position: relative; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; transition: all 0.3s ease; pointer-events: auto; z-index: 1002;">
                    <i class="fas fa-bell" style="font-size: 18px; color: #fff;"></i>
                    <span class="notification-badge" id="notification-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 11px; padding: 2px 6px; border-radius: 10px; font-weight: bold; min-width: 18px; text-align: center;">0</span>
                </a>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notification-dropdown" style="display: none; z-index: 1002; pointer-events: auto;">
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
                        <a href="{{ route('admin.notifications.index') }}">Lihat Semua Notifikasi</a>
                    </div>
                </div>
            </div>
            
            <div class="admin-dropdown" style="position: relative; z-index: 1002; pointer-events: auto;">
                <button class="admin-dropdown__btn" onclick="event.stopPropagation(); toggleAdminDropdown();" style="pointer-events: auto;">
                    @if(auth('admin')->user()->avatar)
                        <img src="{{ asset('storage/' . auth('admin')->user()->avatar) }}" alt="Avatar" class="admin-avatar">
                    @else
                        <div class="admin-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <div class="admin-info">
                        <span class="admin-role">{{ auth('admin')->user()->role_name }}</span>
                        <span class="admin-name">{{ auth('admin')->user()->name }}</span>
                    </div>
                    <i class="fas fa-chevron-down admin-dropdown__icon"></i>
                </button>
                <div class="admin-dropdown__menu" id="adminDropdownMenu">
                    <a href="{{ route('admin.profile') }}" class="admin-dropdown__item">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="admin-dropdown__form">
                        @csrf
                        <button type="submit" class="admin-dropdown__item admin-dropdown__item--danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar__header">
                <!-- Logo moved to top navbar -->
            </div>

            <ul class="sidebar__menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar__link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.order-list') }}" class="sidebar__link {{ request()->routeIs('admin.order-list*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Order List</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('chatbot.index') }}" class="sidebar__link {{ request()->routeIs('chatbot.*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span>Chatbot</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.management-users') }}" class="sidebar__link {{ request()->routeIs('admin.management-users*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.analytics') }}" class="sidebar__link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics Reports</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.history') }}" class="sidebar__link {{ request()->routeIs('admin.history*') ? 'active' : '' }}">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.finance.index') }}" class="sidebar__link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet"></i>
                        <span>Finance & Wallet</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.all-products') }}" class="sidebar__link {{ request()->routeIs('admin.all-products*') ? 'active' : '' }}">
                        <i class="fas fa-boxes"></i>
                        <span>All Products</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.management-product') }}" class="sidebar__link {{ request()->routeIs('admin.management-product*') ? 'active' : '' }}">
                        <i class="fas fa-box-open"></i>
                        <span>Product Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.custom-design-prices') }}" class="sidebar__link {{ request()->routeIs('admin.custom-design-prices*') ? 'active' : '' }}">
                        <i class="fas fa-paint-brush"></i>
                        <span>Custom Design</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar Footer -->
            <div class="sidebar__footer">
                <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="page-header__left">
                    <!-- Breadcrumb -->
                    <nav class="breadcrumb">
                        <a href="{{ route('home') }}" class="breadcrumb__item breadcrumb__item--link">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                        <i class="fas fa-chevron-right breadcrumb__separator"></i>
                        <span class="breadcrumb__item breadcrumb__item--active">{{ $title ?? 'Dashboard' }}</span>
                    </nav>
                </div>
                <div class="page-header__right">
                    <div class="page-header__date-range">
                        <i class="fas fa-calendar"></i>
                        <span>{{ now()->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- PAGE CONTENT -->
            <div class="page-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    @vite('resources/js/admin/layout.js')
    {{-- notifications.js removed - using notification-dropdown.js instead --}}
    @stack('scripts')
</body>
</html>
