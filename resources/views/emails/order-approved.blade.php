<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Disetujui</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #1a2332 0%, #0a1220 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fbbf24;
        }
        .email-body {
            padding: 30px 20px;
        }
        .success-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .greeting {
            font-size: 18px;
            color: #1a2332;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .message {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 15px;
        }
        .order-details {
            background-color: #f9fafb;
            border-left: 4px solid #fbbf24;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .order-details h3 {
            margin: 0 0 15px 0;
            color: #1a2332;
            font-size: 16px;
            font-weight: 600;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 500;
            color: #6b7280;
        }
        .detail-value {
            font-weight: 600;
            color: #1f2937;
            text-align: right;
        }
        .deadline-warning {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 6px;
            padding: 15px;
            margin: 25px 0;
        }
        .deadline-warning .icon {
            font-size: 20px;
            margin-right: 10px;
            color: #f59e0b;
        }
        .deadline-warning p {
            margin: 0;
            color: #92400e;
            font-weight: 500;
        }
        .deadline-time {
            font-size: 18px;
            font-weight: 700;
            color: #b45309;
            margin-top: 5px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1a2332;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(251, 191, 36, 0.3);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(251, 191, 36, 0.4);
        }
        .instructions {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instructions h4 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 14px;
            font-weight: 600;
        }
        .instructions ol {
            margin: 0;
            padding-left: 20px;
            color: #1e3a8a;
        }
        .instructions li {
            margin: 5px 0;
            font-size: 14px;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 13px;
        }
        .email-footer a {
            color: #fbbf24;
            text-decoration: none;
            font-weight: 500;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 25px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 4px;
            }
            .email-body {
                padding: 20px 15px;
            }
            .detail-row {
                flex-direction: column;
                gap: 4px;
            }
            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">LGI STORE</div>
            <h1>🎉 Pesanan Disetujui!</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="success-badge">
                ✓ Status: Disetujui
            </div>

            <p class="greeting">Halo, {{ $customerName }}!</p>

            <p class="message">
                Kabar gembira! Pesanan Anda telah <strong>disetujui</strong> oleh admin kami. 
                Silakan lakukan pembayaran untuk melanjutkan proses pesanan Anda.
            </p>

            <!-- Order Details -->
            <div class="order-details">
                <h3>📦 Detail Pesanan</h3>
                <div class="detail-row">
                    <span class="detail-label">Jenis Pesanan:</span>
                    <span class="detail-value">{{ $orderType === 'custom' ? 'Custom Design' : 'Regular Order' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ID Pesanan:</span>
                    <span class="detail-value">#{{ $order->id }}</span>
                </div>
                @if($orderType === 'custom')
                    <div class="detail-row">
                        <span class="detail-label">Produk:</span>
                        <span class="detail-value">{{ $order->product->name ?? 'Custom Design' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Harga:</span>
                        <span class="detail-value">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="detail-row">
                        <span class="detail-label">Jumlah Item:</span>
                        <span class="detail-value">{{ is_array($order->items) ? count($order->items) : 1 }} item</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Harga:</span>
                        <span class="detail-value">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Tanggal Disetujui:</span>
                    <span class="detail-value">{{ now()->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <!-- Deadline Warning -->
            <div class="deadline-warning">
                <p>
                    <span class="icon">⏰</span>
                    <strong>Batas Waktu Pembayaran:</strong>
                </p>
                <div class="deadline-time">
                    {{ $paymentDeadline }}
                </div>
                <p style="margin-top: 10px; font-size: 13px;">
                    Harap lakukan pembayaran sebelum batas waktu. Pesanan akan dibatalkan otomatis jika tidak dibayar.
                </p>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h4>📝 Langkah Selanjutnya:</h4>
                <ol>
                    <li>Login ke akun Anda di website LGI Store</li>
                    <li>Buka halaman "Daftar Pesanan"</li>
                    <li>Klik tombol "Bayar" pada pesanan ini</li>
                    <li>Generate Virtual Account (VA) sesuai bank pilihan Anda</li>
                    <li>Lakukan pembayaran melalui VA yang sudah dibuat</li>
                    <li>Pesanan akan diproses setelah pembayaran terverifikasi</li>
                </ol>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/order-list" class="cta-button">
                    Lihat Pesanan Saya →
                </a>
            </div>

            <div class="divider"></div>

            <p class="message" style="font-size: 14px; color: #6b7280;">
                <strong>Catatan:</strong> Jika Anda memiliki pertanyaan atau kendala, jangan ragu untuk menghubungi customer service kami 
                melalui fitur chat di website atau email kami di 
                <a href="mailto:support@lgistore.com" style="color: #fbbf24;">support@lgistore.com</a>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>LGI STORE</strong></p>
            <p>Peduli Kualitas, Bukan Kuantitas</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}">Website</a> | 
                <a href="{{ config('app.url') }}/chatbot">Customer Support</a>
            </p>
            <p style="margin-top: 10px; font-size: 12px;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>
