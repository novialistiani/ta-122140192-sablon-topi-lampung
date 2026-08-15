<x-customer-layout title="Proses Pembayaran" active="pembayaran">
    @vite(['resources/css/customer/shared.css', 'resources/css/customer/Pembayaran.css'])

            <!-- Main Content -->
            <main class="main-container">
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

                    <div class="step completed">
                        <div class="step-icon completed">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <div class="step-text">
                            <div class="step-number">Langkah 2</div>
                            <div class="step-label">Pengiriman</div>
                        </div>
                    </div>

                    <div class="step active">
                        <div class="step-icon active">
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

                <!-- Content Grid -->
                <div class="payment-grid">
                    <!-- Left Section - Order Summary -->
                    <div class="order-summary">
                        <h2 class="section-title">Ringkasan pesanan</h2>
                        
                        <!-- Product Items -->
                        @foreach($items as $item)
                        <div class="product-item">
                            <div class="product-image">
                                @php
                                    $imageUrl = $item['image'] ? (filter_var($item['image'], FILTER_VALIDATE_URL) ? $item['image'] : asset('storage/' . $item['image'])) : 'https://via.placeholder.com/60';
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $item['name'] }}">
                            </div>
                            <div class="product-info">
                                <span class="product-name">{{ $item['name'] }}</span>
                            </div>
                            <div class="product-price">
                                <span>Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach

                        <!-- Address Info -->
                        <div class="info-section">
                            <h3 class="info-title">Alamat</h3>
                            <p class="info-text">{{ $address->address }}, {{ $address->city }} {{ $address->postal_code }}</p>
                        </div>

                        <!-- Shipping Method -->
                        <div class="info-section">
                            <h3 class="info-title">Metode Pengiriman</h3>
                            <p class="info-text">{{ $shippingMethod === 'delivery' ? 'Melalui Paket' : 'Ambil di Toko' }} - Gratis</p>
                        </div>

                        <!-- Price Summary -->
                        <div class="price-summary">
                            <div class="price-row">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="price-row total">
                                <span>Total</span>
                                <span class="total-price">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section - Payment Form -->
                    <div class="payment-form">
                        <h2 class="section-title">Pembayaran</h2>
                        
                        @if(isset($activeVA) && $activeVA)
                        <!-- Active VA Display -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold text-lg text-blue-900">Virtual Account Aktif</h3>
                                <span class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-full">{{ strtoupper($activeVA->bank_code) }}</span>
                            </div>
                            
                            <div class="mb-4">
                                <label class="text-sm text-blue-700 font-medium">Nomor Virtual Account</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 bg-white border border-blue-300 rounded-lg px-4 py-3">
                                        <span class="text-2xl font-mono font-bold text-blue-900" id="va-number-display">{{ $activeVA->va_number }}</span>
                                    </div>
                                    <button type="button" onclick="copyVANumber()" class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="text-sm text-blue-700 font-medium">Jumlah Pembayaran</label>
                                <div class="text-2xl font-bold text-blue-900 mt-1">Rp {{ number_format($activeVA->amount, 0, ',', '.') }}</div>
                            </div>
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-clock text-yellow-600 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-yellow-800">Waktu tersisa:</p>
                                        <p class="text-lg font-bold text-yellow-900" id="countdown-timer">{{ $activeVA->expired_at->diffForHumans() }}</p>
                                        <p class="text-xs text-yellow-700 mt-1">Expired pada: {{ $activeVA->expired_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Selesaikan pembayaran VA ini atau tunggu hingga expired untuk memilih metode pembayaran lain.
                                </p>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-orange" onclick="window.location.href='/pemesanan'">Kembali</button>
                            <button type="button" class="btn btn-primary" onclick="checkPaymentStatus()">
                                <i class="fas fa-sync-alt mr-2"></i>Cek Status Pembayaran
                            </button>
                        </div>
                        @else
                        <!-- Payment Selection Form -->
                        <form id="payment-form">
                            <div class="form-group">
                                <label class="form-label">Generate VA</label>
                                <select id="payment-va" class="form-input">
                                    <option value="">Pilih Bank</option>
                                    <option value="bca">BCA Virtual Account</option>
                                    <option value="bni">BNI Virtual Account</option>
                                    <option value="bri">BRI Virtual Account</option>
                                    <option value="permata">Permata VA</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">E Wallet</label>
                                <select id="payment-ewallet" class="form-input" disabled>
                                    <option value="">Pilih E-Wallet (Coming Soon)</option>
                                    <option value="gopay">GoPay</option>
                                    <option value="dana">DANA</option>
                                    <option value="shopeepay">ShopeePay</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">E-Wallet akan segera tersedia</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="form-actions">
                                <button type="button" class="btn btn-orange" onclick="window.location.href='/pemesanan'">Kembali</button>
                                <button type="button" class="btn btn-primary" id="submitOrder">Generate VA</button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 transform transition-all">
                <div class="p-6">
                    <div class="text-center">
                        <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-amber-100 flex items-center justify-center">
                            <span class="material-icons text-amber-600 text-3xl">check_circle</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mb-2">Pesanan sudah masuk!</h2>
                        <p class="text-slate-600 text-sm leading-relaxed">Silahkan cek notifikasi untuk pemberitahuan lebih lanjut</p>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button id="closeModal" class="flex-1 px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        console.log('Pembayaran script loaded');
        
        // Attach event listener to submit button
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submitOrder');
            if (submitBtn) {
                submitBtn.addEventListener('click', submitOrder);
                console.log('Submit button event listener attached');
            }
        });
        
        // Global functions need to be defined outside conditional blocks
        function copyVANumber() {
            console.log('Copy VA clicked');
            const vaNumber = document.getElementById('va-number-display').textContent;
            navigator.clipboard.writeText(vaNumber).then(() => {
                alert('Nomor VA berhasil disalin!');
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Gagal menyalin nomor VA');
            });
        }
        
        function checkPaymentStatus() {
            console.log('Check payment status clicked');
            
            // Get order info from blade variables
            @if(isset($orderType) && isset($orderId))
                const orderType = '{{ $orderType }}';
                const orderId = '{{ $orderId }}';
                
                // Redirect to payment status page with parameters
                window.location.href = `/payment-status?type=${orderType}&order_id=${orderId}`;
            @else
                // If no specific order (cart checkout), just redirect to payment-status
                // The page will show all orders with VA active status
                window.location.href = '/payment-status';
            @endif
        }
        
        function submitOrder() {
            console.log('submitOrder function called');
            
            const paymentVAElement = document.getElementById('payment-va');
            console.log('payment-va element:', paymentVAElement);
            
            if (!paymentVAElement) {
                console.error('payment-va element not found!');
                alert('Error: Dropdown bank tidak ditemukan');
                return;
            }
            
            const bankCode = paymentVAElement.value;
            console.log('Selected bank code:', bankCode);
            
            if (!bankCode) {
                alert('Silakan pilih bank untuk Generate VA');
                return;
            }
            
            const submitBtn = document.getElementById('submitOrder');
            console.log('Submit button:', submitBtn);
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating VA...';
            }
            
            console.log('Sending request to generate VA...');
            
            // Generate VA
            fetch('{{ route('pembayaran.generate-va') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    bank_code: bankCode,
                    order_type: '{{ $orderType ?? null }}',
                    order_id: '{{ $orderId ?? null }}'
                })
            })
            .then(response => {
                console.log('Response received:', response);
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    console.log('VA generated/retrieved successfully, reloading page...');
                    // Force reload with cache bypass to show VA
                    window.location.reload(true);
                } else {
                    console.error('Generate VA failed:', data.message);
                    alert(data.message || 'Gagal generate VA');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Generate VA';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Generate VA';
                }
            });
        }
        
        console.log('submitOrder function defined:', typeof submitOrder);

        @if(isset($activeVA) && $activeVA)
        // Countdown timer for VA expiry
        const expiredAt = new Date('{{ $activeVA->expired_at->toISOString() }}').getTime();
        
        const countdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const distance = expiredAt - now;
            
            if (distance < 0) {
                clearInterval(countdownInterval);
                document.getElementById('countdown-timer').textContent = 'EXPIRED';
                // Auto refresh page to clear expired VA
                setTimeout(() => location.reload(), 2000);
                return;
            }
            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-timer').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
        @else
        // Mutual exclusive selection for payment methods
        document.addEventListener('DOMContentLoaded', function() {
            const paymentVA = document.getElementById('payment-va');
            const paymentEwallet = document.getElementById('payment-ewallet');
            
            if (paymentVA) {
                paymentVA.addEventListener('change', function() {
                    if (this.value) {
                        if (paymentEwallet) paymentEwallet.value = '';
                    }
                });
            }
            
            const closeModalBtn = document.getElementById('closeModal');
            const successModal = document.getElementById('successModal');
            
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function() {
                    window.location.href = '{{ route('order-list') }}';
                });
            }

            if (successModal) {
                successModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        window.location.href = '{{ route('order-list') }}';
                    }
                });
            }
        });
        @endif
    </script>

    @stack('scripts')
</x-customer-layout>
