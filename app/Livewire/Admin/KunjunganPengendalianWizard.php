<?php

namespace App\Livewire\Admin;

use App\Models\KebutuhanRegulasi;
use App\Models\KunjunganPengendalian;
use App\Models\KunjunganPengendalianAnggaran;
use App\Models\KunjunganPengendalianDokumentasi;
use App\Models\KunjunganPengendalianFisik;
use App\Models\KunjunganPengendalianKelembagaan;
use App\Models\KunjunganPengendalianRegulasi;
use App\Models\KunjunganPengendalianRisiko;
use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefPic;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Wizard Instrumen Kunjungan Lapangan bidang Pengendalian -- Bagian A-I
 * formulir (lihat Bagian 7.1 prompt pengembangan). Setiap bagian disimpan
 * langsung ke tabel masing-masing begitu diisi, sehingga instrumen bisa
 * dikerjakan bertahap tanpa kehilangan data antar-kunjungan lapangan.
 */
class KunjunganPengendalianWizard extends Component
{
    use WithFileUploads;

    protected const STEPS = [
        1 => 'Identitas',
        2 => 'Kelembagaan',
        3 => 'Fisik',
        4 => 'Anggaran',
        5 => 'Risiko',
        6 => 'Regulasi',
        7 => 'Evaluasi',
        8 => 'Dokumentasi',
        9 => 'Kesimpulan',
    ];

    protected const PERAN_KELEMBAGAAN = ['Pengusul', 'Penanggung Jawab', 'Pengelola', 'Kontraktor', 'Supervisi'];

    public ?KunjunganPengendalian $kunjungan = null;

    public int $step = 1;

    // Step 1: Identitas
    public array $identitas = [];

    // Step 2: Kelembagaan
    public array $kelembagaan = [];

    // Step 3/4/5/6: form tambah baris
    public array $fisikForm = [];

    public array $anggaranForm = [];

    public array $risikoForm = [];

    public array $regulasiForm = [];

    // Step 7: Evaluasi
    public array $evaluasi = [];

    // Step 8: dokumentasi
    public array $dokumentasiForm = [];

    public $dokumentasiFile = null;

    // Step 9
    public array $kesimpulan = [];

    public function mount(?KunjunganPengendalian $kunjungan = null): void
    {
        if ($kunjungan?->exists) {
            Gate::authorize('view', $kunjungan->psn);
            $this->kunjungan = $kunjungan;
            $this->loadIdentitas();
            $this->loadKelembagaan();
            $this->loadEvaluasi();
            $this->loadKesimpulan();
        } else {
            $this->identitas = [
                'psn_id' => request()->integer('psn_id') ?: null,
                'tanggal_kunjungan' => now()->format('Y-m-d'),
                'verifikator_id' => null,
                'tim_verifikator_tambahan' => null,
                'lokasi_kunjungan' => null,
                'kepatuhan_frekuensi_pelaporan' => null,
            ];
        }

        $this->resetFisikForm();
        $this->resetAnggaranForm();
        $this->resetRisikoForm();
        $this->resetRegulasiForm();
        $this->resetDokumentasiForm();
    }

    protected function psn(): ?Psn
    {
        $psnId = $this->kunjungan?->psn_id ?? $this->identitas['psn_id'] ?? null;

        return $psnId ? Psn::find($psnId) : null;
    }

    public function goToStep(int $step): void
    {
        if ($step > 1 && ! $this->kunjungan) {
            $this->addError('step', 'Simpan Bagian A (Identitas) terlebih dahulu.');

            return;
        }

        $this->step = $step;

        match ($step) {
            2 => $this->loadKelembagaan(),
            7 => $this->loadEvaluasi(),
            9 => $this->loadKesimpulan(),
            default => null,
        };
    }

    // ============ STEP 1: IDENTITAS ============

    protected function loadIdentitas(): void
    {
        $this->identitas = $this->kunjungan->only([
            'psn_id', 'tanggal_kunjungan', 'verifikator_id', 'tim_verifikator_tambahan',
            'lokasi_kunjungan', 'kepatuhan_frekuensi_pelaporan', 'status_psn_tercatat',
        ]);
        $this->identitas['tanggal_kunjungan'] = optional($this->kunjungan->tanggal_kunjungan)->format('Y-m-d');
    }

    public function updatedIdentitasPsnId(): void
    {
        $psn = $this->psn();

        if ($psn) {
            $this->identitas['status_psn_tercatat'] = $psn->statusPsn?->nama_status;
            $this->identitas['kepatuhan_frekuensi_pelaporan'] = $psn->cekKepatuhanFrekuensiPelaporan();
        }
    }

