<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KunjunganPengendalianController;
use App\Http\Controllers\Admin\KunjunganPerencanaanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MatriksSandinganController;
use App\Http\Controllers\Admin\PsnController;
use App\Http\Controllers\Admin\SinkronisasiPsiController;
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

    Route::middleware('permission:pengendalian.manage')->prefix('kunjungan-pengendalian')->name('kunjungan-pengendalian.')->group(function () {
        Route::get('/', [KunjunganPengendalianController::class, 'index'])->name('index');
        Route::get('/create', [KunjunganPengendalianController::class, 'create'])->name('create');
        Route::get('/{kunjungan}/edit', [KunjunganPengendalianController::class, 'edit'])->name('edit');
    });

    Route::middleware('permission:perencanaan.manage')->prefix('kunjungan-perencanaan')->name('kunjungan-perencanaan.')->group(function () {
        Route::get('/', [KunjunganPerencanaanController::class, 'index'])->name('index');
        Route::get('/create', [KunjunganPerencanaanController::class, 'create'])->name('create');
        Route::get('/{kunjungan}/edit', [KunjunganPerencanaanController::class, 'edit'])->name('edit');
    });

    Route::middleware('permission:audit.view')->get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log');

    Route::middleware('permission:sinkronisasi.manage')->prefix('sinkronisasi-psi')->group(function () {
        Route::get('/', [SinkronisasiPsiController::class, 'index'])->name('sinkronisasi-psi');
        Route::post('/trigger', [SinkronisasiPsiController::class, 'trigger'])->name('sinkronisasi-psi.trigger');
    });

    Route::middleware('permission:laporan.export')->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/ringkasan-pdf', [LaporanController::class, 'ringkasanPdf'])->name('ringkasan-pdf');
        Route::get('/matriks-excel', [LaporanController::class, 'matriksExcel'])->name('matriks-excel');
        Route::get('/daftar-psn-excel', [LaporanController::class, 'daftarPsnExcel'])->name('daftar-psn-excel');
    });
});

// Breeze redirects ke route('dashboard') setelah login/registrasi -- alias ke Executive Dashboard admin.
Route::redirect('/dashboard', '/admin')->middleware('auth')->name('dashboard');
