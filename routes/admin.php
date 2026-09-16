<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MatriksSandinganController;
use App\Http\Controllers\Admin\PsnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('psn', PsnController::class);

    Route::get('/matriks-sandingan', [MatriksSandinganController::class, 'index'])->name('matriks-sandingan');
});

// Breeze redirects ke route('dashboard') setelah login/registrasi -- alias ke Executive Dashboard admin.
Route::redirect('/dashboard', '/admin')->middleware('auth')->name('dashboard');
