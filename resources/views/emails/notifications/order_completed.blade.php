@extends('emails.layouts.base')

@section('content')
    <h2>Pesanan Selesai! 🎊</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Terima kasih telah berbelanja di {{ config('app.name') }}! Pesanan Anda telah <strong>selesai diproses</strong>.</p>
    
    <div class="info-box info-box-success">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Selesai:</span>
            <span class="info-value">{{ $data['completed_date'] ?? date('d M Y H:i') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Total Pembayaran:</span>
            <span class="info-value"><strong>{{ $data['total_amount'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #28a745;">Selesai</strong></span>
        </div>
    </div>
    
    @if(isset($data['delivery_info']))
        <div class="info-box">
            <p style="margin: 0;"><strong>Informasi Pengiriman/Pengambilan:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['delivery_info'] }}</p>
        </div>
    @endif
    
    <p>Kami berharap Anda puas dengan produk dan layanan kami. Jika ada masalah atau pertanyaan, jangan ragu untuk menghubungi kami.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-success">Lihat Detail Pesanan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        <strong>Terima kasih atas kepercayaan Anda!</strong> Kami tunggu pesanan Anda selanjutnya. 😊
    </p>
@endsection
