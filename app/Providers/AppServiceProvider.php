<?php

namespace App\Providers;

use App\Models\CatatanMonev;
use App\Models\DasarHukumPsn;
use App\Models\HakAkses;
use App\Models\IndikatorPsn;
use App\Models\IndikatorPsnTargetTahunan;
use App\Models\InfoMemo;
use App\Models\KebutuhanRegulasi;
use App\Models\KunjunganIndeksBukti;
use App\Models\KunjunganPengendalian;
use App\Models\KunjunganPengendalianAnggaran;
use App\Models\KunjunganPengendalianDokumentasi;
use App\Models\KunjunganPengendalianFisik;
use App\Models\KunjunganPengendalianKelembagaan;
use App\Models\KunjunganPengendalianRegulasi;
use App\Models\KunjunganPengendalianRisiko;
use App\Models\KunjunganPerencanaan;
use App\Models\KunjunganVerifikasiDokumenTeknis;
use App\Models\KunjunganVerifikasiKriteria;
use App\Models\KunjunganVerifikasiLokasi;
use App\Models\KunjunganVerifikasiTrisula;
use App\Models\PenerimaManfaatPsn;
use App\Models\PenerimaManfaatTargetTahunan;
use App\Models\Psn;
use App\Models\PsnEvaluasiStatus;
use App\Models\PsnIsuLainnya;
use App\Models\RefPic;
use App\Models\RisikoPsn;
use App\Models\RisikoStatusPeriode;
use App\Models\RoProyek;
use App\Models\RoTargetPeriode;
use App\Models\StakeholderPsn;
use App\Models\TrisulaKontribusiPsn;
use App\Models\TrisulaTargetPeriode;
use App\Models\User;
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
        // Jejak audit tabel inti (Bagian 8 prompt pengembangan) -- didaftarkan
        // untuk SELURUH model yang ditulis lewat Controller/Livewire admin
        // (bukan hanya entitas induk), supaya "data apa yang berubah" benar-benar
        // terlacak sampai ke tabel anak (mis. target periode, sub-resource
        // profil PSN), bukan hanya perubahan pada baris induknya. Model
        // pipeline/import massal (mis. MatriksSandinganImporter, seeder) SENGAJA
        // tidak diaudit di sini -- itu bukan aksi CRUD pengguna, dan menulis
        // lewat query builder mass update yang tidak memicu event Eloquent.
        foreach ($this->modelDiaudit() as $model) {
            $model::observe(AuditLogObserver::class);
        }
    }

    /**
     * @return array<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    protected function modelDiaudit(): array
    {
        return [
            // Profil PSN & seluruh sub-resource-nya
            Psn::class,
            DasarHukumPsn::class,
            StakeholderPsn::class,
            IndikatorPsn::class,
            IndikatorPsnTargetTahunan::class,
            PenerimaManfaatPsn::class,
            PenerimaManfaatTargetTahunan::class,
            TrisulaKontribusiPsn::class,
            TrisulaTargetPeriode::class,
            PsnIsuLainnya::class,
            PsnEvaluasiStatus::class,
            RoProyek::class,
            RoTargetPeriode::class,
            RisikoPsn::class,
            RisikoStatusPeriode::class,
            KebutuhanRegulasi::class,
            InfoMemo::class,
            CatatanMonev::class,
            // Instrumen Kunjungan Pengendalian & Perencanaan
            KunjunganPengendalian::class,
            KunjunganPengendalianKelembagaan::class,
            KunjunganPengendalianFisik::class,
            KunjunganPengendalianAnggaran::class,
            KunjunganPengendalianRisiko::class,
            KunjunganPengendalianRegulasi::class,
            KunjunganPengendalianDokumentasi::class,
            KunjunganPerencanaan::class,
            KunjunganVerifikasiKriteria::class,
            KunjunganVerifikasiLokasi::class,
            KunjunganVerifikasiDokumenTeknis::class,
            KunjunganVerifikasiTrisula::class,
            KunjunganIndeksBukti::class,
            // Manajemen Pengguna & Hak Akses
            User::class,
            RefPic::class,
            HakAkses::class,
        ];
    }
}
