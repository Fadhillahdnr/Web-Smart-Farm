<?php

namespace App\Exports;

use App\Models\SoilPlot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SensorDataExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly SoilPlot $soilPlot)
    {
    }

    public function collection()
    {
        return $this->soilPlot->sensorData()->select(
            'created_at',
            'moisture',
            'ph',
            'color',
            'battery',
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
            'Baterai (%)',
            'Status'
        ];
    }
}