    public function saveIdentitas(): void
    {
        $this->validate([
            'identitas.psn_id' => 'required|exists:psn,id',
            'identitas.tanggal_kunjungan' => 'required|date',
        ]);

        $psn = Psn::findOrFail($this->identitas['psn_id']);
        Gate::authorize('view', $psn);

        $data = $this->identitas;
        $data['tipe_hierarki'] = $psn->tipe_hierarki;

        if ($this->kunjungan) {
            $this->kunjungan->update($data);
        } else {
            $this->kunjungan = KunjunganPengendalian::create($data);
        }

        $this->step = 2;
        $this->loadKelembagaan();
    }

    // ============ STEP 2: KELEMBAGAAN ============

    protected function loadKelembagaan(): void
    {
        $psn = $this->psn();
        $existing = $this->kunjungan->kelembagaan()->get()->keyBy('peran');

        $prefillTercatat = [
            'Pengusul' => $psn?->pengusul_instansi_id,
            'Penanggung Jawab' => null,
            'Pengelola' => $psn?->pengelola_instansi_id,
            'Kontraktor' => $psn?->kontraktor_instansi_id,
            'Supervisi' => $psn?->supervisi_instansi_id,
        ];

        $this->kelembagaan = [];
        foreach (self::PERAN_KELEMBAGAAN as $peran) {
            $row = $existing->get($peran);
            $this->kelembagaan[$peran] = [
                'instansi_tercatat_id' => $row->instansi_tercatat_id ?? $prefillTercatat[$peran],
                'instansi_aktual' => $row->instansi_aktual ?? null,
                'sesuai' => $row->sesuai ?? null,
                'catatan' => $row->catatan ?? null,
            ];
        }
    }

    public function saveKelembagaan(): void
    {
        foreach ($this->kelembagaan as $peran => $data) {
            KunjunganPengendalianKelembagaan::updateOrCreate(
                ['kunjungan_id' => $this->kunjungan->id, 'peran' => $peran],
                $data
            );
        }

        $this->step = 3;
    }

    // ============ STEP 3: FISIK ============

    public function resetFisikForm(): void
    {
        $this->fisikForm = ['ro_id' => null, 'target_periode_ini' => null, 'realisasi_fisik_klaim' => null, 'realisasi_fisik_verifikasi' => null, 'catatan' => null];
    }

    public function addFisik(): void
    {
        $this->validate(['fisikForm.ro_id' => 'required|exists:ro_proyek,id']);
        KunjunganPengendalianFisik::create($this->fisikForm + ['kunjungan_id' => $this->kunjungan->id]);
        $this->resetFisikForm();
    }

