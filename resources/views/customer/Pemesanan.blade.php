<x-customer-layout title="Pemesanan" active="pemesanan">
    @vite(['resources/css/guest/Pemesanan.css'])
  
    

    <!-- Main Content -->
    <main class="checkout-main">
        <div class="container">
            <!-- Progress Steps -->
            <div class="steps-container">
                <div class="step completed">
                    <div class="step-icon completed">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="step-text">
                        <div class="step-number">Langkah 1</div>
                        <div class="step-label">Alamat</div>
                    </div>
                </div>

                <div class="step active">
                    <div class="step-icon active">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                    </div>
                    <div class="step-text">
                        <div class="step-number">Langkah 2</div>
                        <div class="step-label">Pengiriman</div>
                    </div>
                </div>

                <div class="step">
                    <div class="step-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                    </div>
                    <div class="step-text">
                        <div class="step-number">Langkah 3</div>
                        <div class="step-label">Pembayaran</div>
                    </div>
                </div>
            </div>

            <!-- Address Info -->
            <section class="mb-6">
                <h2 class="section-title">Alamat Pengiriman</h2>
                <div class="bg-slate-50 p-4 rounded-lg">
                    <p class="font-medium text-slate-900">{{ $address->label ?? $address->recipient_name }}</p>
                    <p class="text-sm text-slate-600 mt-1">{{ $address->address }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                    <p class="text-sm text-slate-600">{{ $address->phone }}</p>
                </div>
            </section>

            <!-- Shipping Method Section -->
            <section class="payment-section">
                <h2 class="section-title">Metode Pengiriman</h2>

                <div class="payment-options">
                    <!-- Option 1: Melalui paket -->
                    <label class="payment-option payment-option-selected">
                        <input type="radio" name="shipping" value="delivery" checked>
                        <div class="option-content">
                            <span class="option-icon material-icons">local_shipping</span>
                            <span class="option-text">Melalui paket</span>
                        </div>
                        <span class="checkmark"></span>
                    </label>

                    <!-- Option 2: Ambil Di toko -->
                    <label class="payment-option">
                        <input type="radio" name="shipping" value="pickup">
                        <div class="option-content">
                            <span class="option-icon material-icons">store</span>
                            <span class="option-text">Ambil Di toko</span>
                        </div>
                        <span class="checkmark"></span>
                    </label>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Melalui paket:</strong> Barang akan dikirim ke alamat Anda<br>
                        <strong>Ambil di toko:</strong> Gratis - Ambil barang langsung di toko kami
                    </p>
                </div>
            </section>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-orange" onclick="window.location.href='/alamat'">Kembali</button>
                <button class="btn btn-primary" id="next-btn" onclick="proceedToPayment()">Lanjut</button>
            </div>
        </div>
    </main>

    <script>
        function proceedToPayment() {
            const selectedShipping = document.querySelector('input[name="shipping"]:checked');
            
            if (!selectedShipping) {
                alert('Silakan pilih metode pengiriman');
                return;
            }
            
            // Save to session via AJAX
            fetch('{{ route('pemesanan.select') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    shipping_method: selectedShipping.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/pembayaran';
                } else {
                    alert('Gagal menyimpan metode pengiriman. Silakan coba lagi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        }
        
        // Handle payment option clicks to toggle selection
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('payment-option-selected');
                });
                this.classList.add('payment-option-selected');
            });
        });
    </script>

    @stack('scripts')
</x-customer-layout>
