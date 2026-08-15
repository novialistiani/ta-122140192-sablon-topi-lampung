<x-admin-layout title="Custom Order Detail">
    @push('styles')
        {{-- Pastikan path ini sesuai dengan struktur proyek Anda --}}
        @vite(['resources/css/admin/order-detail-custom.css'])
        
        {{-- Diperlukan untuk ikon kalender --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @endpush

<div class="order-detail-container">
    {{-- Header Halaman dengan Breadcrumb --}}
    <header class="page-header">
        <h1 class="page-title">
            <a href="/path-to-order-list">Order List</a> 
            <span class="breadcrumb-separator">></span> 
            Detail
        </h1>
        <div class="date-range-picker">
            <i class="far fa-calendar-alt"></i>
            <span>Oktober 16, 2025 - November 11, 2025</span>
        </div>
    </header>

    {{-- Kartu Detail Custom --}}
    <div class="card detail-custom-card">
        <div class="card-body">
            <table class="custom-details-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Detail</th>
                        <th>Preview & Download</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="category-col">Posisi Cetak</td>
                        <td>Depan Dada (Area A)</td>
                        <td>Panduan Area Cetak: <a href="#">[ Lihat Gambar Panduan ]</a></td>
                    </tr>
                    <tr>
                        <td class="category-col">Jenis Cetak</td>
                        <td>Sablon Digital (Printing)</td>
                        <td>Ukuran Cetak: A4 (21 x 29.7 cm)</td>
                    </tr>
                    <tr>
                        <td class="category-col">File Desain</td>
                        <td><a href="#" class="file-link">Logo_PT_ABC.png</a></td>
                        <td>
                            Preview: <a href="#">[ Tampilkan Pratinjau Desain ]</a><br>
                            Download: <a href="#">[ Unduh File Asli (.png) ]</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kartu Rincian Biaya dan Log (dengan Biaya Custom) --}}
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
                     <div class="summary-col">Rp 30.000</div>
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