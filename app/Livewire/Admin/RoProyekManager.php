<?php

namespace App\Livewire\Admin;

use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefRoKrisna;
use App\Models\RoProyek;
use App\Models\RoTargetPeriode;
use App\Models\StakeholderPsn;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Kelola hierarki RO/Proyek (RO induk -> Aktivitas turunan, penanda RO Kunci/
 * Critical Path) beserta target & realisasi per periode (ro_target_periode).
 */
class RoProyekManager extends Component
{
    use WithFileUploads;

    #[Locked]
    public Psn $psn;

    public array $form = [];

    public ?int $editingId = null;

    /**
     * Pilihan dropdown "isi cepat dari Krisna" (Risalah Rapat 21 Sept 2026:
     * "RO pilihannya dropdown, pilihan ditarik dari krisna"). Hanya sebagai
     * bantuan pengisian awal Nama RO/Satuan/Lokasi/Target Akhir -- field
     * tetap bebas teks dan bisa diubah manual sesudahnya, dan RO yang tidak
     * ada di katalog Krisna tetap bisa ditambahkan langsung lewat form.
     */
    public ?int $krisnaTerpilihId = null;

    public ?int $expandedPeriodeRoId = null;

    public array $periodeForm = [];

    public function mount(Psn $psn): void
    {
        Gate::authorize('update', $psn);
        $this->psn = $psn;
        $this->resetForm();
        $this->resetPeriodeForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->krisnaTerpilihId = null;
        $this->form = [
            'nama_ro' => null,
            'tipe' => 'RO',
            'ro_induk_id' => null,
            'is_ro_kunci' => false,
            'satuan' => null,
            'baseline' => null,
            'baseline_tahun' => null,
            'target_akhir' => null,
            'lokasi' => null,
            'instansi_pelaksana_id' => null,
        ];
    }

    public function edit(int $id): void
    {
        $ro = RoProyek::where('psn_id', $this->psn->id)->findOrFail($id);
        $this->editingId = $id;
        $this->form = $ro->only(['nama_ro', 'tipe', 'ro_induk_id', 'is_ro_kunci', 'satuan', 'baseline', 'baseline_tahun', 'target_akhir', 'lokasi', 'instansi_pelaksana_id']);
    }

    /**
     * Isi cepat Nama RO/Satuan/Lokasi/Target Akhir dari katalog Krisna yang
     * dipilih -- field tetap bisa diubah manual sesudahnya (bukan mode
     * kunci/readonly), sehingga "proyek dan non-RO" yang memang harus bebas
     * teks tidak terganggu (Risalah Rapat 21 Sept 2026).
     */
    public function updatedKrisnaTerpilihId(?int $value): void
    {
        if (! $value) {
            return;
        }

        $krisna = RefRoKrisna::find($value);
        if (! $krisna) {
            return;
        }

        $this->form['nama_ro'] = $this->buangKodeAwal((string) $krisna->project_rkp) ?: $krisna->ro;
        $this->form['satuan'] = $krisna->satuan;
        $this->form['lokasi'] = $krisna->lokasi_ro;
        if ($krisna->volume !== null) {
            $this->form['target_akhir'] = rtrim(rtrim((string) $krisna->volume, '0'), '.');
        }

        $namaInstansi = $this->cariInstansiCocok((string) $krisna->kementerian);
        if ($namaInstansi) {
            $this->form['instansi_pelaksana_id'] = $namaInstansi->id;
        }
    }

    private function buangKodeAwal(string $teks): string
    {
        return trim(preg_replace('/^[\w.]+-\s*/', '', $teks, 1));
    }

    private function cariInstansiCocok(string $kementerian): ?RefInstansi
    {
        if ($kementerian === '') {
            return null;
        }

        // "KEMENTERIAN PEKERJAAN UMUM" (Krisna) vs "Menteri Pekerjaan Umum"
        // (ref_instansi) -- normalisasi kedua sisi sebelum dibandingkan.
        $bersih = fn (string $s) => trim(preg_replace('/^(kementerian|menteri)\s+/i', '', $s));
        $target = mb_strtolower($bersih($kementerian));

        return RefInstansi::all()->first(fn ($i) => mb_strtolower($bersih($i->nama_instansi)) === $target);
    }

