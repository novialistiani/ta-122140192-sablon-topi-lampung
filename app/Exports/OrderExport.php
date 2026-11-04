<?php

namespace App\Exports;

use App\Models\CustomDesignOrder;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $regular = Order::with('user')->get()->map(function ($order) {
            $order->order_type = 'regular';
            return $order;
        });

        $custom = CustomDesignOrder::with('user')->get()->map(function ($order) {
            $order->order_type = 'custom';
            return $order;
        });

        return $regular->concat($custom)->sortByDesc('created_at')->values();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jenis Pesanan',
            'Pelanggan',
            'Email',
            'Total',
            'Status',
            'Status Pembayaran',
            'Tanggal Pesanan',
            'Tanggal Disetujui',
            'Tenggat Pembayaran',
        ];
    }

    public function map($order): array
    {
        $isCustom = $order->order_type === 'custom';
        $total = $isCustom ? $order->total_price : $order->total;

        if ($isCustom) {
            $jenis = 'Custom Design';
        } else {
            $jenis = ($order->source ?? null) === 'pos' ? 'POS (Offline)' : 'Reguler';
        }

        return [
            ($isCustom ? 'CD-' : 'ORD-') . $order->id,
            $jenis,
            $order->user->name ?? 'Walk-in',
            $order->user->email ?? 'N/A',
            'Rp ' . number_format((float) $total, 0, ',', '.'),
            $order->status,
            $order->payment_status ?? 'unpaid',
            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : 'N/A',
            $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('Y-m-d H:i:s') : 'N/A',
            $order->payment_deadline ? \Carbon\Carbon::parse($order->payment_deadline)->format('Y-m-d H:i:s') : 'N/A',
        ];
    }
}