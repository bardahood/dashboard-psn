<?php

use App\Http\Controllers\Admin\AnalisisRkp2027Controller;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DebottleneckingController;
use App\Http\Controllers\Admin\DokumenController;
use App\Http\Controllers\Admin\EvaluasiKeluarController;
use App\Http\Controllers\Admin\KunjunganPengendalianController;
use App\Http\Controllers\Admin\KunjunganPerencanaanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MatriksSandinganController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ProjectProfileController;
use App\Http\Controllers\Admin\PsnController;
use App\Http\Controllers\Admin\SinkronisasiPsiController;
use App\Http\Controllers\Admin\VerifikasiUsulanController;
use App\Models\Psn;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'akun.aktif'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('psn', PsnController::class);

    // Profil PSN disusun jadi 4 tab konsolidasi mengikuti struktur resmi
    // paparan "Update Project Profile": Gambaran Umum, Perencanaan,
    // Penjabaran, Upload Dokumen -- menggantikan tab-tab terpisah per
    // sub-resource sebelumnya. Dirender via komponen Livewire (lihat
    // app/Livewire/Admin), otorisasi ditegakkan di dalam masing-masing
    // komponen (Gate::authorize('update', $psn)).
    Route::get('/psn/{psn}/gambaran-umum', [PsnController::class, 'gambaranUmum'])->name('psn.gambaran-umum');
    Route::get('/psn/{psn}/perencanaan', fn (Psn $psn) => view('admin.psn.perencanaan', compact('psn')))->name('psn.perencanaan');
    Route::get('/psn/{psn}/penjabaran', fn (Psn $psn) => view('admin.psn.penjabaran', compact('psn')))->name('psn.penjabaran');
    Route::get('/psn/{psn}/dokumen', fn (Psn $psn) => view('admin.psn.dokumen', compact('psn')))->name('psn.dokumen');

    Route::get('/matriks-sandingan', [MatriksSandinganController::class, 'index'])->name('matriks-sandingan');

    Route::prefix('project-profile')->name('project-profile.')->group(function () {
        Route::get('/', [ProjectProfileController::class, 'index'])->name('index');
        Route::get('/export', [ProjectProfileController::class, 'export'])->name('export');
        Route::get('/{psn}', [ProjectProfileController::class, 'show'])->name('show');
    });

    Route::middleware('permission:pengendalian.manage')->prefix('kunjungan-pengendalian')->name('kunjungan-pengendalian.')->group(function () {
        Route::get('/', [KunjunganPengendalianController::class, 'index'])->name('index');
        Route::get('/create', [KunjunganPengendalianController::class, 'create'])->name('create');
        Route::get('/{kunjungan}/edit', [KunjunganPengendalianController::class, 'edit'])->name('edit');
    });

    Route::middleware('permission:pengendalian.manage')->get('/debottlenecking', [DebottleneckingController::class, 'index'])->name('debottlenecking');

    Route::middleware('permission:perencanaan.manage')->prefix('kunjungan-perencanaan')->name('kunjungan-perencanaan.')->group(function () {
        Route::get('/', [KunjunganPerencanaanController::class, 'index'])->name('index');
        Route::get('/create', [KunjunganPerencanaanController::class, 'create'])->name('create');
        Route::get('/{kunjungan}/edit', [KunjunganPerencanaanController::class, 'edit'])->name('edit');
    });

    Route::middleware('permission:perencanaan.manage')->get('/verifikasi-usulan', [VerifikasiUsulanController::class, 'index'])->name('verifikasi-usulan');

    Route::middleware('permission:profil.manage')->get('/evaluasi-keluar', [EvaluasiKeluarController::class, 'index'])->name('evaluasi-keluar');

    Route::middleware('permission:profil.manage')->prefix('analisis-rkp2027')->name('analisis-rkp2027')->group(function () {
        Route::get('/', [AnalisisRkp2027Controller::class, 'index'])->name('');
        Route::post('/terapkan', [AnalisisRkp2027Controller::class, 'terapkan'])->name('.terapkan');
    });

    Route::middleware('permission:pengguna.manage')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
        Route::get('/create', [PenggunaController::class, 'create'])->name('create');
        Route::post('/', [PenggunaController::class, 'store'])->name('store');
        Route::get('/{pengguna}/edit', [PenggunaController::class, 'edit'])->name('edit');
        Route::put('/{pengguna}', [PenggunaController::class, 'update'])->name('update');
    });

    Route::middleware('permission:audit.view')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log');
        Route::get('/audit-log/{auditLog}', [AuditLogController::class, 'show'])->name('audit-log.show');
    });

    Route::middleware('permission:pengendalian.manage')->get('/dokumen', [DokumenController::class, 'index'])->name('dokumen');

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
