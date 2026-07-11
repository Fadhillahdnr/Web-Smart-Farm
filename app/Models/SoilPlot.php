<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SoilPlot extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sensor_token', 'is_active'];

    protected $hidden = ['sensor_token'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        // Literal PostgreSQL diperlukan saat PDO emulated prepares digunakan.
        // Bentuk ini juga valid pada SQLite yang dipakai oleh test suite.
        return $query->whereRaw('is_active IS TRUE');
    }
}
