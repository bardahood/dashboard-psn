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

        $this->flushDashboardCache();

        return redirect()->route('admin.psn.edit', $psn)->with('status', 'Data PSN berhasil ditambahkan.');
    }

    public function show(Psn $psn)
    {
        $this->authorize('view', $psn);

        $psn->load(['klaster', 'provinsi', 'statusPsn', 'pengusulInstansi', 'pengelolaInstansi', 'kontraktorInstansi', 'supervisiInstansi', 'roProyek', 'risiko']);

        return view('admin.psn.show', compact('psn'));
    }

    public function edit(Psn $psn)
    {
        $this->authorize('update', $psn);

        return view('admin.psn.edit', $this->formOptions() + ['psn' => $psn]);
    }

    public function update(Request $request, Psn $psn)
    {
        $this->authorize('update', $psn);

        $data = $this->validated($request);
        $psn->update($data);

        $this->flushDashboardCache();

        return redirect()->route('admin.psn.edit', $psn)->with('status', 'Data PSN berhasil disimpan.');
    }

    public function destroy(Psn $psn)
    {
        $this->authorize('delete', $psn);

        $psn->delete();

        $this->flushDashboardCache();

        return redirect()->route('admin.psn.index')->with('status', 'Data PSN berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        // Validasi longgar-tapi-terarah: hanya nama_psn yang wajib diisi, karena
        // banyak data sumber riil belum lengkap (lihat Bagian 8 prompt pengembangan).
        return $request->validate([
            'nama_psn' => ['required', 'string'],
            'urgensi' => ['nullable', 'string'],
            'tujuan_utama' => ['nullable', 'string'],
            'tahun_penyelesaian' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'output_akhir' => ['nullable', 'string'],
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
            'tipe_hierarki' => ['nullable', 'in:PKPN,PSN'],
            'kabupaten_kota' => ['nullable', 'string', 'max:150'],
            'kode_rkp' => ['nullable', 'string', 'max:30'],
            'sumber_input' => ['required', 'in:API PSI,Manual'],
            'periode_update' => ['nullable', 'date'],
        ]);
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
