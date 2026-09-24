<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KunjunganPengendalianDokumentasi;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    private const KATEGORI = [
        'Foto Lapangan', 'Berita Acara', 'Dokumen Realisasi Anggaran',
        'Dokumen Teknis', 'Dokumen Regulasi', 'Lainnya',
    ];

    /**
     * Manajemen Dokumen (Tier 2) -- repositori lintas PSN atas seluruh bukti
     * dukung yang diunggah pada Instrumen Kunjungan Pengendalian (Bagian H),
     * tanpa tabel baru: hanya menyandingkan kunjungan_pengendalian_dokumentasi
     * dengan PSN & tanggal kunjungan pemiliknya agar bisa ditelusuri/difilter
     * lintas kunjungan, bukan terkubur di dalam masing-masing wizard.
     */
    public function index(Request $request)
    {
        $query = KunjunganPengendalianDokumentasi::query()
            ->with(['kunjungan.psn:id,nama_psn']);

        if ($request->filled('psn')) {
            $cari = $request->string('psn');
            $query->whereHas('kunjungan.psn', fn ($q) => $q->where('nama_psn', 'like', '%'.$cari.'%'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->string('kategori'));
        }

        $dokumen = $query->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.dokumen.index', [
            'dokumen' => $dokumen,
            'kategoriList' => self::KATEGORI,
        ]);
    }
}
