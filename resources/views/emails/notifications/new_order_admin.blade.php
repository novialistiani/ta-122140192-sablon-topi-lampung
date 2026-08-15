@extends('emails.layouts.base')

@section('content')
    <h2>Pesanan Baru Masuk! 🔔</h2>
    
    <p>Halo <strong>Admin</strong>,</p>
    
    <p>Ada pesanan baru yang perlu diverifikasi dan diproses.</p>
    
    <div class="info-box info-box-warning">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] ?? 'N/A' }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Nama Customer:</span>
            <span class="info-value">{{ $data['customer_name'] ?? 'N/A' }}</span>
        </div>
        @if(isset($data['customer_email']))
            <div class="info-item">
                <span class="info-label">Email Customer:</span>
                <span class="info-value">{{ $data['customer_email'] }}</span>
            </div>
        @endif
        @if(isset($data['customer_phone']))
            <div class="info-item">
                <span class="info-label">No. Telepon:</span>
                <span class="info-value">{{ $data['customer_phone'] }}</span>
            </div>
        @endif
        <div class="info-item">
            <span class="info-label">Total Pesanan:</span>
            <span class="info-value"><strong>{{ $data['total_amount'] ?? 'N/A' }}</strong></span>
        </div>
        @if(isset($data['order_date']))
        <div class="info-item">
            <span class="info-label">Tanggal Pesanan:</span>
            <span class="info-value">{{ $data['order_date'] }}</span>
        </div>
        @endif
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #f2994a;">Menunggu Verifikasi</strong></span>
        </div>
    </div>
    
    @if(isset($data['order_items']))
        <div class="info-box">
            <p style="margin: 0 0 10px 0;"><strong>Item Pesanan:</strong></p>
            {!! $data['order_items'] !!}
        </div>
    @endif
    
    @if(isset($data['customer_notes']))
        <div class="info-box">
            <p style="margin: 0;"><strong>Catatan Customer:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['customer_notes'] }}</p>
        </div>
    @endif
    
    <p>Silakan verifikasi dan proses pesanan ini sesegera mungkin.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-warning">Verifikasi Pesanan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Email ini dikirim otomatis saat ada pesanan baru masuk ke sistem.
    </p>
@endsection
