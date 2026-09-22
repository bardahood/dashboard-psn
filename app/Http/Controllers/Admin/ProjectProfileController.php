<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\RefStatusPsn;
use Illuminate\Http\Request;

/**
 * Halaman "Project Profile" -- rekap baca-saja (read-only) seluruh muatan
 * profil PSN, disusun mengikuti struktur resmi paparan "Update Project
 * Profile" (Perencanaan vs Penjabaran Tahunan, lihat slide "Struktur Project
 * Profile"). Berbeda dari tab-tab admin.psn.* yang berorientasi
 * pengisian/edit satu bagian per satu bagian, halaman ini menyatukan seluruh
 * bagian jadi satu dokumen agar mudah ditinjau/diekspor sekaligus (matriks
 * untuk semua PSN, detail lengkap per PSN).
 */
class ProjectProfileController extends Controller
{
    /** Komponen "Perencanaan" pada Struktur Project Profile, dipakai untuk skor kelengkapan matriks. */
    private const KOMPONEN_PERENCANAAN = ['gambaran_umum', 'ro_proyek', 'risiko', 'indikator', 'trisula_kontribusi', 'penerima_manfaat'];

    /** Komponen "Penjabaran Tahunan", dipakai untuk skor kelengkapan matriks. */
    private const KOMPONEN_PENJABARAN = ['ro_penjabaran_tahun_ini', 'trisula_tw_tahun_ini', 'isu_lainnya', 'evaluasi_status_tahun_ini'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', Psn::class);

        $tahunIni = now()->year;

        $query = Psn::query()
            ->with(['klaster', 'provinsi', 'statusPsn'])
            ->withCount([
                'roProyek',
                'risiko',
                'indikator',
                'trisulaKontribusi',
                'penerimaManfaat',
                'isuLainnya',
                'roProyek as ro_penjabaran_tahun_ini_count' => fn ($q) => $q->whereHas(
                    'targetPeriode',
                    fn ($qq) => $qq->where('tahun', $tahunIni)->whereIn('tipe_periode', ['BULANAN', 'TRIWULANAN'])
                ),
                'trisulaKontribusi as trisula_tw_tahun_ini_count' => fn ($q) => $q->whereHas(
                    'targetPeriode',
                    fn ($qq) => $qq->where('tahun', $tahunIni)->where('tipe_periode', 'TRIWULANAN')
                ),
                'evaluasiStatus as evaluasi_status_tahun_ini_count' => fn ($q) => $q->where('tahun_evaluasi', $tahunIni),
            ]);

        if ($request->filled('klaster_id')) {
            $query->where('klaster_id', $request->integer('klaster_id'));
        }
        if ($request->filled('status_psn_id')) {
            $query->where('status_psn_id', $request->integer('status_psn_id'));
        }
        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', $request->integer('provinsi_id'));
        }
        if ($request->filled('tipe_hierarki')) {
            $query->where('tipe_hierarki', $request->string('tipe_hierarki'));
        }
        if ($request->filled('q')) {
            $query->whereFullText('nama_psn', $request->string('q'));
        }

        $daftarPsn = $query->orderBy('nama_psn')->paginate(20)->withQueryString();

        // Skor kelengkapan dihitung di controller (bukan view) supaya definisi
        // "lengkap" konsisten dengan yang dipakai pada halaman detail per-PSN.
        $daftarPsn->getCollection()->transform(function (Psn $psn) {
            $psn->skor_perencanaan = collect([
                (bool) ($psn->tujuan_utama && $psn->output_akhir),
                $psn->ro_proyek_count > 0,
                $psn->risiko_count > 0,
                $psn->indikator_count > 0,
                $psn->trisula_kontribusi_count > 0,
                $psn->penerima_manfaat_count > 0,
            ])->filter()->count();

            $psn->skor_penjabaran = collect([
                $psn->trisula_tw_tahun_ini_count > 0,
                $psn->ro_penjabaran_tahun_ini_count > 0,
                $psn->isu_lainnya_count > 0,
                $psn->evaluasi_status_tahun_ini_count > 0,
            ])->filter()->count();

            return $psn;
        });

        return view('admin.project-profile.index', [
            'daftarPsn' => $daftarPsn,
            'klasterOptions' => RefKlaster::orderBy('nama_klaster')->get(),
            'statusOptions' => RefStatusPsn::orderBy('urutan')->get(),
            'provinsiOptions' => RefProvinsi::orderBy('nama_provinsi')->get(),
            'totalKomponenPerencanaan' => count(self::KOMPONEN_PERENCANAAN),
            'totalKomponenPenjabaran' => count(self::KOMPONEN_PENJABARAN),
        ]);
    }

    public function show(Request $request, Psn $psn)
    {
        $this->authorize('view', $psn);

        $psn->load([
            'klaster', 'provinsi', 'statusPsn',
            'pengusulInstansi', 'pengelolaInstansi', 'kontraktorInstansi', 'supervisiInstansi',
            'dasarHukum',
            'stakeholder',
            'indikator.targetTahunan',
            'penerimaManfaat.targetTahunan',
            'trisulaKontribusi.targetPeriode',
            'kebutuhanRegulasi.penanggungJawab',
            'risiko.penanggungJawab',
            'risiko.pelaksanaPerlakuan',
            'risiko.ro',
            'risiko.statusPeriode',
            'roProyek.anak.targetPeriode',
            'roProyek.instansiPelaksana',
            'roProyek.targetPeriode',
            'isuLainnya',
            'evaluasiStatus',
        ]);

        $roIndukList = $psn->roProyek->whereNull('ro_induk_id')->values();

        $tahunPenjabaran = $request->integer('tahun') ?: now()->year;
        $tahunOptions = range(2025, 2030);
        $tahunGrid = range(2025, 2030);

        return view('admin.project-profile.show', compact('psn', 'roIndukList', 'tahunPenjabaran', 'tahunOptions', 'tahunGrid'));
    }
}
