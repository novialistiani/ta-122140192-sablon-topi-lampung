@props(['active' => 'dashboard'])

<!-- Sidebar -->
<div class="w-64 bg-navy-900 text-white p-6 flex flex-col sticky top-0 h-screen">
    <div class="mb-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo-lgi-Photoroom.png') }}" alt="LGI Store Logo" class="h-10">
            <div>
                <div class="font-bold text-sm">LGI STORE</div>
                <div class="text-xs text-gray-300">PEDULI KUALITAS</div>
            </div>
        </a>
    </div>
    
    <nav class="flex-1">
        <a href="{{ route('dashboard') }}" 
           class="flex items-center p-3 rounded-lg mb-2 {{ $active === 'dashboard' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="material-icons mr-3">home</span>
            Dashboard
        </a>
        <a href="{{ route('keranjang') }}" 
           class="flex items-center p-3 rounded-lg mb-2 {{ $active === 'keranjang' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="material-icons mr-3">shopping_cart</span>
            Keranjang
        </a>
        <a href="{{ route('order-list') }}"
           class="flex items-center p-3 rounded-lg mb-2 {{ $active === 'order-list' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="material-icons mr-3">list_alt</span>
            Daftar Pesanan
        </a>
        <a href="{{ route('custom-design') }}"
           class="flex items-center p-3 rounded-lg mb-2 {{ $active === 'custom-design' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="material-icons mr-3">palette</span>
            Desain Kustom
        </a>
        <a href="{{ route('chatpage') }}"
           class="flex items-center p-3 rounded-lg mb-2 {{ $active === 'chatpage' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="material-icons mr-3">chat</span>
            Chatbot
        </a>
        <a href="{{ route('notifikasi') }}"
           class="flex items-center justify-between p-3 rounded-lg mb-2 {{ $active === 'notifikasi' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="flex items-center">
                <span class="material-icons mr-3">notifications</span>
                Notifikasi
            </span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $active === 'notifikasi' ? 'bg-navy-900 text-yellow-300' : 'bg-yellow-400 text-navy-900' }}">
                0
            </span>
        </a>
        <a href="{{ route('profile') }}"
           class="flex items-center p-3 rounded-lg mb-2 {{ $active === 'profile' ? 'bg-yellow-400 text-navy-900' : 'text-white hover:bg-navy-800' }}">
            <span class="material-icons mr-3">person</span>
            Profil
        </a>
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="mt-auto">
        @csrf
        <button type="submit" class="w-full bg-yellow-400 text-navy-900 p-3 rounded-lg flex items-center justify-center font-semibold hover:bg-yellow-500 transition">
            <span class="material-icons mr-2">logout</span>
            Keluar
        </button>
    </form>
</div>