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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reporting: bahan Laporan Presiden/Semester -- kombinasi banyak view
 * (Bagian 5.2 prompt pengembangan).
 *
 * GAP #10 (analisis KAK vs Dashboard PSN): KAK menyebut fokus klaster
 * berbeda per periode laporan (mis. Laporan Awal fokus Energi & Pangan,
 * Laporan Interim fokus Konektivitas/Hilirisasi/SDA). Daripada menyimpan
 * "periode laporan" sebagai konsep baru di database, ketiga jenis laporan
 * di bawah menerima filter klaster opsional pada saat digenerate --
 * cukup untuk menghasilkan laporan yang di-scope ke klaster fokus periode
 * berjalan tanpa menambah tabel/state baru.
 */
class LaporanController extends Controller
{
    public function index()
    {
        $klasterOptions = RefKlaster::orderBy('nama_klaster')->get();

        return view('admin.laporan.index', compact('klasterOptions'));
    }

    public function matriksExcel(Request $request)
    {
        $namaKlaster = $this->resolveNamaKlaster($request);

        return Excel::download(new MatriksSandinganExport($namaKlaster), 'matriks-sandingan-sumber-'.now()->format('Y-m-d').'.xlsx');
    }

    public function daftarPsnExcel(Request $request)
    {
        $namaKlaster = $this->resolveNamaKlaster($request);

        return Excel::download(new DaftarPsnExport($namaKlaster), 'daftar-psn-'.now()->format('Y-m-d').'.xlsx');
    }

    public function ringkasanPdf(Request $request)
    {
        $klasterIds = $request->input('klaster_id', []);
        $namaKlaster = $this->resolveNamaKlaster($request);

        $lingkupPsn = fn ($query) => $klasterIds ? $query->whereIn('klaster_id', $klasterIds) : $query;

        $data = [
            'tanggal' => now()->translatedFormat('d F Y'),
            'klaster_fokus' => $namaKlaster,
            'total_psn' => $lingkupPsn(Psn::query())->count(),
            'total_pkpn' => $lingkupPsn(Psn::query())->where('tipe_hierarki', 'PKPN')->count(),
            'per_klaster' => RefKlaster::when($klasterIds, fn ($q) => $q->whereIn('id', $klasterIds))
                ->withCount('psn')->having('psn_count', '>', 0)->orderByDesc('psn_count')->get(),
            'per_status' => RefStatusPsn::withCount(['psn' => fn ($q) => $lingkupPsn($q)])->orderBy('urutan')->get(),
            'risiko_per_level' => RisikoPsn::selectRaw('level_risiko_awal, count(*) as jumlah')
                ->whereNotNull('level_risiko_awal')
                ->when($klasterIds, fn ($q) => $q->whereHas('psn', fn ($p) => $p->whereIn('klaster_id', $klasterIds)))
                ->groupBy('level_risiko_awal')
                ->pluck('jumlah', 'level_risiko_awal'),
            'total_ro_kunci' => RoProyek::where('is_ro_kunci', true)
                ->when($klasterIds, fn ($q) => $q->whereHas('psn', fn ($p) => $p->whereIn('klaster_id', $klasterIds)))
                ->count(),
            'psn_sumber_manual' => $lingkupPsn(Psn::query())->where('sumber_input', 'Manual')->count(),
            'ketersediaan_sumber' => DB::table('v_psn_sandingan_sumber')
                ->when($namaKlaster, fn ($q) => $q->whereIn('nama_klaster', $namaKlaster))
                ->selectRaw('
                sum(rkp_pemutakhiran_2026 = 1) as rkp,
                sum(data_peks3 = 1) as peks3,
                sum(data_psi = 1) as psi,
                sum(permenko = 1) as permenko
            ')->first(),
        ];

        $pdf = Pdf::loadView('laporan.ringkasan-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('laporan-ringkasan-psn-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * @return array<int, string>|null
     */
    protected function resolveNamaKlaster(Request $request): ?array
    {
        $ids = $request->input('klaster_id', []);

        if (empty($ids)) {
            return null;
        }

        $nama = RefKlaster::whereIn('id', $ids)->orderBy('nama_klaster')->pluck('nama_klaster')->all();

        return $nama ?: null;
    }
}
