<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BerandaController;
use App\Http\Controllers\Public\PsnPublicController;
use App\Http\Controllers\Public\StatistikController;
use Illuminate\Support\Facades\Route;

// ============ HALAMAN PUBLIK (tanpa login) ============
Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/psn', [PsnPublicController::class, 'index'])->name('psn.index');
Route::get('/psn/{psn}', [PsnPublicController::class, 'show'])->name('psn.show');
Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik');

// ============ AUTH (Breeze) ============
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
