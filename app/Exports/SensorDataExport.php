<?php

namespace App\Exports;

use App\Models\SensorData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SensorDataExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SensorData::select(
            'created_at',
            'moisture',
            'ph',
            'color',
            'status'
        )->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Waktu',
            'Soil (%)',
            'pH',
            'Warna Tanah',
            'Status'
        ];
    }
}