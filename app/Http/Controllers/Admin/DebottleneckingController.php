<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KebutuhanRegulasi;
use App\Models\KunjunganPengendalian;
use App\Models\KunjunganPengendalianRisiko;
use App\Models\RefKlaster;
use App\Models\RisikoPsn;
use Illuminate\Http\Request;

/**
 * GAP #3 (analisis KAK vs Dashboard PSN): KAK Bagian 2b-2c meminta
 * "identifikasi dan analisis permasalahan pelaksanaan PSN ... termasuk
 * faktor penghambat, isu dan risiko" serta "rekomendasi percepatan
 * penyelesaian dan ringkasan hasil debottlenecking ... termasuk
 * identifikasi dan perlakuan risiko" untuk klaster tertentu per periode
 * laporan (mis. Konektivitas, Industri Hilirisasi, Sumber Daya Air pada
 * Laporan Interim).
 *
 * Datanya sudah ada sejak awal, tersebar di 3 sumber: register risiko
 * (risiko_psn + pelaporan triwulanan risiko_status_periode), kebutuhan
 * regulasi (kebutuhan_regulasi + verifikasi lapangan
 * kunjungan_pengendalian_regulasi), dan isu/tindak lanjut hasil Instrumen
 * Kunjungan Pengendalian. Controller ini menyatukan ketiganya lintas PSN,
 * bisa disaring per klaster fokus periode laporan -- tanpa tabel baru.
 */
class DebottleneckingController extends Controller
{
    public function index(Request $request)
    {
        $klasterIds = array_filter((array) $request->input('klaster_id', []));

        $risiko = RisikoPsn::query()
            ->with(['psn.klaster', 'statusPeriode'])
            ->whereHas('psn', fn ($q) => $klasterIds ? $q->whereIn('klaster_id', $klasterIds) : $q)
            ->get()
            ->map(function (RisikoPsn $r) {
                $terkini = $r->statusPeriode->sortByDesc(fn ($s) => $s->tahun * 10 + $s->triwulan)->first();
                $r->setAttribute('status_terkini', $terkini);

                return $r;
            })
            ->sortByDesc(fn (RisikoPsn $r) => KunjunganPengendalianRisiko::LEVEL_RANK[$r->level_risiko_awal] ?? 0)
            ->values();

        $regulasi = KebutuhanRegulasi::query()
            ->with(['psn.klaster', 'penanggungJawab', 'kunjunganPengendalianRegulasi.kunjungan'])
            ->whereHas('psn', fn ($q) => $klasterIds ? $q->whereIn('klaster_id', $klasterIds) : $q)
            ->get()
            ->map(function (KebutuhanRegulasi $r) {
                $terkini = $r->kunjunganPengendalianRegulasi
                    ->sortByDesc(fn ($v) => $v->kunjungan?->tanggal_kunjungan)
                    ->first();
                $r->setAttribute('status_terkini', $terkini?->status_klaim);
                $r->setAttribute('terlambat', $r->target_tahun_penyelesaian
                    && $r->target_tahun_penyelesaian < now()->year
                    && $terkini?->status_klaim !== 'Selesai');

                return $r;
            })
            ->sortByDesc('terlambat')
            ->values();

        $isuTerkini = KunjunganPengendalian::query()
            ->with('psn.klaster')
            ->whereNotNull('isu_tantangan')
            ->whereHas('psn', fn ($q) => $klasterIds ? $q->whereIn('klaster_id', $klasterIds) : $q)
            ->get()
            ->sortByDesc('tanggal_kunjungan')
            ->unique('psn_id')
            ->values();

        $klasterOptions = RefKlaster::orderBy('nama_klaster')->get();

        return view('admin.debottlenecking.index', compact('risiko', 'regulasi', 'isuTerkini', 'klasterOptions', 'klasterIds'));
    }
}
