@extends('emails.layouts.base')

@section('content')
    <h2>Desain Custom Disetujui! ✅</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Kabar gembira! Desain custom Anda telah <strong>disetujui</strong> dan siap untuk diproduksi.</p>
    
    <div class="info-box info-box-success">
        <div class="info-item">
            <span class="info-label">Nomor Desain:</span>
            <span class="info-value"><strong>{{ $data['design_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Disetujui:</span>
            <span class="info-value">{{ $data['approved_date'] ?? date('d M Y H:i') }}</span>
        </div>
        @if(isset($data['design_name']))
            <div class="info-item">
                <span class="info-label">Nama Desain:</span>
                <span class="info-value">{{ $data['design_name'] }}</span>
            </div>
        @endif
        @if(isset($data['price']))
            <div class="info-item">
                <span class="info-label">Estimasi Harga:</span>
                <span class="info-value"><strong>{{ $data['price'] }}</strong></span>
            </div>
        @endif
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #28a745;">Disetujui</strong></span>
        </div>
    </div>
    
    @if(isset($data['admin_notes']))
        <div class="info-box">
            <p style="margin: 0;"><strong>Catatan dari Tim Review:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['admin_notes'] }}</p>
        </div>
    @endif
    
    @if(isset($data['price']))
        <p>Anda dapat melanjutkan untuk melakukan pemesanan berdasarkan desain ini dengan harga yang telah ditentukan.</p>
    @else
        <p>Silakan hubungi kami untuk informasi lebih lanjut mengenai harga dan proses produksi.</p>
    @endif
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-success">Lihat Detail & Pesan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Terima kasih telah mempercayai {{ config('app.name') }} untuk mewujudkan desain custom Anda!
    </p>
@endsection
