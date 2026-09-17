<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DaftarPsnExport;
use App\Exports\MatriksSandinganExport;
use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefStatusPsn;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reporting: bahan Laporan Presiden/Semester -- kombinasi banyak view
 * (Bagian 5.2 prompt pengembangan).
 */
class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan.index');
    }

    public function matriksExcel()
    {
        return Excel::download(new MatriksSandinganExport, 'matriks-sandingan-sumber-'.now()->format('Y-m-d').'.xlsx');
    }

    public function daftarPsnExcel()
    {
        return Excel::download(new DaftarPsnExport, 'daftar-psn-'.now()->format('Y-m-d').'.xlsx');
    }

    public function ringkasanPdf()
    {
        $data = [
            'tanggal' => now()->translatedFormat('d F Y'),
            'total_psn' => Psn::count(),
            'total_pkpn' => Psn::where('tipe_hierarki', 'PKPN')->count(),
            'per_klaster' => RefKlaster::withCount('psn')->having('psn_count', '>', 0)->orderByDesc('psn_count')->get(),
            'per_status' => RefStatusPsn::withCount('psn')->orderBy('urutan')->get(),
            'risiko_per_level' => RisikoPsn::selectRaw('level_risiko_awal, count(*) as jumlah')
                ->whereNotNull('level_risiko_awal')
                ->groupBy('level_risiko_awal')
                ->pluck('jumlah', 'level_risiko_awal'),
            'total_ro_kunci' => RoProyek::where('is_ro_kunci', true)->count(),
            'psn_sumber_manual' => Psn::where('sumber_input', 'Manual')->count(),
            'ketersediaan_sumber' => DB::table('v_psn_sandingan_sumber')->selectRaw('
                sum(rkp_pemutakhiran_2026 = 1) as rkp,
                sum(data_peks3 = 1) as peks3,
                sum(data_psi = 1) as psi,
                sum(permenko = 1) as permenko
            ')->first(),
        ];

        $pdf = Pdf::loadView('laporan.ringkasan-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('laporan-ringkasan-psn-'.now()->format('Y-m-d').'.pdf');
    }
}
