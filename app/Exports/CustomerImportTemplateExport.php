<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class CustomerImportTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'name', 'username', 'password', 'email', 'contact_number',
            'address', 'mac_address', 'expire_date', 'registered_at',
            'status', 'remarks', 'internet_plan', 'branch'
        ];
    }

    public function collection()
    {
        return new Collection([
            [
                'Manish Lama', 'manish', 'infitech', 'lamamanish234@gmail.com', '9801973210',
                'Nayapati', '', '2025-12-31', '2024-01-01',
                'active', 'Welcome customer', '100Mbps', 'Head Office'
            ]
        ]);
    }
}
