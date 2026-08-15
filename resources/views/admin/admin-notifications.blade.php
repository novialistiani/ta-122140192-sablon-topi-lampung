<x-admin-layout title="Notifikasi Admin">
    @push('styles')
        {{-- Pastikan path ini sesuai dengan struktur proyek Anda --}}
        @vite(['resources/css/admin/admin-notifications.css'])
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @endpush

<div class="notification-container">
    {{-- Header Halaman --}}
    <header class="page-header">
        <h1 class="page-title">Pemberitahuan</h1>
        <div class="date-range-picker">
            <i class="far fa-calendar-alt"></i>
            <span>Oktober 16, 2025 - November 11, 2025</span>
        </div>
    </header>

    {{-- Kartu Daftar Notifikasi --}}
    <div class="card notification-card">
        
        <div class="card-header">
            <h2 class="card-title">Notifikasi Terbaru (3 Belum Dibaca)</h2>
            <button class="btn btn-subtle mark-all-read">Tandai Semua Sudah Dibaca</button>
        </div>

        <div class="card-body">
            <ul class="notification-list">
                
                {{-- Notifikasi 1: Pesanan Baru (Belum Dibaca) --}}
                <li class="notification-item unread">
                    <div class="notification-icon new-order"><i class="fas fa-shopping-bag"></i></div>
                    <div class="notification-content">
                        <span class="notification-title">Pesanan Baru dari **Hakiki** #23456 telah masuk.</span>
                        <span class="notification-time">5 menit yang lalu</span>
                    </div>
                    <div class="notification-actions">
                        <button class="btn btn-primary">Lihat Pesanan</button>
                    </div>
                </li>

                {{-- Notifikasi 2: Custom Desain Menunggu Persetujuan (Belum Dibaca) --}}
                <li class="notification-item unread">
                    <div class="notification-icon custom-design"><i class="fas fa-palette"></i></div>
                    <div class="notification-content">
                        <span class="notification-title">Desain Custom #CUST992 menunggu persetujuan Anda.</span>
                        <span class="notification-time">1 jam yang lalu</span>
                    </div>
                    <div class="notification-actions">
                        <button class="btn btn-primary">Tinjau Desain</button>
                    </div>
                </li>

                {{-- Notifikasi 3: Chat Baru (Belum Dibaca) --}}
                <li class="notification-item unread">
                    <div class="notification-icon new-chat"><i class="fas fa-comment-dots"></i></div>
                    <div class="notification-content">
                        <span class="notification-title">Pesan baru dari **Elsa Novi** masuk. (Tanggapi sekarang)</span>
                        <span class="notification-time">2 jam yang lalu</span>
                    </div>
                    <div class="notification-actions">
                        <button class="btn btn-primary">Balas Chat</button>
                    </div>
                </li>

                {{-- Notifikasi 4: Pesanan Dibatalkan (Sudah Dibaca) --}}
                <li class="notification-item">
                    <div class="notification-icon cancelled"><i class="fas fa-times-circle"></i></div>
                    <div class="notification-content">
                        <span class="notification-title">Pesanan #12345 telah dibatalkan oleh pelanggan.</span>
                        <span class="notification-time">Kemarin, 14:00</span>
                    </div>
                    <div class="notification-actions">
                        <button class="btn btn-secondary">Arsipkan</button>
                    </div>
                </li>

                {{-- Notifikasi 5: Sistem Error (Sudah Dibaca) --}}
                <li class="notification-item">
                    <div class="notification-icon system-error"><i class="fas fa-bug"></i></div>
                    <div class="notification-content">
                        <span class="notification-title">PERINGATAN: Koneksi database MySQL gagal (Lihat Log).</span>
                        <span class="notification-time">2 hari yang lalu</span>
                    </div>
                    <div class="notification-actions">
                        <button class="btn btn-secondary">Lihat Log</button>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</div>

</x-admin-layout>