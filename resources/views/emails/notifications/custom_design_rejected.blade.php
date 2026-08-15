@extends('emails.layouts.base')

@section('content')
    <h2>Design Custom Ditolak ❌</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] ?? 'Pelanggan' }}</strong>,</p>
    
    <p>Mohon maaf, design custom Anda tidak dapat kami proses.</p>
    
    <div class="info-box info-box-danger">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['design_number'] ?? 'N/A' }}</strong></span>
        </div>
        @if(isset($data['design_name']))
        <div class="info-item">
            <span class="info-label">Nama Produk:</span>
            <span class="info-value">{{ $data['design_name'] }}</span>
        </div>
        @endif
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #dc3545;">Ditolak</strong></span>
        </div>
    </div>
    
    @if(isset($data['rejection_reason']) && $data['rejection_reason'])
        <div class="info-box">
            <p style="margin: 0;"><strong>Alasan Penolakan:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['rejection_reason'] }}</p>
        </div>
    @endif
    
    <p>Jika Anda memiliki pertanyaan atau ingin mengajukan revisi design, silakan hubungi tim kami melalui fitur chat atau buat pesanan custom design baru.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-danger">Lihat Detail</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Terima kasih telah menggunakan layanan kami. Kami harap dapat melayani Anda di kesempatan berikutnya.
    </p>
@endsection
