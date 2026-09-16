<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KunjunganPengendalian;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefStatusPsn;
use App\Models\RisikoPsn;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Executive Dashboard -- ringkasan portfolio lintas klaster, KPI, tren.
     */
    public function index()
    {
        $data = Cache::remember('admin.dashboard.ringkasan', now()->addMinutes(15), function () {
            return [
                'total_psn' => Psn::count(),
                'total_pkpn' => Psn::where('tipe_hierarki', 'PKPN')->count(),
                'per_klaster' => RefKlaster::withCount('psn')->orderByDesc('psn_count')->get(),
                'per_status' => RefStatusPsn::withCount('psn')->orderBy('urutan')->get(),
                'risiko_tinggi' => RisikoPsn::whereIn('level_risiko_awal', ['Tinggi', 'Sangat Tinggi'])->count(),
                'total_risiko' => RisikoPsn::count(),
                'kunjungan_bulan_ini' => KunjunganPengendalian::whereMonth('tanggal_kunjungan', now()->month)
                    ->whereYear('tanggal_kunjungan', now()->year)
                    ->count(),
                'psn_manual_belum_sinkron' => Psn::where('sumber_input', 'Manual')->count(),
            ];
        });

        return view('admin.dashboard', compact('data'));
    }
}
