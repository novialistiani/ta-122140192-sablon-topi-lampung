@extends('emails.layouts.base')

@section('content')
    <h2>Menunggu Pembayaran ⏳</h2>
    
    <p>Halo <strong>{{ $data['customer_name'] }}</strong>,</p>
    
    <p>Pesanan Anda telah dibuat. Silakan lakukan pembayaran untuk melanjutkan proses pesanan.</p>
    
    <div class="info-box info-box-warning">
        <div class="info-item">
            <span class="info-label">Nomor Pesanan:</span>
            <span class="info-value"><strong>{{ $data['order_number'] }}</strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">Total Pembayaran:</span>
            <span class="info-value"><strong style="color: #f2994a;">{{ $data['total_amount'] }}</strong></span>
        </div>
        @if(isset($data['payment_deadline']))
            <div class="info-item">
                <span class="info-label">Batas Waktu:</span>
                <span class="info-value"><strong>{{ $data['payment_deadline'] }}</strong></span>
            </div>
        @endif
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong style="color: #f2994a;">Menunggu Pembayaran</strong></span>
        </div>
    </div>
    
    @if(isset($data['payment_instructions']))
        <div class="info-box">
            <p style="margin: 0;"><strong>Instruksi Pembayaran:</strong></p>
            <div style="margin: 10px 0 0 0;">
                {!! nl2br(e($data['payment_instructions'])) !!}
            </div>
        </div>
    @endif
    
    @if(isset($data['virtual_account']))
        <div class="info-box info-box-success">
            <p style="margin: 0;"><strong>Nomor Virtual Account:</strong></p>
            <p style="margin: 10px 0 0 0; font-size: 24px; font-weight: bold; color: #667eea; letter-spacing: 2px;">
                {{ $data['virtual_account'] }}
            </p>
        </div>
    @endif
    
    <p>Setelah melakukan pembayaran, kami akan memverifikasi pembayaran Anda dan mengirimkan konfirmasi melalui email.</p>
    
    @if(isset($data['action_url']))
        <div class="button-container">
            <a href="{{ $data['action_url'] }}" class="button button-warning">Bayar Sekarang</a>
        </div>
    @endif
    
    <hr class="divider">
    
    <p style="font-size: 13px; color: #6c757d;">
        <strong>Penting:</strong> Pastikan Anda melakukan pembayaran sebelum batas waktu yang ditentukan untuk menghindari pembatalan otomatis.
    </p>
@endsection
