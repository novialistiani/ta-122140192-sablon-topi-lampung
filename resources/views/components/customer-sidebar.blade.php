@props(['active' => 'dashboard'])

<!-- Mobile Topbar Toggle (hanya tampil di layar kecil) -->
<div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-navy-900 text-white flex items-center px-4 z-40">
    <button onclick="toggleCustomerSidebar()" class="p-2 -ml-2 rounded-lg hover:bg-navy-800">
        <span class="material-icons">menu</span>
    </button>
    <div class="flex items-center gap-2 ml-2">
        <img src="{{ asset('images/logo-lgi-Photoroom.png') }}" alt="LGI Store Logo" class="h-8">
        <span class="font-bold text-sm">LGI STORE</span>
    </div>
</div>
<!-- Spacer supaya konten tidak ketutup topbar mobile -->
<div class="lg:hidden h-16"></div>

<!-- Overlay (hanya muncul saat sidebar mobile terbuka) -->
<div id="customer-sidebar-overlay"
     onclick="toggleCustomerSidebar()"
     class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

<!-- Sidebar -->
<div id="customer-sidebar"
     class="w-64 bg-navy-900 text-white p-6 flex flex-col
            fixed inset-y-0 left-0 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out
            lg:translate-x-0 lg:static lg:sticky lg:top-0 lg:h-screen">

    <button onclick="toggleCustomerSidebar()" class="lg:hidden self-end mb-4 p-1 text-gray-300 hover:text-white">
        <span class="material-icons">close</span>
    </button>

    <div class="mb-8 hidden lg:block">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo-lgi-Photoroom.png') }}" alt="LGI Store Logo" class="h-10">
            <div>
                <div class="font-bold text-sm">LGI STORE</div>
                <div class="text-xs text-gray-300">PEDULI KUALITAS</div>
            </div>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto">
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

<script>
    function toggleCustomerSidebar() {
        document.getElementById('customer-sidebar').classList.toggle('-translate-x-full');
        document.getElementById('customer-sidebar-overlay').classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }
</script>