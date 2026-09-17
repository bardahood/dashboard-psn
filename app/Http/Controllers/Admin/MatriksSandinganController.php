<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriksSandinganController extends Controller
{
    /**
     * Matriks Sandingan Sumber -- tabel v/x 4 sumber per PSN, highlight gap.
     * Langsung memakai view v_psn_sandingan_sumber, tidak menulis ulang JOIN di PHP.
     *
     * Filter lanjutan (klaster, provinsi, "hanya gap") ditambahkan karena inti
     * fungsi halaman ini adalah rekonsiliasi 4 sumber -- setelah 388 PSN riil
     * diimpor, menyaring PSN yang bolong di salah satu sumber jadi kebutuhan
     * nyata, bukan sekadar melihat semua baris satu per satu.
     */
    public function index(Request $request)
    {
        $query = DB::table('v_psn_sandingan_sumber');

        if ($request->filled('q')) {
            $query->where('nama_psn', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('klaster')) {
            $query->where('nama_klaster', $request->string('klaster'));
        }

        if ($request->filled('provinsi')) {
            $query->where('nama_provinsi', $request->string('provinsi'));
        }

        if ($request->boolean('hanya_gap')) {
            $query->where(function ($q) {
                $q->where('rkp_pemutakhiran_2026', false)
                    ->orWhere('data_peks3', false)
                    ->orWhere('data_psi', false)
                    ->orWhere('permenko', false);
            });
        }

        $matriks = $query->orderBy('nama_psn')->paginate(25)->withQueryString();

        $klaster = RefKlaster::orderBy('nama_klaster')->pluck('nama_klaster');
        $provinsi = RefProvinsi::orderBy('nama_provinsi')->pluck('nama_provinsi');

        return view('admin.matriks.index', compact('matriks', 'klaster', 'provinsi'));
    }
}
