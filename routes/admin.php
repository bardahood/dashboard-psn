<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MatriksSandinganController;
use App\Http\Controllers\Admin\PsnController;
use App\Models\Psn;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('psn', PsnController::class);

    // Sub-profil PSN: dirender via komponen Livewire (lihat app/Livewire/Admin),
    // otorisasi ditegakkan di dalam masing-masing komponen (Gate::authorize('update', $psn)).
    Route::get('/psn/{psn}/ro', fn (Psn $psn) => view('admin.psn.ro', compact('psn')))->name('psn.ro');
    Route::get('/psn/{psn}/risiko', fn (Psn $psn) => view('admin.psn.risiko', compact('psn')))->name('psn.risiko');
    Route::get('/psn/{psn}/capaian/{type}', fn (Psn $psn, string $type) => view('admin.psn.capaian', compact('psn', 'type')))->name('psn.capaian');
    Route::get('/psn/{psn}/profil/{type}', fn (Psn $psn, string $type) => view('admin.psn.profil', compact('psn', 'type')))->name('psn.profil');

    Route::get('/matriks-sandingan', [MatriksSandinganController::class, 'index'])->name('matriks-sandingan');
});

// Breeze redirects ke route('dashboard') setelah login/registrasi -- alias ke Executive Dashboard admin.
Route::redirect('/dashboard', '/admin')->middleware('auth')->name('dashboard');
