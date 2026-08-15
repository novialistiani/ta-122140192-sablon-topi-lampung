<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Detail - {{ $customer->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            background: #0a1d37;
            color: white;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0a1d37;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-grid td {
            padding: 10px;
            border: 1px solid #e5e7eb;
        }
        
        .info-grid td.label {
            width: 35%;
            background: #f9fafb;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        
        .info-grid td.value {
            width: 65%;
            color: #111827;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            background: #d1fae5;
            color: #065f46;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        
        .export-info {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .export-info p {
            margin: 5px 0;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>CUSTOMER DETAIL REPORT</h1>
        <p>{{ $customer->name }}</p>
    </div>

    <!-- Export Information -->
    <div class="export-info">
        <p><strong>Exported By:</strong> {{ $currentAdmin->name }} ({{ $currentAdmin->role_name }})</p>
        <p><strong>Export Date:</strong> {{ now()->format('d F Y, H:i:s') }}</p>
        <p><strong>Customer ID:</strong> #{{ str_pad($customer->id, 6, '0', STR_PAD_LEFT) }}</p>
    </div>

    <!-- Contact Information -->
    <div class="info-section">
        <div class="section-title">CONTACT INFORMATION</div>
        <table class="info-grid">
            <tr>
                <td class="label">Customer Name</td>
                <td class="value">{{ $customer->name }}</td>
            </tr>
            <tr>
                <td class="label">Email Address</td>
                <td class="value">{{ $customer->email }}</td>
            </tr>
            <tr>
                <td class="label">Phone Number</td>
                <td class="value">{{ $customer->phone ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Address Information -->
    <div class="info-section">
        <div class="section-title">ADDRESS INFORMATION</div>
        <table class="info-grid">
            <tr>
                <td class="label">Full Address</td>
                <td class="value">{{ $customer->address ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Province</td>
                <td class="value">{{ $customer->province ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">City</td>
                <td class="value">{{ $customer->city ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">District</td>
                <td class="value">{{ $customer->district ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Postal Code</td>
                <td class="value">{{ $customer->postal_code ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Account Information -->
    <div class="info-section">
        <div class="section-title">ACCOUNT INFORMATION</div>
        <table class="info-grid">
            <tr>
                <td class="label">Customer ID</td>
                <td class="value">#{{ str_pad($customer->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label">Account Status</td>
                <td class="value"><span class="status-badge">Active</span></td>
            </tr>
            <tr>
                <td class="label">Joined Date</td>
                <td class="value">{{ $customer->created_at->format('d F Y, H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">Last Updated</td>
                <td class="value">{{ $customer->updated_at->format('d F Y, H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This document is generated automatically by LGI STORE Admin System</p>
        <p>© {{ date('Y') }} LGI STORE - Produk Eksklusif Kaos Berkualitas</p>
        <p>Document ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>
