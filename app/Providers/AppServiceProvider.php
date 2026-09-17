<?php

namespace App\Providers;

use App\Models\KebutuhanRegulasi;
use App\Models\KunjunganPengendalian;
use App\Models\KunjunganPerencanaan;
use App\Models\Psn;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use App\Observers\AuditLogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Jejak audit tabel inti (Bagian 8 prompt pengembangan).
        Psn::observe(AuditLogObserver::class);
        RoProyek::observe(AuditLogObserver::class);
        RisikoPsn::observe(AuditLogObserver::class);
        KebutuhanRegulasi::observe(AuditLogObserver::class);
        KunjunganPengendalian::observe(AuditLogObserver::class);
        KunjunganPerencanaan::observe(AuditLogObserver::class);
    }
}
