<x-admin-layout title="Order Status">
    @push('styles')
        {{-- Pastikan path ini sesuai dengan struktur proyek Anda --}}
        @vite(['resources/css/admin/order-status.css'])
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @endpush

<div class="order-detail-container">
    {{-- Header Halaman dengan Breadcrumb --}}
    <header class="page-header">
        <h1 class="page-title">
            <a href="#">Home</a> 
            <span class="breadcrumb-separator">></span> 
            <a href="#">Order List</a> 
            <span class="breadcrumb-separator">></span> 
            Detail
            <span class="breadcrumb-separator">></span>
            <span class="breadcrumb-active">Status</span>
        </h1>
        <div class="date-range-picker">
            <i class="far fa-calendar-alt"></i>
            <span>Oktober 16, 2025 - November 11, 2025</span>
        </div>
    </header>

    {{-- Pelacak Status (BARU) --}}
    <div class="card status-tracker-card">
        <div class="status-tracker">
            <div class="status-step active">
                <div class="status-icon-wrapper">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="status-label">
                    <span class="status-title">Disetujui</span>
                    <span class="status-date">Senin, 11/15/2025 09:27</span>
                </div>
            </div>
            
            <div class="status-connector active"></div>

            <div class="status-step active">
                <div class="status-icon-wrapper">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="status-label">
                    <span class="status-title">Diproses</span>
                    <span class="status-date">Rabu, 11/17/2025 08:22</span>
                </div>
            </div>

            <div class="status-connector"></div>

            <div class="status-step">
                <div class="status-icon-wrapper">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="status-label">
                    <span class="status-title">Selesai</span>
                </div>
            </div>
        </div>
    </div>


    {{-- Kartu Detail Produk Pesanan --}}
    <div class="card product-detail-card">
        <div class="card-header">
            <div class="header-column">Detail</div>
            <div class="header-column">Nilai</div>
            <div class="header-column">Catatan dan Aksi</div>
        </div>
        <div class="card-body">
            <div class="product-info">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nama Produk</span>
                        <span class="info-value">ONE LIFE GRAPHIC T-SHIRT</span>
                    </div>
                     <div class="info-item">
                        <span class="info-label">Warna</span>
                        <span class="info-value">Olive</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ukuran & Qty</span>
                        <span class="info-value">Large x 1</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Harga Produk Dasar</span>
                        <span class="info-value">Rp. 70.000</span>
                    </div>
                </div>
            </div>
            <div class="product-image-section">
                <img src="httpspre-logo.jpg" alt="Product Image">
            </div>
        </div>
    </div>

    {{-- Kartu Rincian Biaya dan Log --}}
    <div class="card summary-card">
        <div class="summary-table">
            <div class="summary-header">
                <div class="summary-col">Kategori</div>
                <div class="summary-col">Detail</div>
            </div>
            <div class="summary-body">
                <div class="summary-row">
                    <div class="summary-col">Biaya Produk</div>
                    <div class="summary-col">Rp 70.000</div>
                </div>
                <div class="summary-row">
                    <div class="summary-col">Biaya Custom</div>
                    <div class="summary-col">Rp -</div>
                </div>
                <div class="summary-row">
                    <div class="summary-col">Subtotal</div>
                    <div class="summary-col">Rp 100.000</div>
                </div>
                <div class="summary-row">
                    <div class="summary-col">Diskon</div>
                    <div class="summary-col">Rp 0</div>
                </div>
                <div class="summary-row">
                    <div class="summary-col">Log Status Pesanan</div>
                    <div class="summary-col log-status">
                        Nov 8th, 2023 10:00: Pesanan dibuat. 
                        Nov 8th, 2023 10:15: diterima. 
                        Nov 9th, 2023 14:00: Status diubah ke Produksi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</x-admin-layout>