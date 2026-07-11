<?php

namespace App\Http\Controllers;

use App\Models\SoilPlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $soilPlots = $request->user()->soilPlots()->orderBy('name')->get();
        $selectedSoil = $this->selectedSoil($request, $soilPlots->first());
        $history = $selectedSoil
            ? $selectedSoil->sensorData()->latest()->limit(20)->get()
            : collect();

        return view('dashboard', compact('soilPlots', 'selectedSoil', 'history'));
    }

    public function latest(Request $request, SoilPlot $soilPlot): JsonResponse
    {
        $this->authorizeOwner($request, $soilPlot);
        $data = $soilPlot->sensorData()->latest()->first();

        return response()->json($data ?: [
            'id' => null,
            'moisture' => 0,
            'ph' => 0,
            'color' => '-',
            'status' => '-',
            'battery' => 0,
            'created_at' => null,
        ]);
    }

    public function history(Request $request, SoilPlot $soilPlot): JsonResponse
    {
        $this->authorizeOwner($request, $soilPlot);

        return response()->json(
            $soilPlot->sensorData()->latest()->limit(20)->get()
        );
    }

    private function selectedSoil(Request $request, ?SoilPlot $fallback): ?SoilPlot
    {
        if (! $request->filled('soil')) {
            return $fallback;
        }

        return $request->user()->soilPlots()->findOrFail($request->integer('soil'));
    }

    private function authorizeOwner(Request $request, SoilPlot $soilPlot): void
    {
        abort_unless($soilPlot->user_id === $request->user()->id, 404);
    }
}
