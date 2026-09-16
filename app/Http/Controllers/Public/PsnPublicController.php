<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\RefStatusPsn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsnPublicController extends Controller
{
    /**
     * Daftar PSN untuk publik -- HANYA kolom agregat/ringkasan, tanpa data internal
     * (permasalahan, catatan monev, nilai anggaran granular per RO).
     */
    public function index(Request $request)
    {
        $query = Psn::query()
            ->select('id', 'nama_psn', 'klaster_id', 'provinsi_id', 'status_psn_id', 'tahun_penyelesaian', 'output_akhir')
            ->with(['klaster', 'provinsi', 'statusPsn']);

        if ($request->filled('klaster_id')) {
            $query->where('klaster_id', $request->integer('klaster_id'));
        }
        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', $request->integer('provinsi_id'));
        }
        if ($request->filled('status_psn_id')) {
            $query->where('status_psn_id', $request->integer('status_psn_id'));
        }
        if ($request->filled('q')) {
            $query->whereFullText('nama_psn', $request->string('q'));
        }

        $daftarPsn = $query->orderBy('nama_psn')->paginate(20)->withQueryString();

        $klaster = RefKlaster::orderBy('nama_klaster')->get();
        $provinsi = RefProvinsi::orderBy('nama_provinsi')->get();
        $status = RefStatusPsn::orderBy('urutan')->get();

        return view('public.psn.index', compact('daftarPsn', 'klaster', 'provinsi', 'status'));
    }

    /**
     * Detail PSN untuk publik -- subset kolom dari v_psn_profil_lengkap (sudah
     * secara alami tidak menyertakan anggaran granular per-RO maupun catatan
     * internal, karena view tersebut memang hanya menggabungkan tabel `psn`
     * dengan tabel referensi).
     */
    public function show(Psn $psn)
    {
        $profil = DB::table('v_psn_profil_lengkap')->where('psn_id', $psn->id)->first();

        abort_if(! $profil, 404);

        $indikator = DB::table('v_indikator_capaian_terkini')->where('psn_id', $psn->id)->get();

        return view('public.psn.show', compact('psn', 'profil', 'indikator'));
    }
}
