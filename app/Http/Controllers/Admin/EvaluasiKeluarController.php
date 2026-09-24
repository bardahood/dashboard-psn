<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PsnEvaluasiStatus;
use Illuminate\Http\Request;

/**
 * GAP #4 (analisis KAK vs Dashboard PSN): KAK Bagian 3c meminta "ringkasan
 * eksekutif dan rekomendasi ... untuk PSN carry-over yang dikeluarkan dari
 * daftar". Mekanismenya sudah ada di tabel psn_evaluasi_status
 * (masih_butuh_status_psn=false + justifikasi, diisi lewat SubResourceManager
 * "Kebutuhan Status PSN Tahun Selanjutnya" pada profil tiap PSN) -- yang belum ada hanyalah
 * rekapitulasi lintas-PSN-nya. Halaman ini murni menyaring data yang sudah
 * ada, tanpa tabel baru.
 */
class EvaluasiKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = PsnEvaluasiStatus::query()
            ->where('masih_butuh_status_psn', false)
            ->with('psn.klaster');

        if ($request->filled('tahun_evaluasi')) {
            $query->where('tahun_evaluasi', $request->integer('tahun_evaluasi'));
        }

        $daftarRekomendasi = $query->orderByDesc('tahun_evaluasi')->orderBy('psn_id')->paginate(25)->withQueryString();

        $tahunOptions = PsnEvaluasiStatus::where('masih_butuh_status_psn', false)
            ->distinct()
            ->orderByDesc('tahun_evaluasi')
            ->pluck('tahun_evaluasi');

        return view('admin.evaluasi-keluar.index', compact('daftarRekomendasi', 'tahunOptions'));
    }
}
