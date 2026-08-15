@component('mail::message')
# Pesanan Anda Telah Disetujui

Halo {{ $order->user->name }},

Pesanan Anda dengan nomor **{{ $order->order_number }}** telah disetujui oleh admin. Mohon segera lakukan pembayaran dengan mengikuti langkah berikut:

1. Generate Virtual Account melalui halaman detail pesanan Anda
2. Lakukan pembayaran sesuai nominal yang tertera

**Penting:**
- Batas waktu generate VA: {{ $vaGenerateDeadline }} WIB
- Setelah generate VA, lakukan pembayaran maksimal 10 menit
- Jika melewati batas waktu, pesanan akan otomatis dibatalkan

@component('mail::button', ['url' => url("/orders/{$order->id}")])
Lihat Detail Pesanan
@endcomponent

Terima kasih telah berbelanja di toko kami.

Salam,<br>
{{ config('app.name') }}
@endcomponent