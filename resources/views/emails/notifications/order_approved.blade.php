@extends('emails.layouts.base')

@section('content')
    <h2>Pesanan Disetujui! ✅</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Kabar gembira! Pesanan Anda telah <strong>disetujui</strong> dan sedang dalam proses produksi.</p>
    
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
    
    <p>Pesanan Anda akan segera diproses. Kami akan mengirimkan notifikasi ketika pesanan sudah siap dikirim atau siap diambil.</p>
    
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
