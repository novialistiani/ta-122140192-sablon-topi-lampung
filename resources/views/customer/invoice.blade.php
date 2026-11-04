<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 0; padding: 24px; background: #f3f4f6; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border: 1px solid #e5e7eb; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0a1d37; padding-bottom: 16px; margin-bottom: 24px; }
        .brand h1 { margin: 0; font-size: 22px; color: #0a1d37; }
        .brand p { margin: 2px 0; font-size: 12px; color: #6b7280; }
        .title { text-align: right; }
        .title h2 { margin: 0; font-size: 26px; letter-spacing: 2px; color: #0a1d37; }
        .title p { margin: 4px 0 0; font-size: 13px; }
        .meta { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 24px; font-size: 13px; }
        .meta h3 { margin: 0 0 6px; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        .meta p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0a1d37; color: #fff; text-align: left; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 15px; border-bottom: 0; border-top: 2px solid #0a1d37; }
        .badge { display: inline-block; padding: 3px 12px; border-radius: 9999px; background: #d1fae5; color: #065f46; font-weight: bold; font-size: 12px; }
        .note { margin-top: 28px; font-size: 12px; color: #6b7280; }
        .actions { max-width: 800px; margin: 0 auto 16px; display: flex; justify-content: space-between; }
        .btn { padding: 10px 20px; border: 0; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-print { background: #0a1d37; color: #fff; }
        .btn-back { background: #e5e7eb; color: #1f2937; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="actions no-print">
        <a href="{{ route('order-detail', ['type' => $type, 'id' => $order->id]) }}" class="btn btn-back">Kembali</a>
        <button type="button" class="btn btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="sheet">
        <div class="header">
            <div class="brand">
                <h1>LGI Store</h1>
                <p>Sablon Topi Lampung - Peduli Kualitas Bukan Kuantitas</p>
                <p>Jl. Ratu Dibalau No. 31, Jatimulyo, Lampung Selatan</p>
            </div>
            <div class="title">
                <h2>INVOICE</h2>
                <p>{{ $invoiceNumber }}</p>
                <p><span class="badge">LUNAS</span></p>
            </div>
        </div>

        <div class="meta">
            <div>
                <h3>Ditagihkan kepada</h3>
                <p><strong>{{ $user->name }}</strong></p>
                <p>{{ $user->email }}</p>
                @if(!empty($user->phone))
                    <p>{{ $user->phone }}</p>
                @endif
            </div>
            <div>
                <h3>Rincian Pesanan</h3>
                <p>Nomor pesanan: #{{ $order->id }}</p>
                <p>Jenis: {{ $type === 'custom' ? 'Custom Design' : 'Pesanan Reguler' }}</p>
                <p>Tanggal pesanan: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p>Tanggal pembayaran: {{ $order->paid_at ? \Carbon\Carbon::parse($order->paid_at)->format('d/m/Y H:i') : '-' }}</p>
                @if(!empty($order->pickup_date))
                    <p>Tanggal pengambilan: {{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }}</p>
                @endif
                <p>Metode pembayaran: QRIS</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Detail</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                <tr>
                    <td>{{ $line['name'] }}</td>
                    <td>{{ $line['detail'] }}</td>
                    <td class="right">{{ $line['quantity'] }}</td>
                    <td class="right">Rp {{ number_format($line['price'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="right">Total</td>
                    <td class="right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="note">
            Pesanan diambil langsung di toko sesuai tanggal pengambilan. Invoice ini dibuat otomatis oleh sistem dan sah tanpa tanda tangan.
        </p>
    </div>
</body>
</html>