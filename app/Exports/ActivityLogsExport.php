<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $userType;

    public function __construct($userType = null)
    {
        $this->userType = $userType;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = ActivityLog::with(['user'])
            ->orderBy('created_at', 'desc');

        if ($this->userType) {
            $query->where('user_type', $this->userType);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'User Type',
            'User Name',
            'User Email',
            'Action',
            'Description',
            'IP Address',
            'Date & Time'
        ];
    }

    public function map($log): array
    {
        // Get user type display name
        $userTypeDisplay = 'System';
        if ($log->user_type === 'App\Models\Admin') {
            $userTypeDisplay = 'Admin';
        } elseif ($log->user_type === 'App\Models\User') {
            $userTypeDisplay = 'Customer';
        }

        // Get user name and email using polymorphic relationship
        $userName = 'N/A';
        $userEmail = 'N/A';
        
        if ($log->user) {
            $userName = $log->user->name ?? 'N/A';
            $userEmail = $log->user->email ?? 'N/A';
        }

        return [
            $log->id,
            $userTypeDisplay,
            $userName,
            $userEmail,
            ucfirst($log->action),
            $log->description,
            $log->ip_address ?? 'N/A',
            $log->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