    public function deleteFisik(int $id): void
    {
        KunjunganPengendalianFisik::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id)->delete();
    }

    // ============ STEP 4: ANGGARAN ============

    public function resetAnggaranForm(): void
    {
        $this->anggaranForm = ['ro_id' => null, 'pembiayaan_rencana_juta_rp' => null, 'realisasi_anggaran_klaim_juta_rp' => null, 'realisasi_anggaran_verifikasi_juta_rp' => null, 'bukti_dokumen_tersedia' => null, 'catatan' => null];
    }

    public function addAnggaran(): void
    {
        $this->validate(['anggaranForm.ro_id' => 'required|exists:ro_proyek,id']);
        KunjunganPengendalianAnggaran::create($this->anggaranForm + ['kunjungan_id' => $this->kunjungan->id]);
        $this->resetAnggaranForm();
    }

    public function deleteAnggaran(int $id): void
    {
        KunjunganPengendalianAnggaran::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id)->delete();
    }

    // ============ STEP 5: RISIKO ============

    public function resetRisikoForm(): void
    {
        $this->risikoForm = ['risiko_id' => null, 'progres_pelaksanaan_persen' => null, 'risiko_residual_aktual' => null, 'status_perlakuan' => null, 'catatan' => null];
    }

    public function addRisiko(): void
    {
        $this->validate(['risikoForm.risiko_id' => 'required|exists:risiko_psn,id']);
        KunjunganPengendalianRisiko::create($this->risikoForm + ['kunjungan_id' => $this->kunjungan->id]);
        $this->resetRisikoForm();
    }

    public function deleteRisiko(int $id): void
    {
        KunjunganPengendalianRisiko::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id)->delete();
    }

    // ============ STEP 6: REGULASI ============

    public function resetRegulasiForm(): void
    {
        $this->regulasiForm = ['regulasi_id' => null, 'status_klaim' => null, 'status_temuan_lapangan' => null, 'bukti_dukung_ditemukan' => null, 'kesesuaian' => null, 'catatan' => null];
    }

    public function addRegulasi(): void
    {
        $this->validate(['regulasiForm.regulasi_id' => 'required|exists:kebutuhan_regulasi,id']);
        KunjunganPengendalianRegulasi::create($this->regulasiForm + ['kunjungan_id' => $this->kunjungan->id]);
        $this->resetRegulasiForm();
    }

    public function deleteRegulasi(int $id): void
    {
        KunjunganPengendalianRegulasi::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id)->delete();
    }

    // ============ STEP 7: EVALUASI ============

    protected function loadEvaluasi(): void
    {
        $this->evaluasi = $this->kunjungan->only(['isu_tantangan', 'kebutuhan_tindak_lanjut', 'hasil_evaluasi_proyek']);
    }

    public function saveEvaluasi(): void
    {
        $this->kunjungan->update($this->evaluasi);
        $this->step = 8;
    }

    // ============ STEP 8: DOKUMENTASI ============

    public function resetDokumentasiForm(): void
    {
        $this->dokumentasiForm = ['deskripsi' => null, 'kategori' => null];
        $this->dokumentasiFile = null;
    }

    public function addDokumentasi(): void
    {
        $this->validate([
            'dokumentasiFile' => 'required|file|max:10240',
            'dokumentasiForm.deskripsi' => 'nullable|string',
        ]);

        $path = $this->dokumentasiFile->store('kunjungan-pengendalian/'.$this->kunjungan->id, 'public');
        $nomor = KunjunganPengendalianDokumentasi::where('kunjungan_id', $this->kunjungan->id)->max('nomor') + 1;

        KunjunganPengendalianDokumentasi::create($this->dokumentasiForm + [
            'kunjungan_id' => $this->kunjungan->id,
            'nomor' => $nomor,
            'nama_file_tautan' => $path,
        ]);

        $this->resetDokumentasiForm();
    }

    public function deleteDokumentasi(int $id): void
    {
        $doc = KunjunganPengendalianDokumentasi::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->nama_file_tautan);
        $doc->delete();
    }

    // ============ STEP 9: KESIMPULAN & PENGESAHAN ============

    protected function loadKesimpulan(): void
    {
        $this->kunjungan->refresh();
        $this->kesimpulan = $this->kunjungan->only([
            'isu_tantangan', 'kebutuhan_tindak_lanjut', 'hasil_evaluasi_proyek',
            'status_pengendalian', 'rekomendasi_kelanjutan_status', 'mengetahui_id',
            'tanggal_pengesahan', 'kesimpulan_umum',
        ]);

        if (! $this->kesimpulan['status_pengendalian']) {
            $this->kesimpulan['status_pengendalian'] = $this->kunjungan->rekomendasiOtomatis();
        }
    }

    public function saveKesimpulan(): void
    {
        $this->kunjungan->update($this->kesimpulan);
        session()->flash('status', 'Instrumen kunjungan pengendalian berhasil disimpan.');
    }

    public function render()
    {
        $psn = $this->psn();

        return view('livewire.admin.kunjungan-pengendalian-wizard', [
            'steps' => self::STEPS,
            'peranList' => self::PERAN_KELEMBAGAAN,
            'psn' => $psn,
            'psnOptions' => $this->kunjungan ? collect() : Psn::orderBy('nama_psn')->pluck('nama_psn', 'id'),
            'picOptions' => RefPic::orderBy('nama_pic')->pluck('nama_pic', 'id'),
            'instansiOptions' => RefInstansi::orderBy('nama_instansi')->pluck('nama_instansi', 'id'),
            'roOptions' => $psn ? RoProyek::where('psn_id', $psn->id)->pluck('nama_ro', 'id') : collect(),
            'risikoOptions' => $psn ? RisikoPsn::where('psn_id', $psn->id)->pluck('peristiwa_risiko', 'id') : collect(),
            'regulasiOptions' => $psn ? KebutuhanRegulasi::where('psn_id', $psn->id)->pluck('nama_regulasi', 'id') : collect(),
            'fisikList' => $this->kunjungan ? $this->kunjungan->fisik()->with('ro')->get() : collect(),
            'anggaranList' => $this->kunjungan ? $this->kunjungan->anggaran()->with('ro')->get() : collect(),
            'risikoList' => $this->kunjungan ? $this->kunjungan->risiko()->with('risiko')->get() : collect(),
            'regulasiList' => $this->kunjungan ? $this->kunjungan->regulasi()->with('regulasi')->get() : collect(),
            'dokumentasiList' => $this->kunjungan ? $this->kunjungan->dokumentasi()->orderBy('nomor')->get() : collect(),
            'skorKeseluruhan' => $this->kunjungan?->skorKeseluruhan(),
            'rekomendasiOtomatis' => $this->kunjungan?->rekomendasiOtomatis(),
        ]);
    }
}
