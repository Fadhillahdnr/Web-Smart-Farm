<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SoilPlot;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'soil_token' => 'nullable|string|size:48',
            'moisture' => 'required|integer|between:0,100',
            'ph' => 'required|numeric|between:0,14',
            'color' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'battery' => 'required|integer|between:0,100',
        ]);

        $token = $request->header('X-Soil-Token') ?: ($validated['soil_token'] ?? null);
        $soilPlot = $token
            ? SoilPlot::where('sensor_token', $token)->first()
            : SoilPlot::active()->first();

        if (! $soilPlot) {
            return response()->json([
                'success' => false,
                'message' => $token
                    ? 'Token tanah tidak valid.'
                    : 'Belum ada tanah aktif. Pilih tanah dan tekan Mulai Rekam pada dashboard.',
            ], $token ? 401 : 409);
        }

        unset($validated['soil_token']);
        $data = $soilPlot->sensorData()->create($validated);

        return response()->json([
            'success' => true,
            'soil' => $soilPlot->name,
            'data' => $data,
        ], 201);
    }
}
