@extends('emails.layouts.base')

@section('content')
    <h2>Pesanan Disetujui! ✅</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Kabar gembira! Pesanan Anda telah <strong>disetujui</strong>. Silakan lakukan pembayaran melalui QRIS dalam waktu <strong>60 menit</strong>.</p>
    
    <div class="info-box info-box-success">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Disetujui:</span>
            <span class="info-value">{{ $data['approved_date'] ?? date('d M Y H:i') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Total Pembayaran:</span>
            <span class="info-value"><strong>{{ $data['total_amount'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #28a745;">Disetujui</strong></span>
        </div>
    </div>
    
    @if(isset($data['notes']))
        <div class="info-box">
            <p style="margin: 0;"><strong>Catatan dari Admin:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['notes'] }}</p>
        </div>
    @endif
    
       <p>Setelah pembayaran, unggah bukti pembayaran dan pilih tanggal pengambilan di halaman detail pesanan. Pesanan akan diproses setelah pembayaran dikonfirmasi admin. Jika bukti pembayaran belum diunggah sebelum batas waktu, pesanan akan dibatalkan otomatis.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-success">Lihat Detail Pesanan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Terima kasih atas kepercayaan Anda berbelanja di {{ config('app.name') }}!
    </p>
@endsection
