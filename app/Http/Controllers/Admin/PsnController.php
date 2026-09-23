<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\RefStatusPsn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PsnController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Psn::class);

        $query = Psn::query()->with(['klaster', 'provinsi', 'statusPsn']);

        if ($request->filled('klaster_id')) {
            $query->where('klaster_id', $request->integer('klaster_id'));
        }
        if ($request->filled('status_psn_id')) {
            $query->where('status_psn_id', $request->integer('status_psn_id'));
        }
        if ($request->filled('kategori_usulan')) {
            $query->where('kategori_usulan', $request->string('kategori_usulan'));
        }
        if ($request->filled('q')) {
            $query->whereFullText('nama_psn', $request->string('q'));
        }

        $daftarPsn = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $klaster = RefKlaster::orderBy('nama_klaster')->get();
        $status = RefStatusPsn::orderBy('urutan')->get();

        return view('admin.psn.index', compact('daftarPsn', 'klaster', 'status'));
    }

    public function create()
    {
        $this->authorize('create', Psn::class);

        return view('admin.psn.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Psn::class);

        $data = $this->validated($request);
        $psn = Psn::create($data);

        $this->simpanDiagramKelembagaan($request, $psn);

        $this->flushDashboardCache();

        return redirect()->route('admin.psn.gambaran-umum', $psn)->with('status', 'Data PSN berhasil ditambahkan.');
    }

    /**
     * Detail/Ubah PSN sudah disatukan ke tab "Gambaran Umum" pada struktur
     * profil PSN 5-tab (Gambaran Umum, Perencanaan, Trisula, Penjabaran,
     * Upload Dokumen) -- route show/edit dipertahankan untuk kompatibilitas
     * tautan lama, cukup alihkan ke sana.
     */
    public function show(Psn $psn)
    {
        $this->authorize('view', $psn);

        return redirect()->route('admin.psn.gambaran-umum', $psn);
    }

    public function edit(Psn $psn)
    {
        $this->authorize('update', $psn);

        return redirect()->route('admin.psn.gambaran-umum', $psn);
    }

    /** Tab "Gambaran Umum" pada struktur profil PSN 5-tab. */
    public function gambaranUmum(Psn $psn)
    {
        $this->authorize('update', $psn);

        return view('admin.psn.gambaran-umum', $this->formOptions() + ['psn' => $psn]);
    }

    public function update(Request $request, Psn $psn)
    {
        $this->authorize('update', $psn);

        $data = $this->validated($request);
        $psn->update($data);

        $this->simpanDiagramKelembagaan($request, $psn);

        if ($request->boolean('hapus_diagram_kelembagaan') && $psn->diagram_kelembagaan_path) {
            Storage::disk('public')->delete($psn->diagram_kelembagaan_path);
            $psn->update(['diagram_kelembagaan_path' => null]);
        }

        $this->flushDashboardCache();

        return redirect()->route('admin.psn.gambaran-umum', $psn)->with('status', 'Data PSN berhasil disimpan.');
    }

    /**
     * "Visualisasi Kerangka Kelembagaan" -- diagram skematik hubungan antar
     * pihak, komponen wajib Project Profile PSN sesuai Pedoman Project
     * Profile PSN yang sebelumnya tidak punya tempat penyimpanan.
     */
    protected function simpanDiagramKelembagaan(Request $request, Psn $psn): void
    {
        if (! $request->hasFile('diagram_kelembagaan')) {
            return;
        }

        if ($psn->diagram_kelembagaan_path) {
            Storage::disk('public')->delete($psn->diagram_kelembagaan_path);
        }

        $path = $request->file('diagram_kelembagaan')->store('psn-diagram-kelembagaan/'.$psn->id, 'public');
        $psn->update(['diagram_kelembagaan_path' => $path]);
    }

    public function destroy(Psn $psn)
    {
        $this->authorize('delete', $psn);

        if ($psn->diagram_kelembagaan_path) {
            Storage::disk('public')->delete($psn->diagram_kelembagaan_path);
        }

        $psn->delete();

        $this->flushDashboardCache();

        return redirect()->route('admin.psn.index')->with('status', 'Data PSN berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        // Validasi longgar-tapi-terarah: hanya nama_psn yang wajib diisi, karena
        // banyak data sumber riil belum lengkap (lihat Bagian 8 prompt pengembangan).
        $data = $request->validate([
            'nama_psn' => ['required', 'string'],
            'nama_sub_proyek' => ['nullable', 'string', 'max:255'],
            'urgensi' => ['nullable', 'string', 'min:20'],
            'tujuan_utama' => ['nullable', 'string', 'min:20'],
            'tahun_penyelesaian' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan_penyelesaian' => ['nullable', 'integer', 'between:1,12'],
            'output_akhir' => ['nullable', 'string', 'min:20'],
            'data_teknis' => ['nullable', 'string'],
            'nilai_investasi_apbn_rp' => ['nullable', 'numeric', 'min:0'],
            'nilai_investasi_non_apbn_rp' => ['nullable', 'numeric', 'min:0'],
            'asta_cita' => ['nullable', 'string'],
            'pengusul_instansi_id' => ['nullable', 'exists:ref_instansi,id'],
            'pengelola_instansi_id' => ['nullable', 'exists:ref_instansi,id'],
            'kontraktor_instansi_id' => ['nullable', 'exists:ref_instansi,id'],
            'supervisi_instansi_id' => ['nullable', 'exists:ref_instansi,id'],
            'klaster_id' => ['nullable', 'exists:ref_klaster,id'],
            'provinsi_id' => ['nullable', 'exists:ref_provinsi,id'],
            'status_psn_id' => ['nullable', 'exists:ref_status_psn,id'],
            'kategori_usulan' => ['nullable', 'in:Carryover,Usulan Baru'],
            'tipe_hierarki' => ['nullable', 'in:PKPN,PSN'],
            'kabupaten_kota' => ['nullable', 'string', 'max:150'],
            'kode_rkp' => ['nullable', 'string', 'max:30'],
            'sumber_input' => ['required', 'in:API PSI,Manual'],
            'periode_update' => ['nullable', 'date'],
            'diagram_kelembagaan' => ['nullable', 'image', 'max:4096'],
        ]);

        // diagram_kelembagaan (file upload) ditangani terpisah di simpanDiagramKelembagaan(),
        // bukan lewat mass-assignment biasa -- kolomnya (diagram_kelembagaan_path) berbeda nama.
        unset($data['diagram_kelembagaan']);

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'klasterOptions' => RefKlaster::orderBy('nama_klaster')->get(),
            'provinsiOptions' => RefProvinsi::orderBy('nama_provinsi')->get(),
            'statusOptions' => RefStatusPsn::orderBy('urutan')->get(),
            'instansiOptions' => RefInstansi::orderBy('nama_instansi')->get(),
        ];
    }

    protected function flushDashboardCache(): void
    {
        Cache::forget('admin.dashboard.ringkasan');
        Cache::forget('publik.beranda.ringkasan');
        Cache::forget('publik.statistik');
    }
}
