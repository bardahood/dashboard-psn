<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefStatusPsn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BerandaController extends Controller
{
    public function index()
    {
        $ringkasan = Cache::remember('publik.beranda.ringkasan', now()->addMinutes(15), function () {
            return [
                'total_psn' => Psn::count(),
                'per_klaster' => RefKlaster::withCount('psn')->orderByDesc('psn_count')->get(),
                'per_status' => RefStatusPsn::withCount('psn')->orderBy('urutan')->get(),
                'per_sumber' => DB::table('psn_sumber_data')
                    ->join('ref_sumber_data', 'ref_sumber_data.id', '=', 'psn_sumber_data.sumber_data_id')
                    ->where('psn_sumber_data.tersedia', true)
                    ->select('ref_sumber_data.nama_sumber', DB::raw('count(*) as jumlah'))
                    ->groupBy('ref_sumber_data.nama_sumber')
                    ->get(),
            ];
        });

        return view('public.beranda', compact('ringkasan'));
    }
}
