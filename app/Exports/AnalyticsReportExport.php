<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsReportExport implements FromArray, WithStyles, WithTitle
{
    protected $periodLabel;
    protected $salesOverview;
    protected $funnelData;
    protected $rfmData;

    /** @var int[] Baris (1-indexed) yang perlu ditebalkan sebagai judul bagian */
    protected $sectionHeaderRows = [];

    public function __construct(string $periodLabel, array $salesOverview, array $funnelData, array $rfmData)
    {
        $this->periodLabel = $periodLabel;
        $this->salesOverview = $salesOverview;
        $this->funnelData = $funnelData;
        $this->rfmData = $rfmData;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $rows = [];

        $rows[] = ['Laporan Analytics - LGI Store'];
        $rows[] = ['Periode: ' . $this->periodLabel];
        $rows[] = [];

        // --- Ringkasan Penjualan ---
        $this->sectionHeaderRows[] = count($rows) + 1;
        $rows[] = ['RINGKASAN PENJUALAN'];
        $rows[] = ['Total Revenue', 'Rp ' . number_format($this->salesOverview['totalRevenue'], 0, ',', '.')];
        $rows[] = ['Completed Orders', $this->salesOverview['completedOrders']];
        $rows[] = ['Total Orders', $this->salesOverview['totalOrders']];
        $rows[] = ['Conversion Rate', $this->salesOverview['conversionRate'] . '%'];
        $rows[] = ['Average Order Value', 'Rp ' . number_format($this->salesOverview['averageOrderValue'], 0, ',', '.')];
        $rows[] = ['Revenue Growth', $this->salesOverview['revenueGrowth'] . '%'];
        $rows[] = [];

        // --- Conversion Funnel ---
        $this->sectionHeaderRows[] = count($rows) + 1;
        $rows[] = ['CONVERSION FUNNEL'];
        $rows[] = ['Tahap', 'Jumlah', 'Persentase'];
        foreach ($this->funnelData as $stage) {
            $rows[] = [$stage['stage'], $stage['count'], $stage['rate'] . '%'];
        }
        $rows[] = [];

        // --- Top Customers (RFM) ---
        $this->sectionHeaderRows[] = count($rows) + 1;
        $rows[] = ['TOP CUSTOMERS (RFM)'];
        $rows[] = ['Rank', 'Customer', 'Email', 'Recency (Hari)', 'Frequency', 'Monetary', 'Segment'];
        foreach ($this->rfmData as $idx => $c) {
            $rows[] = [
                '#' . ($idx + 1),
                $c['customer'],
                $c['email'],
                round($c['recency'], 0),
                $c['frequency'],
                'Rp ' . number_format($c['monetary'], 0, ',', '.'),
                $c['segment'] ?? '-',
            ];
        }

        return $rows;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Judul utama
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Judul tiap bagian (Ringkasan Penjualan / Conversion Funnel / Top Customers)
        foreach ($this->sectionHeaderRows as $rowNumber) {
            $sheet->getStyle('A' . $rowNumber)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $rowNumber)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('0a1d37');
            $sheet->getStyle('A' . $rowNumber)->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Analytics Report';
    }
}