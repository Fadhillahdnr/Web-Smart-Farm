<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $table = 'sensor_data';

    protected $fillable = [
        'moisture',
        'ph',
        'color',
        'status',
        'battery'
    ];
}