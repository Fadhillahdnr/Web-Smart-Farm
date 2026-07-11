<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoilPlot extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sensor_token'];

    protected $hidden = ['sensor_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }
}
