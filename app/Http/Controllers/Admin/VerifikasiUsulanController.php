<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KunjunganPerencanaan;
use App\Models\RefDokumenTeknis;
use Illuminate\Http\Request;

/**
 * GAP #5 (analisis KAK vs Dashboard PSN): KAK Bagian 3d meminta
 * "rekapitulasi kelengkapan administrasi dan hasil verifikasi penilaian
 * Pengusulan PSN". Data granularnya sudah ada per kunjungan lewat Instrumen
 * Kunjungan Perencanaan (verifikasiDokumenTeknis, skor & rekomendasi
 * otomatis) tapi terkubur di dalam wizard satu-per-satu -- halaman ini
 * menyatukannya lintas seluruh usulan, pola yang sama dengan
 * DokumenController untuk Instrumen Pengendalian.
 */
class VerifikasiUsulanController extends Controller
{
    public function index(Request $request)
    {
        $totalDokumen = RefDokumenTeknis::count();

        $query = KunjunganPerencanaan::query()
            ->with(['psn', 'klaster', 'verifikasiDokumenTeknis']);

        if ($request->filled('q')) {
            $cari = $request->string('q');
            $query->where(function ($q) use ($cari) {
                $q->where('nama_usulan_psn', 'like', '%'.$cari.'%')
                    ->orWhereHas('psn', fn ($p) => $p->where('nama_psn', 'like', '%'.$cari.'%'));
            });
        }

        $daftarUsulan = $query->orderByDesc('tanggal_kunjungan')->paginate(25)->withQueryString();

        $daftarUsulan->through(function (KunjunganPerencanaan $kunjungan) use ($totalDokumen) {
            $kunjungan->setAttribute('rekap_dokumen', [
                'ya' => $kunjungan->verifikasiDokumenTeknis->where('tersedia', 'Ya')->count(),
                'sebagian' => $kunjungan->verifikasiDokumenTeknis->where('tersedia', 'Sebagian')->count(),
                'tidak' => $kunjungan->verifikasiDokumenTeknis->where('tersedia', 'Tidak')->count(),
                'total' => $totalDokumen,
            ]);

            return $kunjungan;
        });

        return view('admin.verifikasi-usulan.index', compact('daftarUsulan'));
    }
}
