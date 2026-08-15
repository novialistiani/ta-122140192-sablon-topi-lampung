<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject ?? 'Notifikasi' }}</title>
    <style>
        /* Reset Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        table {
            border-collapse: collapse;
        }
        
        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
        }
        
        .logo {
            margin-bottom: 10px;
        }
        
        /* Content */
        .email-content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        
        .email-content h2 {
            color: #667eea;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .email-content p {
            margin: 15px 0;
            font-size: 15px;
        }
        
        /* Info Box */
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .info-box-success {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        
        .info-box-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        
        .info-box-danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .info-item {
            margin: 10px 0;
            font-size: 14px;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            min-width: 140px;
        }
        
        .info-value {
            color: #333;
        }
        
        /* Button */
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            transition: transform 0.2s;
        }
        
        .button:hover {
            transform: translateY(-2px);
        }
        
        .button-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        }
        
        .button-warning {
            background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
        }
        
        /* Footer */
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .email-footer p {
            margin: 8px 0;
            font-size: 13px;
            color: #6c757d;
        }
        
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        /* Divider */
        .divider {
            border: 0;
            border-top: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 30px 20px !important;
            }
            
            .email-header h1 {
                font-size: 20px !important;
            }
            
            .button {
                padding: 12px 24px !important;
                font-size: 14px !important;
            }
            
            .info-label {
                min-width: 100px !important;
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px 0;">
                <table class="email-container" cellspacing="0" cellpadding="0" border="0" width="600" align="center">
                    
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            @if(isset($logo))
                                <div class="logo">
                                    {{ $logo }}
                                </div>
                            @endif
                            <h1>{{ $header ?? config('app.name') }}</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td class="email-content">
                            @yield('content')
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p><strong>{{ config('app.name') }}</strong></p>
                            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                            <p>
                                Jika Anda memiliki pertanyaan, silakan hubungi 
                                <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
                            </p>
                            <p style="margin-top: 20px; font-size: 12px;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
