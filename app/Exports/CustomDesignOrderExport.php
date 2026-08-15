<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\CustomDesignOrder;

class CustomDesignOrderExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return CustomDesignOrder::with(['user', 'product'])->get();
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'ID',
            'Produk',
            'ID Pesanan',
            'Tanggal',
            'Nama Pelanggan',
            'Status',
            'Jumlah',
        ];
    }

    /**
     * Map each row to specific columns
     */
    public function map($order): array
    {
        return [
            $order->id,
            $order->product_name ?? '-',
            '#' . $order->id,
            $order->created_at->format('M d, Y'),
            $order->user->name ?? '-',
            ucfirst($order->status),
            'Rp. ' . number_format($order->total_price ?? 0, 0, ',', '.'),
        ];
    }
}