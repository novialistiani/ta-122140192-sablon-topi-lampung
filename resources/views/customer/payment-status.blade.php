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
                @if($orderData['payment_status'] === 'pending' || $orderData['payment_status'] === 'unpaid')
                    <div class="alert alert-warning">
                         <i class="fas fa-exclamation-triangle"></i>
                        <strong>Menunggu Pembayaran</strong><br>
                        Silakan lakukan pembayaran dan unggah bukti pembayaran.
                    </div>
                @elseif($orderData['payment_status'] === 'paid')
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
                           <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="product-image">
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
                
                @if($orderData['payment_status'] === 'paid')
                    <div class="alert alert-success">
                         <i class="fas fa-check-circle"></i>
                        <strong>Pembayaran berhasil.</strong><br>
                         Bukti pembayaran telah diverifikasi.
                    </div>

                @if($orderData['paid_at'])
                     <div class="info-row">
                        <span class="info-label">Dibayar Pada</span>
                         <span class="info-value">
                {{ $orderData['paid_at']->format('d M Y, H:i') }}
                     </span>
                    </div>
                @endif

                @elseif($orderData['payment_status'] === 'waiting_verification')
                        <div class="alert alert-warning">
                                <i class="fas fa-clock"></i>
                                 <strong>Menunggu Verifikasi</strong><br>
                                Bukti pembayaran telah dikirim dan sedang diperiksa oleh admin.
                        </div>

                @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle"></i>
                                 <strong>Belum Dibayar</strong><br>
                                  Belum ada pembayaran untuk pesanan ini.
                        </div>
                @endif

                @if($orderData['payment_proof'])
                    <div class="info-row">
                            <span class="info-label">Bukti Pembayaran</span>
                            <span class="info-value">Sudah diunggah</span>
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

            

                @if($orderData['paid_at'])
                   <div class="timeline-item">
                    <div class="timeline-date">
                        {{ $orderData['paid_at']->format('d M Y, H:i') }}
                    </div>
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

    
                    
                    
</x-customer-layout>
