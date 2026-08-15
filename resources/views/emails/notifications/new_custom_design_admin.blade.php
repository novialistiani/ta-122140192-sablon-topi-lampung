@extends('emails.layouts.base')

@section('content')
    <h2>Design Custom Baru! 🎨</h2>
    
    <p>Halo <strong>Admin</strong>,</p>
    
    <p>Ada pesanan custom design baru yang perlu diproses.</p>
    
    <div class="info-box info-box-warning">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['design_number'] ?? 'N/A' }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Nama Customer:</span>
            <span class="info-value">{{ $data['customer_name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Nama Produk:</span>
            <span class="info-value">{{ $data['design_name'] ?? 'Desain Custom' }}</span>
        </div>
        @if(isset($data['total_amount']))
        <div class="info-item">
            <span class="info-label">Total Pesanan:</span>
            <span class="info-value"><strong>{{ $data['total_amount'] }}</strong></span>
        </div>
        @endif
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #f2994a;">Menunggu Konfirmasi</strong></span>
        </div>
    </div>
    
    <p>Silakan review dan konfirmasi pesanan custom design ini.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-warning">Lihat Detail Pesanan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Email ini dikirim otomatis saat ada pesanan custom design baru.
    </p>
@endsection
