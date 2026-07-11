<?php

namespace App\Http\Controllers;

use App\Models\SoilPlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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

    public function activate(Request $request, SoilPlot $soilPlot): RedirectResponse
    {
        $this->authorizeOwner($request, $soilPlot);

        DB::transaction(function () use ($soilPlot): void {
            // ESP32 lama tidak membawa identitas perangkat. Karena itu hanya
            // boleh ada satu tujuan rekaman tanpa token di seluruh sistem.
            SoilPlot::query()->lockForUpdate()->get(['id']);
            SoilPlot::active()->update(['is_active' => DB::raw('FALSE')]);
            SoilPlot::query()->whereKey($soilPlot->id)->update(['is_active' => DB::raw('TRUE')]);
        });

        return to_route('dashboard', ['soil' => $soilPlot->id])
            ->with('success', "Rekaman dimulai. Data ESP32 sekarang masuk ke {$soilPlot->name}.");
    }

    public function deactivate(Request $request, SoilPlot $soilPlot): RedirectResponse
    {
        $this->authorizeOwner($request, $soilPlot);
        SoilPlot::query()->whereKey($soilPlot->id)->update(['is_active' => DB::raw('FALSE')]);

        return to_route('dashboard', ['soil' => $soilPlot->id])
            ->with('success', 'Rekaman dihentikan. Data ESP32 tanpa token tidak akan disimpan.');
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
