<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Support\Rkp2027CarryoverAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sandingkan lampiran "Daftar PSN dalam RKP 2027" dengan data PSN dashboard
 * (hasil impor Matrik Sandingan, bersumber dari RKP Pemutakhiran 2026) untuk
 * mengidentifikasi proyek carryover ke 2027, proyek yang perlu ditinjau
 * manual (redaksional berbeda/usulan baru), dan proyek yang tidak lagi
 * muncul di RKP 2027 (kandidat evaluasi keluar dari daftar PSN).
 */
class AnalisisRkp2027Controller extends Controller
{
    private function dokumenPath(): string
    {
        return database_path('seeders/data/Daftar_PSN_RKP_2027.docx');
    }

    public function index(Rkp2027CarryoverAnalyzer $analyzer)
    {
        $hasil = is_file($this->dokumenPath()) ? $analyzer->analisis($this->dokumenPath()) : null;

        return view('admin.analisis-rkp2027.index', [
            'hasil' => $hasil,
            'ambangSkor' => Rkp2027CarryoverAnalyzer::SKOR_AMBANG_COCOK,
            'jumlahBelumBerkategori' => $hasil ? $hasil['carryover']->pluck('psn_id')->filter()->unique()
                ->intersect(Psn::whereNull('kategori_usulan')->pluck('id'))->count() : 0,
        ]);
    }

    public function terapkan(Request $request, Rkp2027CarryoverAnalyzer $analyzer): RedirectResponse
    {
        if (! is_file($this->dokumenPath())) {
            return redirect()->route('admin.analisis-rkp2027')->with('error', 'Berkas Daftar PSN RKP 2027 tidak ditemukan.');
        }

        $hasil = $analyzer->analisis($this->dokumenPath());
        $jumlahKategori = $analyzer->terapkanKategoriCarryover($hasil['carryover']);
        $jumlahMatriks = $analyzer->terapkanKeMatriksSandingan($hasil);

        return redirect()->route('admin.analisis-rkp2027')
            ->with('status', "kategori_usulan='Carryover' diterapkan pada {$jumlahKategori} PSN, dan kolom RKP 2027 pada Matriks Sandingan diperbarui untuk {$jumlahMatriks} PSN.");
    }
}
