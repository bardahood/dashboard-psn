<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProjectProfileListExport;
use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\RefStatusPsn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Halaman "Project Profile" -- rekap baca-saja (read-only) seluruh muatan
 * profil PSN, disusun mengikuti struktur resmi paparan "Update Project
 * Profile" (Perencanaan vs Penjabaran Tahunan, lihat slide "Struktur Project
 * Profile"). Berbeda dari tab-tab admin.psn.* yang berorientasi
 * pengisian/edit satu bagian per satu bagian, halaman ini menyatukan seluruh
 * bagian jadi satu dokumen agar mudah ditinjau/diekspor sekaligus (matriks
 * untuk semua PSN, detail lengkap per PSN).
 */
class ProjectProfileController extends Controller
{
    /** Kolom yang boleh dipakai untuk urutkan tabel (whitelist, cegah SQL injection lewat parameter `sort`). */
    private const KOLOM_URUT = ['kode_rkp', 'nama_psn', 'tahun_penyelesaian'];

    /** Pilihan "entries per page" ala DataTables pada tabel Profile PSN. */
    private const PILIHAN_PER_HALAMAN = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $this->authorize('viewAny', Psn::class);

        $query = $this->query($request);

        $kolomUrut = in_array($request->string('sort')->toString(), self::KOLOM_URUT, true)
            ? $request->string('sort')->toString()
            : 'nama_psn';
        $arahUrut = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';

        $perHalaman = in_array($request->integer('per_page'), self::PILIHAN_PER_HALAMAN, true)
            ? $request->integer('per_page')
            : 10;

        $daftarPsn = $query->orderBy($kolomUrut, $arahUrut)->paginate($perHalaman)->withQueryString();

        return view('admin.project-profile.index', [
            'daftarPsn' => $daftarPsn,
            'klasterOptions' => RefKlaster::orderBy('nama_klaster')->get(),
            'statusOptions' => RefStatusPsn::orderBy('urutan')->get(),
            'provinsiOptions' => RefProvinsi::orderBy('nama_provinsi')->get(),
            'kolomUrut' => $kolomUrut,
            'arahUrut' => $arahUrut,
            'perHalaman' => $perHalaman,
            'pilihanPerHalaman' => self::PILIHAN_PER_HALAMAN,
        ]);
    }

    /** Unduh Excel dari daftar Profile PSN, mengikuti filter/pencarian yang sedang aktif di halaman. */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Psn::class);

        $daftarPsn = $this->query($request)->orderBy('nama_psn')->get();

        return Excel::download(new ProjectProfileListExport($daftarPsn), 'profile-psn-'.now()->format('Y-m-d').'.xlsx');
    }

    private function query(Request $request): Builder
    {
        $query = Psn::query()->with([
            'klaster', 'provinsi', 'statusPsn',
            'pengusulInstansi', 'pengelolaInstansi', 'kontraktorInstansi', 'supervisiInstansi',
            'penanggungJawab.instansi',
        ]);

        if ($request->filled('klaster_id')) {
            $query->where('klaster_id', $request->integer('klaster_id'));
        }
        if ($request->filled('status_psn_id')) {
            $query->where('status_psn_id', $request->integer('status_psn_id'));
        }
        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', $request->integer('provinsi_id'));
        }
        if ($request->filled('tipe_hierarki')) {
            $query->where('tipe_hierarki', $request->string('tipe_hierarki'));
        }
        if ($request->filled('q')) {
            $query->whereFullText('nama_psn', $request->string('q'));
        }

        return $query;
    }

    public function show(Request $request, Psn $psn)
    {
        $this->authorize('view', $psn);

        $psn->load([
            'klaster', 'provinsi', 'statusPsn',
            'pengusulInstansi', 'pengelolaInstansi', 'kontraktorInstansi', 'supervisiInstansi',
            'dasarHukum',
            'stakeholder',
            'indikator.targetTahunan',
            'penerimaManfaat.targetTahunan',
            'trisulaKontribusi.targetPeriode',
            'kebutuhanRegulasi.penanggungJawab',
            'risiko.penanggungJawab',
            'risiko.pelaksanaPerlakuan',
            'risiko.ro',
            'risiko.statusPeriode',
            'roProyek.anak.targetPeriode',
            'roProyek.instansiPelaksana',
            'roProyek.targetPeriode',
            'isuLainnya',
            'evaluasiStatus',
        ]);

        $roIndukList = $psn->roProyek->whereNull('ro_induk_id')->values();

        $tahunPenjabaran = $request->integer('tahun') ?: now()->year;
        $tahunOptions = range(2025, 2030);
        $tahunGrid = range(2025, 2030);

        return view('admin.project-profile.show', compact('psn', 'roIndukList', 'tahunPenjabaran', 'tahunOptions', 'tahunGrid'));
    }
}
