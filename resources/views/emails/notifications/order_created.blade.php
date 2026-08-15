@extends('emails.layouts.base')

@section('content')
    <h2>Pesanan Berhasil Dibuat! 🎉</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] ?? 'Pelanggan' }}</strong>,</p>
    
    <p>Terima kasih telah berbelanja di {{ config('app.name') }}! Pesanan Anda telah berhasil kami terima dan sedang dalam proses verifikasi.</p>
    
    <div class="info-box info-box-success">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] ?? 'N/A' }}</strong></span>
        </div>
        @if(isset($data['order_date']))
        <div class="info-item">
            <span class="info-label">Tanggal Pesanan:</span>
            <span class="info-value">{{ $data['order_date'] }}</span>
        </div>
        @endif
        <div class="info-item">
            <span class="info-label">Total Pembayaran:</span>
            <span class="info-value"><strong>{{ $data['total_amount'] ?? 'N/A' }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value">Menunggu Verifikasi</span>
        </div>
    </div>
    
    <p>Kami akan segera memverifikasi pesanan Anda dan mengirimkan notifikasi selanjutnya melalui email ini.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button">Lihat Detail Pesanan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        <strong>Tips:</strong> Pastikan Anda melakukan pembayaran sesuai dengan instruksi yang diberikan untuk mempercepat proses pesanan Anda.
    </p>
@endsection
