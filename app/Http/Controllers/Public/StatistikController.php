<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\RefStatusPsn;
use Illuminate\Support\Facades\Cache;

class StatistikController extends Controller
{
    /**
     * Statistik agregat publik -- angka bulat/persentase saja, tanpa detail internal.
     */
    public function index()
    {
        $statistik = Cache::remember('publik.statistik', now()->addMinutes(15), function () {
            return [
                'total_psn' => Psn::count(),
                'per_klaster' => RefKlaster::withCount('psn')->orderByDesc('psn_count')->get(),
                'per_status' => RefStatusPsn::withCount('psn')->orderBy('urutan')->get(),
                'per_provinsi' => RefProvinsi::withCount('psn')->having('psn_count', '>', 0)->orderByDesc('psn_count')->limit(10)->get(),
            ];
        });

        return view('public.statistik', compact('statistik'));
    }
}
