<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Order::with(['user', 'items'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Pelanggan',
            'Email',
            'Total',
            'Status',
            'Status Pembayaran',
            'Tanggal Pesanan',
            'Tanggal Disetujui',
            'Tenggat Pembayaran'
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->user->name ?? 'N/A',
            $order->user->email ?? 'N/A',
            'Rp ' . number_format((float)$order->total, 0, ',', '.'),
            $order->status,
            $order->payment_status,
            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : 'N/A',
            $order->approved_at ? $order->approved_at->format('Y-m-d H:i:s') : 'N/A',
            $order->payment_deadline ? $order->payment_deadline->format('Y-m-d H:i:s') : 'N/A'
        ];
    }
}