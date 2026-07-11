<?php

namespace App\Http\Controllers;

use App\Models\SoilPlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SoilPlotController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('soil_plots')->where('user_id', $request->user()->id),
            ],
        ]);

        $soilPlot = $request->user()->soilPlots()->create([
            'name' => $validated['name'],
            'sensor_token' => Str::random(48),
        ]);

        return to_route('dashboard', ['soil' => $soilPlot->id])
            ->with('success', "Tanah {$soilPlot->name} berhasil ditambahkan.");
    }

    public function update(Request $request, SoilPlot $soilPlot): RedirectResponse
    {
        $this->authorizeOwner($request, $soilPlot);
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('soil_plots')->where('user_id', $request->user()->id)->ignore($soilPlot),
            ],
        ]);

        $soilPlot->update(['name' => $validated['name']]);

        return to_route('dashboard', ['soil' => $soilPlot->id])->with('success', 'Nama tanah diperbarui.');
    }

    public function regenerateToken(Request $request, SoilPlot $soilPlot): RedirectResponse
    {
        $this->authorizeOwner($request, $soilPlot);
        $soilPlot->update(['sensor_token' => Str::random(48)]);

        return to_route('dashboard', ['soil' => $soilPlot->id])
            ->with('success', 'Token sensor baru dibuat. Perbarui token pada perangkat Anda.');
    }

    public function destroy(Request $request, SoilPlot $soilPlot): RedirectResponse
    {
        $this->authorizeOwner($request, $soilPlot);
        $name = $soilPlot->name;
        $soilPlot->delete();

        return to_route('dashboard')->with('success', "Tanah {$name} dan seluruh historinya dihapus.");
    }

    private function authorizeOwner(Request $request, SoilPlot $soilPlot): void
    {
        abort_unless($soilPlot->user_id === $request->user()->id, 404);
    }
}
