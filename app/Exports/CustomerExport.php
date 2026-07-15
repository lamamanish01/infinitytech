<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        // Eager-load the relationships so we can access them in map()
        return Customer::with(['internetPlan', 'branch']);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Username',
            'Email',
            'Contact Number',
            'Address',
            'MAC Address',
            'Expire Date',
            'Registered At',
            'Status',
            'Remarks',
            'Internet Plan',   // from relation
            'Branch'           // from relation
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->username,
            $customer->email,
            $customer->contact_number,
            $customer->address,
            $customer->mac_address,
            $customer->expire_date ? $customer->expire_date->format('Y-m-d') : '',
            $customer->registered_at ? $customer->registered_at->format('Y-m-d') : '',
            $customer->status,
            $customer->remarks,
            $customer->internetPlan->name ?? '',
            $customer->branch->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
