@props(['title' => 'Dashboard', 'active' => 'dashboard'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - LGI Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite('resources/css/customer/shared.css')
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-navy-900 text-white p-6 flex flex-col sticky top-0 h-screen overflow-y-auto">
            <div class="mb-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-lgi-Photoroom.png') }}" alt="LGI Store Logo" class="h-10">
                    <div>
                        <div class="font-bold text-sm">LGI STORE</div>
                        <div class="text-xs text-gray-300">PEDULI KUALITAS</div>
                    </div>
                </a>
            </div>
            
            <nav class="flex-1 space-y-2">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center p-3 rounded-lg {{ $active === 'dashboard' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="material-icons mr-3">home</span>
                    Dashboard
                </a>
                <a href="{{ route('keranjang') }}" 
                   class="flex items-center p-3 rounded-lg {{ $active === 'keranjang' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="material-icons mr-3">shopping_cart</span>
                    Keranjang
                </a>
                <a href="{{ route('order-list') }}"
                   class="flex items-center p-3 rounded-lg {{ $active === 'order-list' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="material-icons mr-3">list_alt</span>
                    Daftar Pesanan
                </a>
                <a href="{{ route('custom-design') }}"
                   class="flex items-center p-3 rounded-lg {{ $active === 'custom-design' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="material-icons mr-3">palette</span>
                    Desain Kustom
                </a>
                <a href="{{ route('chatpage') }}"
                   class="flex items-center p-3 rounded-lg {{ $active === 'chatpage' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="material-icons mr-3">chat</span>
                    Chatbot
                </a>
                <a href="{{ route('notifikasi') }}"
                   class="flex items-center justify-between p-3 rounded-lg {{ $active === 'notifikasi' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="flex items-center">
                        <span class="material-icons mr-3">notifications</span>
                        Notifikasi
                    </span>
                    @php
                        $unreadCount = auth()->check() ? app(\App\Services\NotificationService::class)->getUnreadCount(auth()->id()) : 0;
                    @endphp
                    @if($unreadCount > 0)
                    <span class="sidebar-notification-badge text-xs font-semibold px-2 py-0.5 rounded-full {{ $active === 'notifikasi' ? 'bg-navy-900 text-yellow-300' : 'bg-red-500 text-white' }}">
                        {{ $unreadCount }}
                    </span>
                    @endif
                </a>
                <a href="{{ route('profile') }}"
                   class="flex items-center p-3 rounded-lg {{ $active === 'profile' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
                    <span class="material-icons mr-3">person</span>
                    Profil
                </a>
            </nav>

            <!-- Logout Button -->
            <div class="border-t border-navy-700 pt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center p-3 rounded-lg bg-yellow-400 text-navy-900 hover:bg-yellow-500 transition font-medium">
                        <span class="material-icons mr-3">logout</span>
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="p-4 flex justify-between items-center">
                    <div class="flex items-center text-sm">
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 transition">Beranda</a>
                        <span class="mx-2 text-gray-400"><i class="fas fa-chevron-right text-xs"></i></span>
                        <span class="text-gray-700 font-medium">{{ $title }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Notification Bell -->
                        <div class="notification-wrapper relative" data-user-type="customer">
                            <a href="#" class="relative p-2 hover:bg-gray-100 rounded-full transition-colors inline-flex" id="notification-bell" aria-label="Notifikasi">
                                <i class="fas fa-bell text-gray-600 text-xl"></i>
                                <span class="notification-badge absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center" id="notification-badge" style="display: none;">0</span>
                            </a>

                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50" id="notification-dropdown" style="display: none;">
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
                                    <a href="{{ route('notifikasi') }}">Lihat Semua Notifikasi</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative group">
                            <button class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="h-8 w-8 rounded-full header-avatar">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">
                                    <i class="fas fa-user-circle mr-2"></i> Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 rounded-b-lg">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 {{ $active === 'chatpage' ? 'overflow-hidden p-0' : 'overflow-auto p-2' }}">
                {{ $slot }}
            </div>
        </div>
    </div>

    @vite([
        'resources/css/components/notification-dropdown.css',
        'resources/js/components/notification-dropdown.js'
    ])
    @stack('scripts')
</body>
</html>
