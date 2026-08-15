@component('mail::message')
@if(isset($orderType) && $orderType === 'custom')
# Design Custom Anda Tidak Dapat Diproses

Halo {{ $order->user->name }},

Mohon maaf, design custom Anda dengan nomor **CUSTOM-{{ $order->id }}** tidak dapat diproses karena:

{{ $rejectionReason }}

Detail Pesanan:
- Nomor Pesanan: CUSTOM-{{ $order->id }}
- Produk: {{ $order->product_name ?? 'Custom Design' }}
- Total: Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}
- Tanggal Pesanan: {{ $order->created_at->format('d M Y H:i') }}
@else
# Pesanan Anda Tidak Dapat Diproses

Halo {{ $order->user->name }},

Mohon maaf, pesanan Anda dengan nomor **{{ $order->order_number }}** tidak dapat diproses karena:

{{ $rejectionReason }}

Detail Pesanan:
- Nomor Pesanan: {{ $order->order_number }}
- Total: Rp {{ number_format($order->total_amount ?? $order->total ?? 0, 0, ',', '.') }}
- Tanggal Pesanan: {{ $order->created_at->format('d M Y H:i') }}
@endif

Anda dapat menghubungi kami untuk informasi lebih lanjut atau membuat pesanan baru melalui website kami.

@component('mail::button', ['url' => url('/')])
Kembali ke Website
@endcomponent

Terima kasih atas pengertian Anda.

Salam,<br>
{{ config('app.name') }}
@endcomponent