@extends('emails.layouts.base')

@section('content')
    <h2>Pesanan Ditolak ❌</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Kami mohon maaf untuk memberitahukan bahwa pesanan Anda <strong>tidak dapat diproses</strong> karena alasan berikut:</p>
    
    <div class="info-box info-box-danger">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Ditolak:</span>
            <span class="info-value">{{ $data['rejected_date'] ?? date('d M Y H:i') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #dc3545;">Ditolak</strong></span>
        </div>
    </div>
    
    @if(isset($data['rejection_reason']))
        <div class="info-box info-box-warning">
            <p style="margin: 0;"><strong>Alasan Penolakan:</strong></p>
            <p style="margin: 10px 0 0 0;">{{ $data['rejection_reason'] }}</p>
        </div>
    @endif
    
    <p>Jika Anda memiliki pertanyaan atau ingin melakukan pemesanan ulang dengan perbaikan, silakan hubungi customer service kami.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button">Lihat Detail Pesanan</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        Kami mohon maaf atas ketidaknyamanan ini. Tim kami siap membantu Anda untuk pemesanan selanjutnya.
    </p>
@endsection
