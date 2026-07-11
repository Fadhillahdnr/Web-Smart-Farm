<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorData extends Model
{
    protected $table = 'sensor_data';

    protected $fillable = [
        'soil_plot_id',
        'moisture',
        'ph',
        'color',
        'status',
        'battery'
    ];

    protected function casts(): array
    {
        return [
            'moisture' => 'integer',
            'ph' => 'float',
            'battery' => 'integer',
        ];
    }

    public function soilPlot(): BelongsTo
    {
        return $this->belongsTo(SoilPlot::class);
    }
}
