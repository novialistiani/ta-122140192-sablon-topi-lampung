@extends('emails.layouts.base')

@section('content')
    <h2>Pembayaran Diterima! 💳</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Pembayaran Anda telah <strong>berhasil diterima</strong>! Terima kasih atas pembayaran Anda.</p>
    
    <div class="info-box info-box-success">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Nomor Transaksi:</span>
            <span class="info-value">{{ $data['transaction_number'] ?? '-' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Metode Pembayaran:</span>
            <span class="info-value">{{ $data['payment_method'] ?? 'Transfer Bank' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Jumlah Dibayar:</span>
            <span class="info-value"><strong>{{ $data['amount'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Pembayaran:</span>
            <span class="info-value">{{ $data['payment_date'] ?? date('d M Y H:i') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #28a745;">Lunas</strong></span>
        </div>
    </div>
    
    <p>Pesanan Anda akan segera diproses. Kami akan mengirimkan notifikasi selanjutnya mengenai status pesanan Anda.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-success">Lihat Detail Pembayaran</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Invoice pembayaran Anda dapat diunduh dari halaman detail pesanan.
    </p>
@endsection