    public function save(): void
    {
        Gate::authorize('update', $this->psn);

        // target_akhir & lokasi wajib diisi (Risalah Rapat 21 Sept 2026).
        $this->validate([
            'form.nama_ro' => 'required|string',
            'form.tipe' => 'required|in:RO,Aktivitas',
            'form.ro_induk_id' => 'nullable|exists:ro_proyek,id',
            'form.target_akhir' => 'required|string',
            'form.lokasi' => 'required|string',
        ]);

        $data = $this->form;
        $data['is_ro_kunci'] = (bool) ($data['is_ro_kunci'] ?? false);

        if ($this->editingId) {
            RoProyek::where('psn_id', $this->psn->id)->findOrFail($this->editingId)->update($data);
        } else {
            RoProyek::create($data + ['psn_id' => $this->psn->id]);
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Gate::authorize('update', $this->psn);
        RoProyek::where('psn_id', $this->psn->id)->findOrFail($id)->delete();

        if ($this->expandedPeriodeRoId === $id) {
            $this->expandedPeriodeRoId = null;
        }
    }

    public function resetPeriodeForm(): void
    {
        $this->periodeForm = [
            'tahun' => now()->year,
            'tipe_periode' => 'TRIWULANAN',
            'triwulan' => null,
            'bulan' => null,
            'target' => null,
            'target_persen' => null,
            'pembiayaan_rencana_juta_rp' => null,
            'realisasi_fisik' => null,
            'realisasi_anggaran_juta_rp' => null,
            'indikasi_sumber_pendanaan' => null,
            'status' => null,
            'permasalahan' => null,
            'kebutuhan_dukungan' => null,
            'keterangan' => null,
            'bukti_pelaporan' => null,
        ];
    }

    public function togglePeriode(int $roId): void
    {
        $this->expandedPeriodeRoId = $this->expandedPeriodeRoId === $roId ? null : $roId;
        $this->resetPeriodeForm();
    }

    public function addPeriode(): void
    {
        Gate::authorize('update', $this->psn);

        $this->validate([
            'periodeForm.tahun' => 'required|integer',
            'periodeForm.tipe_periode' => 'required|in:TAHUNAN,TRIWULANAN,BULANAN',
            'periodeForm.bukti_pelaporan' => 'nullable|file|max:8192',
        ]);

        $ro = RoProyek::where('psn_id', $this->psn->id)->findOrFail($this->expandedPeriodeRoId);

        // Validasi: jumlah target seluruh periode tidak melebihi Target Akhir
        // (Risalah Rapat 21 Sept 2026) -- hanya dicek bila keduanya numerik,
        // karena target_akhir adalah field bebas teks (mis. bisa berisi satuan).
        if (is_numeric($ro->target_akhir) && $this->periodeForm['target'] !== null) {
            $totalTargetLain = (float) RoTargetPeriode::where('ro_id', $ro->id)->sum('target');
            $totalBaru = $totalTargetLain + (float) $this->periodeForm['target'];
            if ($totalBaru > (float) $ro->target_akhir) {
                $this->addError('periodeForm.target', "Jumlah target seluruh periode ({$totalBaru}) melebihi Target Akhir ({$ro->target_akhir}).");

                return;
            }
        }

        $payload = $this->periodeForm;
        $bukti = $payload['bukti_pelaporan'] ?? null;
        unset($payload['bukti_pelaporan']);

        if ($bukti) {
            $payload['bukti_pelaporan_path'] = $bukti->store('ro-bukti-pelaporan/'.$ro->id, 'public');
        }

        RoTargetPeriode::create($payload + ['ro_id' => $ro->id]);

        $this->agregasiRealisasiTahunan($ro->id, (int) $this->periodeForm['tahun']);

        $this->resetPeriodeForm();
    }

    /**
     * Realisasi TAHUNAN untuk tahun berjalan (2026) dihitung otomatis dari
     * jumlah realisasi TRIWULANAN/BULANAN tahun tsb (Risalah Rapat 21 Sept
     * 2026: "Realisasi 2026 dan real. Ang 2026 langsung terisi dari TW").
     * Hanya berlaku utk tahun berjalan agar tidak menimpa realisasi tahun
     * lampau yang sudah final/diaudit secara manual.
     */
    private function agregasiRealisasiTahunan(int $roId, int $tahun): void
    {
        if ($tahun !== now()->year) {
            return;
        }

        $agregat = RoTargetPeriode::where('ro_id', $roId)
            ->where('tahun', $tahun)
            ->whereIn('tipe_periode', ['TRIWULANAN', 'BULANAN'])
            ->selectRaw('SUM(realisasi_fisik) AS total_fisik, SUM(realisasi_anggaran_juta_rp) AS total_anggaran')
            ->first();

        RoTargetPeriode::updateOrCreate(
            ['ro_id' => $roId, 'tahun' => $tahun, 'tipe_periode' => 'TAHUNAN'],
            [
                'realisasi_fisik' => $agregat->total_fisik,
                'realisasi_anggaran_juta_rp' => $agregat->total_anggaran,
            ]
        );
    }

    public function deletePeriode(int $id): void
    {
        Gate::authorize('update', $this->psn);
        $periode = RoTargetPeriode::where('ro_id', $this->expandedPeriodeRoId)->findOrFail($id);

        if ($periode->bukti_pelaporan_path) {
            Storage::disk('public')->delete($periode->bukti_pelaporan_path);
        }

        $periode->delete();
    }

    public function render()
    {
        $roIndukList = RoProyek::where('psn_id', $this->psn->id)
            ->whereNull('ro_induk_id')
            ->with(['anak.targetPeriode', 'instansiPelaksana', 'targetPeriode'])
            ->orderBy('id')
            ->get();

        $roIndukOptions = RoProyek::where('psn_id', $this->psn->id)
            ->where('tipe', 'RO')
            ->whereNull('ro_induk_id')
            ->pluck('nama_ro', 'id');

        // Pelaksana ditautkan ke Stakeholder Mapping PSN ini (Risalah Rapat 21
        // Sept 2026); bila belum ada stakeholder yang diinput, tampilkan
        // seluruh instansi supaya form tidak buntu.
        $namaStakeholder = StakeholderPsn::where('psn_id', $this->psn->id)->pluck('nama_pemangku_kepentingan');
        $instansiOptions = $namaStakeholder->isNotEmpty()
            ? RefInstansi::whereIn('nama_instansi', $namaStakeholder)->orderBy('nama_instansi')->pluck('nama_instansi', 'id')
            : RefInstansi::orderBy('nama_instansi')->pluck('nama_instansi', 'id');

        $periodeList = $this->expandedPeriodeRoId
            ? RoTargetPeriode::where('ro_id', $this->expandedPeriodeRoId)->orderByDesc('tahun')->orderByDesc('triwulan')->orderByDesc('bulan')->get()
            : collect();

        $krisnaOptions = RefRoKrisna::where('psn_id', $this->psn->id)->orderBy('project_rkp')->get();

        return view('livewire.admin.ro-proyek-manager', compact('roIndukList', 'roIndukOptions', 'instansiOptions', 'periodeList', 'krisnaOptions'));
    }
}
