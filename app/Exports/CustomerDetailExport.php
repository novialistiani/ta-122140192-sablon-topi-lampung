<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerDetailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $customer;

    public function __construct(User $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([$this->customer]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Customer ID',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Province',
            'City',
            'District',
            'Postal Code',
            'Status',
            'Joined Date',
            'Last Updated',
        ];
    }

    /**
     * @param User $customer
     * @return array
     */
    public function map($customer): array
    {
        return [
            '#' . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
            $customer->name,
            $customer->email,
            $customer->phone ?? '-',
            $customer->address ?? '-',
            $customer->province ?? '-',
            $customer->city ?? '-',
            $customer->district ?? '-',
            $customer->postal_code ?? '-',
            'Active',
            $customer->created_at->format('d F Y, H:i:s'),
            $customer->updated_at->format('d F Y, H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '0a1d37',
                    ],
                ],
                'font' => [
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                    'bold' => true,
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Customer Detail';
    }
}
