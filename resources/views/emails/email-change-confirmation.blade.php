<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Perubahan Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: #001F3F;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .alert-box strong {
            display: block;
            margin-bottom: 5px;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 8px 0;
        }
        .info-box strong {
            color: #001F3F;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background: #001F3F;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background: #003366;
        }
        .security-note {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .warning-text {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Konfirmasi Perubahan Email</h1>
        </div>

        <div class="content">
            <p>Halo <strong>{{ $request->user->name }}</strong>,</p>

            <p>Kami menerima permintaan untuk mengubah alamat email akun Anda di <strong>LGI STORE</strong>.</p>

            <div class="info-box">
                <p><strong>Email Lama:</strong> {{ $request->old_email }}</p>
                <p><strong>Email Baru:</strong> {{ $request->new_email }}</p>
                <p><strong>Waktu Permintaan:</strong> {{ $request->created_at->format('d F Y, H:i') }}</p>
            </div>

            <div class="alert-box">
                <strong>⚠️ Peringatan Keamanan</strong>
                Jika Anda tidak melakukan permintaan ini, segera abaikan email ini dan ubah password Anda untuk keamanan akun.
            </div>

            <p>Untuk melanjutkan perubahan email, silakan klik tombol konfirmasi di bawah ini:</p>

            <div style="text-align: center;">
                <a href="{{ $confirmUrl }}" class="button">
                    Konfirmasi Perubahan Email
                </a>
            </div>

            <p style="font-size: 13px; color: #6c757d;">
                Atau salin dan tempel link berikut ke browser Anda:<br>
                <a href="{{ $confirmUrl }}" style="color: #001F3F; word-break: break-all;">{{ $confirmUrl }}</a>
            </p>

            <div class="security-note">
                <strong>🛡️ Catatan Keamanan:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Link konfirmasi ini hanya berlaku selama <strong>24 jam</strong></li>
                    <li>Setelah konfirmasi, email lama tidak dapat digunakan untuk login</li>
                    <li>Gunakan email baru untuk login selanjutnya</li>
                    <li>Jika Anda tidak melakukan permintaan ini, <span class="warning-text">segera hubungi tim support kami</span></li>
                </ul>
            </div>

            <p>Terima kasih atas perhatian Anda terhadap keamanan akun.</p>

            <p>
                Salam hangat,<br>
                <strong>Tim LGI STORE</strong>
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} LGI STORE - Produk Eksklusif Kaos Berkualitas</p>
            <p>Email ini dikirim otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
