<?php

namespace App\Exports;

use App\Models\Admin;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $type;

    public function __construct($type = 'customer')
    {
        $this->type = $type;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if ($this->type === 'admin') {
            return Admin::orderBy('created_at', 'desc')->get();
        }

        return User::orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        if ($this->type === 'admin') {
            return [
                'ID',
                'Name',
                'Email',
                'Role',
                'Status',
                'Created At',
                'Last Login'
            ];
        }

        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Province',
            'City',
            'District',
            'Postal Code',
            'Gender',
            'Birth Date',
            'Email Verified',
            'Created At'
        ];
    }

    public function map($user): array
    {
        if ($this->type === 'admin') {
            return [
                $user->id,
                $user->name,
                $user->email,
                ucfirst(str_replace('_', ' ', $user->role)),
                ucfirst($user->status),
                $user->created_at->format('Y-m-d H:i:s'),
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'
            ];
        }

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '-',
            $user->address ?? '-',
            $user->province ?? '-',
            $user->city ?? '-',
            $user->district ?? '-',
            $user->postal_code ?? '-',
            $user->gender ? ucfirst($user->gender) : '-',
            $user->birth_date ?? '-',
            $user->email_verified_at ? 'Yes' : 'No',
            $user->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
