<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMovementExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Waktu', 'Produk', 'Varian', 'Jenis', 'Perubahan', 'Stok Sebelum', 'Stok Sesudah', 'Alasan', 'Referensi', 'Catatan'];
    }

    public function map($movement): array
    {
        $reference = '-';
        if ($movement->reference_type) {
            $reference = ($movement->reference_type === 'custom_order' ? 'Pesanan Custom #' : 'Pesanan #') . $movement->reference_id;
        }

        return [
            $movement->created_at->format('d/m/Y H:i'),
            $movement->product_name ?? '-',
            $movement->variant_label ?? '-',
            $movement->quantity_change >= 0 ? 'Masuk' : 'Keluar',
            $movement->quantity_change,
            $movement->stock_before,
            $movement->stock_after,
            $movement->reason_label,
            $reference,
            $movement->note ?? '-',
        ];
    }
}