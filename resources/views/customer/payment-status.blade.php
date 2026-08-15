<x-customer-layout title="Status Pembayaran" active="payment-status">
    @vite(['resources/css/customer/shared.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }

        .main-container {
            margin-left: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #212529;
        }

        .btn-back {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e9ecef;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected, .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .va-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .va-number {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            margin: 16px 0;
            font-family: 'Courier New', monospace;
        }

        .va-info {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 24px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -23px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #667eea;
        }

        .timeline-item:after {
            content: '';
            position: absolute;
            left: -18px;
            top: 20px;
            width: 2px;
            height: calc(100% - 12px);
            background: #e9ecef;
        }

        .timeline-item:last-child:after {
            display: none;
        }

        .timeline-date {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .timeline-content {
            font-weight: 500;
            color: #212529;
        }

        .product-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: #212529;
            margin-bottom: 4px;
        }

        .product-meta {
            font-size: 14px;
            color: #6c757d;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin: 16px 0;
        }
    </style>

    <div class="mx-auto max-w-full">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('order-list') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
                    </a>
                </div>

                <!-- Alert Notifications -->
                @if($virtualAccount && $virtualAccount->status === 'pending')
                    <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Menunggu Pembayaran</strong><br>
                    <span id="alert-countdown">Silakan lakukan pembayaran sebelum waktu habis</span>
                </div>
                @elseif($paymentTransaction && $paymentTransaction->status === 'paid')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Pembayaran Berhasil!</strong><br>
                        Pesanan Anda sedang diproses.
                    </div>
                @endif

                <div class="grid">
                    <!-- Order Details -->
                    <div class="card">
                        <h3 class="card-title"><i class="fas fa-shopping-bag"></i> Detail Pesanan</h3>
                
                @if($orderData['type'] === 'custom')
                    <!-- Custom Order -->
                    <div class="product-item">
                        @if($orderData['image'])
                            <img src="{{ asset('storage/' . $orderData['image']) }}" alt="{{ $orderData['product_name'] }}" class="product-image">
                        @else
                            <div class="product-image" style="background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="font-size: 32px; color: #adb5bd;"></i>
                            </div>
                        @endif
                        <div class="product-details">
                            <div class="product-name">{{ $orderData['product_name'] }}</div>
                            <div class="product-meta">
                                Jumlah: {{ $orderData['quantity'] }}<br>
                                Jenis: Custom Design<br>
                                @if($orderData['cutting_type'])
                                    Cutting: {{ $orderData['cutting_type'] }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <span class="info-label">ID Pesanan</span>
                        <span class="info-value">#{{ $orderData['id'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pesanan</span>
                        <span class="info-value">{{ $orderData['created_at']->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status Pesanan</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $orderData['status'] }}">
                                {{ ucfirst($orderData['status']) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Biaya Produk</span>
                        <span class="info-value">Rp {{ number_format($orderData['product_price'], 0, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Biaya Custom</span>
                        <span class="info-value">Rp {{ number_format($orderData['custom_price'], 0, ',', '.') }}</span>
                    </div>
                @else
                    <!-- Regular Order -->
                    @foreach($orderData['items'] as $item)
                    <div class="product-item">
                        @if(!empty($item['image']))
                            <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="product-image">
                        @else
                            <div class="product-image" style="background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="font-size: 32px; color: #adb5bd;"></i>
                            </div>
                        @endif
                        <div class="product-details">
                            <div class="product-name">{{ $item['name'] }}</div>
                            <div class="product-meta">
                                Warna: {{ $item['color'] ?? 'N/A' }}<br>
                                Ukuran: {{ $item['size'] ?? 'N/A' }}<br>
                                Jumlah: {{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="info-row">
                        <span class="info-label">ID Pesanan</span>
                        <span class="info-value">#{{ $orderData['id'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pesanan</span>
                        <span class="info-value">{{ $orderData['created_at']->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status Pesanan</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $orderData['status'] }}">
                                {{ ucfirst($orderData['status']) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Subtotal</span>
                        <span class="info-value">Rp {{ number_format($orderData['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Diskon</span>
                        <span class="info-value">Rp {{ number_format($orderData['discount'], 0, ',', '.') }}</span>
                    </div>
                @endif

                <div style="margin-top: 16px; padding-top: 16px; border-top: 2px solid #e9ecef;">
                    <div class="info-row">
                        <span class="info-label" style="font-size: 18px;">Total Pembayaran</span>
                        <span class="total-amount">Rp {{ number_format($orderData['total_price'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card">
                <h3 class="card-title"><i class="fas fa-credit-card"></i> Detail Pembayaran</h3>
                
                @if($virtualAccount)
                    <!-- Virtual Account Card -->
                    <div class="va-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <i class="fas fa-university" style="font-size: 24px;"></i>
                                <div style="margin-top: 8px; font-size: 18px; font-weight: 600;">
                                    {{ strtoupper($virtualAccount->bank_code) }} Virtual Account
                                </div>
                            </div>
                            <span class="status-badge" style="background: rgba(255,255,255,0.3); color: white;">
                                {{ ucfirst($virtualAccount->status) }}
                            </span>
                        </div>
                        
                        <div class="va-number">
                            {{ $virtualAccount->va_number }}
                        </div>

                        <div class="va-info">
                            <div>
                                <div style="font-size: 13px; opacity: 0.8;">Total Pembayaran</div>
                                <div style="font-size: 20px; font-weight: 600; margin-top: 4px;">
                                    Rp {{ number_format($virtualAccount->amount, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 13px; opacity: 0.8;">Sisa Waktu</div>
                                <div id="countdown-timer" style="font-size: 24px; font-weight: 700; margin-top: 8px; color: #ef4444; font-family: monospace;">
                                    Loading...
                                </div>
                                <div id="expired-date" style="font-size: 11px; opacity: 0.7; margin-top: 8px; color: #64748b;">
                                    Berlaku sampai: <span id="expired-time-display">{{ $virtualAccount->expired_at->format('d M Y, H:i') }} WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Cara Pembayaran:</strong><br>
                        1. Salin nomor Virtual Account di atas<br>
                        2. Buka aplikasi mobile banking atau ATM<br>
                        3. Pilih menu Transfer/Bayar<br>
                        4. Masukkan nomor VA dan konfirmasi pembayaran
                    </div>
                @endif

                @if($paymentTransaction)
                    <h4 style="font-size: 16px; font-weight: 600; margin: 24px 0 12px;">Informasi Transaksi</h4>
                    <div class="info-row">
                        <span class="info-label">ID Transaksi</span>
                        <span class="info-value">#{{ $paymentTransaction->id }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Metode Pembayaran</span>
                        <span class="info-value">{{ strtoupper($paymentTransaction->payment_method) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jumlah</span>
                        <span class="info-value">Rp {{ number_format($paymentTransaction->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $paymentTransaction->status }}">
                                {{ ucfirst($paymentTransaction->status) }}
                            </span>
                        </span>
                    </div>
                    @if($paymentTransaction->paid_at)
                    <div class="info-row">
                        <span class="info-label">Dibayar Pada</span>
                        <span class="info-value">{{ $paymentTransaction->paid_at->format('d M Y, H:i') }}</span>
                    </div>
                    @endif
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i>
                        Belum ada transaksi pembayaran untuk pesanan ini.
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Status</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date">{{ $orderData['created_at']->format('d M Y, H:i') }}</div>
                    <div class="timeline-content">Pesanan dibuat</div>
                </div>
                
                @if($orderData['approved_at'])
                <div class="timeline-item">
                    <div class="timeline-date">{{ $orderData['approved_at']->format('d M Y, H:i') }}</div>
                    <div class="timeline-content">Pesanan disetujui</div>
                </div>
                @endif

                @if($virtualAccount)
                <div class="timeline-item">
                    <div class="timeline-date">{{ $virtualAccount->created_at->format('d M Y, H:i') }}</div>
                    <div class="timeline-content">Virtual Account dibuat</div>
                </div>
                @endif

                @if($paymentTransaction && $paymentTransaction->paid_at)
                <div class="timeline-item">
                    <div class="timeline-date">{{ $paymentTransaction->paid_at->format('d M Y, H:i') }}</div>
                    <div class="timeline-content">Pembayaran berhasil</div>
                </div>
                @endif

                @if($orderData['status'] === 'processing')
                <div class="timeline-item">
                    <div class="timeline-date">{{ now()->format('d M Y, H:i') }}</div>
                    <div class="timeline-content">Pesanan sedang diproses</div>
                </div>
                @endif

                @if($orderData['status'] === 'completed')
                <div class="timeline-item">
                    <div class="timeline-date">{{ now()->format('d M Y, H:i') }}</div>
                    <div class="timeline-content">Pesanan selesai</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Timeline Card End -->
            </div>
        </div>
    </div>

    @if($virtualAccount && $virtualAccount->status === 'pending')
    <script>
        console.log('Countdown script loaded');
        
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Starting countdown');
            
            // Countdown Timer for VA Expiry
            const expiredAtISO = '{{ $virtualAccount->expired_at->toISOString() }}';
            console.log('Expired At ISO:', expiredAtISO);
            
            const expiredAt = new Date(expiredAtISO).getTime();
            console.log('Expired At Timestamp:', expiredAt);
            console.log('Current Time:', new Date().getTime());
            
            const countdownElement = document.getElementById('countdown-timer');
            const alertCountdown = document.getElementById('alert-countdown');
            
            if (!countdownElement) {
                console.error('Countdown element not found!');
                return;
            }
            
            console.log('Countdown element found:', countdownElement);
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = expiredAt - now;
                
                if (distance < 0) {
                    // VA Expired
                    if (countdownElement) {
                        countdownElement.textContent = 'EXPIRED';
                        countdownElement.style.color = '#991b1b';
                    }
                    if (alertCountdown) {
                        alertCountdown.innerHTML = '<strong style="color: #991b1b;">Virtual Account sudah expired!</strong>';
                    }
                    
                    // Auto refresh page after 2 seconds to update status
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    
                    return;
                }
                
                // Calculate time components
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Format display
                let displayText = '';
                let alertText = '';
                
                if (days > 0) {
                    displayText = `${days}d ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    alertText = `Silakan lakukan pembayaran dalam ${days} hari ${hours} jam ${minutes} menit ${seconds} detik`;
                } else if (hours > 0) {
                    displayText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    alertText = `Silakan lakukan pembayaran dalam ${hours} jam ${minutes} menit ${seconds} detik`;
                } else if (minutes > 0) {
                    displayText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    alertText = `Silakan lakukan pembayaran dalam ${minutes} menit ${seconds} detik`;
                } else {
                    displayText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    alertText = `<strong style="color: #dc2626;">Segera bayar! Tersisa ${seconds} detik!</strong>`;
                }
                
                if (countdownElement) {
                    countdownElement.textContent = displayText;
                    console.log('Timer updated:', displayText);
                }
                
                if (alertCountdown) {
                    alertCountdown.innerHTML = alertText;
                }
                
                // Change color based on time remaining
                if (countdownElement) {
                    if (distance < 5 * 60 * 1000) {
                        // Less than 5 minutes - red and blink
                        countdownElement.style.color = '#dc2626';
                        countdownElement.style.animation = 'blink 1s ease-in-out infinite';
                    } else if (distance < 15 * 60 * 1000) {
                        // Less than 15 minutes - orange
                        countdownElement.style.color = '#ea580c';
                        countdownElement.style.animation = 'none';
                    } else {
                        // More than 15 minutes - red (default)
                        countdownElement.style.color = '#ef4444';
                        countdownElement.style.animation = 'none';
                    }
                }
            }
        
            // Update immediately
            updateCountdown();
            console.log('First countdown update completed');
            
            // Update every second
            const countdownInterval = setInterval(function() {
                updateCountdown();
            }, 1000);
            console.log('Countdown interval started');
            
            // Add blink animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes blink {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.3; }
                }
            `;
            document.head.appendChild(style);
            console.log('Blink animation style added');
        }); // End DOMContentLoaded
    </script>
    @endif
</x-customer-layout>
