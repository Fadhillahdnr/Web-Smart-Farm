<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Models\SensorData;
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
Route::get('/dashboard', function () {
    $latest = SensorData::latest()->first();
    $all = SensorData::latest()->take(20)->get()->reverse();
    return view('dashboard', compact('latest','all'));
})->middleware(['auth'])->name('dashboard');

// Profile (bawaan breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// OPTIONAL: fallback kalau ada /home
Route::get('/home', function () {
    return redirect('/dashboard');
});

Route::get('/export-excel', function () {
    return Excel::download(new SensorDataExport, 'data-sensor.xlsx');
})->middleware('auth');

// Auth routes (login, register, dll)
require __DIR__.'/auth.php';