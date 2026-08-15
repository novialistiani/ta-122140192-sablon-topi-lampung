@props(['title' => 'Dashboard'])

<!-- Header -->
<header class="bg-white p-4 shadow flex justify-between items-center">
    <div class="flex items-center text-sm">
        <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 transition" aria-label="Kembali ke Homepage">Beranda</a>
        <span class="mx-2 text-gray-400"><i class="fas fa-chevron-right text-xs"></i></span>
        <span class="text-gray-700 font-medium">{{ $title }}</span>
    </div>
    <div class="flex items-center">
        <div class="flex items-center">
            <!-- Notification Bell -->
            <button class="notification-bell relative p-2 hover:bg-gray-100 rounded-full transition-colors" aria-label="Notifikasi">
                <i class="fas fa-bell text-gray-600 text-xl"></i>
                <!-- Notification Badge -->
                <span class="notification-badge absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden">0</span>
            </button>
        </div>
    </div>
</header>