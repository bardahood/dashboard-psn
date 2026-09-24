<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KunjunganPerencanaan;
use Illuminate\Http\Request;

class KunjunganPerencanaanController extends Controller
{
    public function index(Request $request)
    {
        $query = KunjunganPerencanaan::query()->with(['psn', 'klaster', 'verifikator']);

        if ($request->filled('q')) {
            $query->where('nama_usulan_psn', 'like', '%'.$request->string('q').'%');
        }

        $daftarKunjungan = $query->orderByDesc('tanggal_kunjungan')->paginate(20)->withQueryString();

        return view('admin.kunjungan-perencanaan.index', compact('daftarKunjungan'));
    }

    public function create()
    {
        return view('admin.kunjungan-perencanaan.wizard', ['kunjungan' => null]);
    }

    public function edit(KunjunganPerencanaan $kunjungan)
    {
        return view('admin.kunjungan-perencanaan.wizard', compact('kunjungan'));
    }
}
