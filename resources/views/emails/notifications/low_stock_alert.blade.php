@extends('emails.layouts.base')

@section('content')
    <h2>Peringatan Stok Menipis! ⚠️</h2>
    
    <p>Halo <strong>Admin</strong>,</p>
    
    <p>Sistem mendeteksi bahwa stok produk berikut telah <strong>mencapai batas minimum</strong> dan perlu segera diisi ulang.</p>
    
    <div class="info-box info-box-danger">
        <div class="info-item">
            <span class="info-label">Nama Produk:</span>
            <span class="info-value"><strong>{{ $data['product_name'] }}</strong></span>
        </div>
        @if(isset($data['product_sku']))
            <div class="info-item">
                <span class="info-label">SKU:</span>
                <span class="info-value">{{ $data['product_sku'] }}</span>
            </div>
        @endif
        @if(isset($data['variant_name']))
            <div class="info-item">
                <span class="info-label">Varian:</span>
                <span class="info-value">{{ $data['variant_name'] }}</span>
            </div>
        @endif
        <div class="info-item">
            <span class="info-label">Stok Tersisa:</span>
            <span class="info-value"><strong style="color: #dc3545;">{{ $data['current_stock'] }} unit</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Batas Minimum:</span>
            <span class="info-value">{{ $data['minimum_stock'] ?? '10' }} unit</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #dc3545;">Stok Rendah</strong></span>
        </div>
    </div>
    
    @if(isset($data['recommendation']))
        <div class="info-box info-box-warning">
            <p style="margin: 0;"><strong>Rekomendasi:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['recommendation'] }}</p>
        </div>
    @else
        <div class="info-box info-box-warning">
            <p style="margin: 0;"><strong>Rekomendasi:</strong></p>
            <p style="margin: 10px 0 0 0;">
                Segera lakukan pengisian ulang stok untuk menghindari kehabisan stok yang dapat mempengaruhi pesanan customer.
            </p>
        </div>
    @endif
    
    <p>Mohon segera lakukan pengecekan dan pengisian stok untuk produk ini.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button">Kelola Stok Produk</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Email ini dikirim otomatis saat sistem mendeteksi stok produk mencapai batas minimum yang telah ditentukan.
    </p>
@endsection
