<x-customer-layout title="Pemesanan" active="pemesanan">
    @vite(['resources/css/guest/Pemesanan.css'])

    <!-- Main Content -->
    <main class="checkout-main">
        <div class="container">

            <!-- Info Pengambilan di Toko -->
            <section class="mb-6">
                <h2 class="section-title">Metode Pengambilan</h2>
                <div class="bg-slate-50 p-4 rounded-lg">
                    <p class="font-medium text-slate-900">Ambil di Toko (Pickup)</p>
                    <p class="text-sm text-slate-600 mt-1">Pesanan dapat diambil langsung di toko LGI Store sesuai jadwal operasional.</p>
                </div>
            </section>

                        <!-- Ringkasan Pesanan -->
            <section class="mb-6">
                <h2 class="section-title">Ringkasan Pesanan</h2>
                <div class="bg-slate-50 p-4 rounded-lg flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total yang harus dibayar</span>
                    <span class="text-lg font-bold text-slate-900">Rp {{ number_format((float) $amount, 0, ',', '.') }}</span>
                </div>
            </section>

            @if(!empty($order->payment_deadline))
            <!-- Peringatan Batas Waktu Pembayaran -->
            <section class="mb-6">
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-center">
                    <p class="text-sm text-red-700 font-medium">
                        <i class="fas fa-clock"></i> Selesaikan pembayaran sebelum batas waktu berakhir
                    </p>
                    <p id="payment-countdown" class="text-2xl font-bold text-red-700 mt-1">--:--</p>
                    <p class="text-xs text-red-600 mt-1">
                        Pesanan akan otomatis dibatalkan jika bukti pembayaran belum diunggah sebelum batas waktu ini.
                    </p>
                </div>
            </section>
            @endif


            <!-- QRIS -->
            <section class="payment-section mb-6">
                <h2 class="section-title">Pembayaran via QRIS</h2>
                <div class="p-4 bg-white rounded-lg border border-slate-200 text-center">
                    @if($qris && $qris->qris_image)
                        <img src="{{ asset('storage/' . $qris->qris_image) }}" alt="QRIS LGI Store" class="mx-auto max-w-xs w-full rounded-lg">
                        <p class="text-sm text-slate-600 mt-3">Scan kode QRIS di atas menggunakan aplikasi e-wallet atau m-banking Anda.</p>
                    @else
                        <p class="text-sm text-red-600">QRIS belum tersedia. Silakan hubungi admin.</p>
                    @endif
                </div>
            </section>

            <!-- Form Upload Bukti Pembayaran + Tanggal Pengambilan -->
            <form action="{{ route('pemesanan.submit-payment') }}" method="POST" enctype="multipart/form-data" class="payment-section">
                @csrf
                <input type="hidden" name="order_type" value="{{ $orderType }}">
                <input type="hidden" name="order_id" value="{{ $orderId }}">

                <h2 class="section-title">Tanggal Pengambilan</h2>
                <div class="mb-6">
                    <input type="date" name="pickup_date" min="{{ now()->format('Y-m-d') }}" required
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                </div>

                <h2 class="section-title">Upload Bukti Pembayaran</h2>
                <div class="mb-6">
                    <input type="file" name="payment_proof" accept="image/jpeg,image/jpg,image/png" required
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    <p class="text-xs text-slate-500 mt-1">Format JPG/PNG, maksimal 5MB.</p>
                </div>

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg">{{ session('error') }}</div>
                @endif

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn btn-orange" onclick="window.location.href='{{ route('order-list') }}'">Kembali</button>
                    <button type="submit" class="btn btn-primary">Kirim Bukti Pembayaran</button>
                </div>
            </form>
        </div>
    </main>

        @if(!empty($order->payment_deadline))
    <script>
        const paymentDeadline = new Date('{{ \Carbon\Carbon::parse($order->payment_deadline)->toIso8601String() }}').getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = paymentDeadline - now;
            const el = document.getElementById('payment-countdown');

            if (!el) return;

            if (distance <= 0) {
                el.textContent = 'Waktu habis';
                clearInterval(countdownInterval);
                return;
            }

            const minutes = Math.floor(distance / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            el.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
    </script>
    @endif

    @stack('scripts')
</x-customer-layout>