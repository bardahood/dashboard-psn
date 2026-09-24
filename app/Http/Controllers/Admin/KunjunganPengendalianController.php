<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KunjunganPengendalian;
use App\Models\Psn;
use Illuminate\Http\Request;

class KunjunganPengendalianController extends Controller
{
    public function index(Request $request)
    {
        $query = KunjunganPengendalian::query()->with(['psn', 'verifikator']);

        if ($request->filled('psn_id')) {
            $query->where('psn_id', $request->integer('psn_id'));
        }

        $daftarKunjungan = $query->orderByDesc('tanggal_kunjungan')->paginate(20)->withQueryString();
        $psnTerpilih = $request->filled('psn_id') ? Psn::find($request->integer('psn_id')) : null;

        return view('admin.kunjungan-pengendalian.index', compact('daftarKunjungan', 'psnTerpilih'));
    }

    public function create()
    {
        return view('admin.kunjungan-pengendalian.wizard', ['kunjungan' => null]);
    }

    public function edit(KunjunganPengendalian $kunjungan)
    {
        return view('admin.kunjungan-pengendalian.wizard', compact('kunjungan'));
    }
}
