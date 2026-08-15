@extends('emails.layouts.base')

@section('content')
    <h2>Desain Custom Berhasil Diunggah! 🎨</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Desain custom Anda telah <strong>berhasil diunggah</strong> dan sedang menunggu verifikasi dari tim kami.</p>
    
    <div class="info-box info-box-success">
        <div class="info-item">
            <span class="info-label">Nomor Desain:</span>
            <span class="info-value"><strong>{{ $data['design_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Upload:</span>
            <span class="info-value">{{ $data['upload_date'] ?? date('d M Y H:i') }}</span>
        </div>
        @if(isset($data['design_name']))
            <div class="info-item">
                <span class="info-label">Nama Desain:</span>
                <span class="info-value">{{ $data['design_name'] }}</span>
            </div>
        @endif
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #f2994a;">Menunggu Review</strong></span>
        </div>
    </div>
    
    @if(isset($data['design_notes']))
        <div class="info-box">
            <p style="margin: 0;"><strong>Catatan Desain:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['design_notes'] }}</p>
        </div>
    @endif
    
    <p>Tim kami akan segera mereview desain Anda dan memberikan estimasi harga. Kami akan mengirimkan notifikasi setelah review selesai.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button">Lihat Detail Desain</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Estimasi waktu review: <strong>1-2 hari kerja</strong>. Kami akan menghubungi Anda jika ada pertanyaan mengenai desain.
    </p>
@endsection
