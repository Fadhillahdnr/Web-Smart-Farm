<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SoilPlotController;
use App\Models\SoilPlot;
use App\Exports\SensorDataExport;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect awal ke login
Route::get('/', function () {
    return redirect('/login');
});

// Dashboard (WAJIB LOGIN)
// Profile (bawaan breeze)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/soil/{soilPlot}/latest', [DashboardController::class, 'latest'])->name('dashboard.latest');
    Route::get('/dashboard/soil/{soilPlot}/history', [DashboardController::class, 'history'])->name('dashboard.history');
    Route::get('/dashboard/soil/{soilPlot}/snapshot', [DashboardController::class, 'snapshot'])->name('dashboard.snapshot');

    Route::post('/soil-plots', [SoilPlotController::class, 'store'])->name('soil-plots.store');
    Route::patch('/soil-plots/{soilPlot}', [SoilPlotController::class, 'update'])->name('soil-plots.update');
    Route::patch('/soil-plots/{soilPlot}/token', [SoilPlotController::class, 'regenerateToken'])->name('soil-plots.token');
    Route::patch('/soil-plots/{soilPlot}/activate', [SoilPlotController::class, 'activate'])->name('soil-plots.activate');
    Route::patch('/soil-plots/{soilPlot}/deactivate', [SoilPlotController::class, 'deactivate'])->name('soil-plots.deactivate');
    Route::delete('/soil-plots/{soilPlot}', [SoilPlotController::class, 'destroy'])->name('soil-plots.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// OPTIONAL: fallback kalau ada /home
Route::get('/home', function () {
    return redirect('/dashboard');
});

Route::get('/soil-plots/{soilPlot}/export-excel', function (SoilPlot $soilPlot) {
    abort_unless($soilPlot->user_id === request()->user()->id, 404);

    $filename = 'data-sensor-'.str($soilPlot->name)->slug().'.xlsx';

    return Excel::download(new SensorDataExport($soilPlot), $filename);
})->middleware('auth')->name('soil-plots.export');

// Auth routes (login, register, dll)
require __DIR__.'/auth.php';
