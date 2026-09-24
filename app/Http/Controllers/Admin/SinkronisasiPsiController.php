<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncLogPsi;
use App\Services\PsiSyncService;

class SinkronisasiPsiController extends Controller
{
    public function index()
    {
        $riwayat = SyncLogPsi::orderByDesc('tanggal_sync')->paginate(20);

        return view('admin.sinkronisasi-psi.index', compact('riwayat'));
    }

    public function trigger(PsiSyncService $service)
    {
        $hasil = $service->sync();

        $pesan = $hasil['status'] === 'Sukses'
            ? "Sinkronisasi berhasil: {$hasil['jumlah']} PSN diterima."
            : 'Sinkronisasi belum berhasil: '.$hasil['catatan'];

        return redirect()->route('admin.sinkronisasi-psi')->with(
            $hasil['status'] === 'Sukses' ? 'status' : 'error',
            $pesan
        );
    }
}
