<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\SensorData;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        Log::info('DATA MASUK:', $request->all());

        $validated = $request->validate([
            'moisture' => 'required|numeric',
            'ph' => 'required|numeric',
            'color' => 'required|string',
            'status' => 'required|string',
            'battery' => 'required|numeric',
        ]);

        $data = SensorData::create($validated);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function latest()
    {
        $data = SensorData::latest()->first();

        return response()->json([
            'id' => $data->id ?? null,
            'moisture' => $data->moisture ?? 0,
            'ph' => $data->ph ?? 0,
            'color' => $data->color ?? '-',
            'status' => $data->status ?? '-',
            'battery' => $data->battery ?? 0,
            'created_at' => $data->created_at ?? null
        ]);
    }

    // 🔥 TAMBAHAN INI
    public function history()
    {
        $data = SensorData::latest()->limit(10)->get();

        return response()->json($data);
    }
}