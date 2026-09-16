<?php

namespace App\Livewire\Admin;

use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RoProyek;
use App\Models\RoTargetPeriode;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Kelola hierarki RO/Proyek (RO induk -> Aktivitas turunan, penanda RO Kunci/
 * Critical Path) beserta target & realisasi per periode (ro_target_periode).
 */
class RoProyekManager extends Component
{
    #[Locked]
    public Psn $psn;

    public array $form = [];

    public ?int $editingId = null;

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
        $this->form = [
            'nama_ro' => null,
            'tipe' => 'RO',
            'ro_induk_id' => null,
            'is_ro_kunci' => false,
            'satuan' => null,
            'baseline' => null,
            'target_akhir' => null,
            'lokasi' => null,
            'instansi_pelaksana_id' => null,
        ];
    }

    public function edit(int $id): void
    {
        $ro = RoProyek::where('psn_id', $this->psn->id)->findOrFail($id);
        $this->editingId = $id;
        $this->form = $ro->only(['nama_ro', 'tipe', 'ro_induk_id', 'is_ro_kunci', 'satuan', 'baseline', 'target_akhir', 'lokasi', 'instansi_pelaksana_id']);
    }

    public function save(): void
    {
        Gate::authorize('update', $this->psn);

        $this->validate([
            'form.nama_ro' => 'required|string',
            'form.tipe' => 'required|in:RO,Aktivitas',
            'form.ro_induk_id' => 'nullable|exists:ro_proyek,id',
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
            'pembiayaan_rencana_juta_rp' => null,
            'realisasi_fisik' => null,
            'realisasi_anggaran_juta_rp' => null,
            'indikasi_sumber_pendanaan' => null,
            'status' => null,
            'permasalahan' => null,
            'kebutuhan_dukungan' => null,
            'keterangan' => null,
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
        ]);

        RoTargetPeriode::create($this->periodeForm + ['ro_id' => $this->expandedPeriodeRoId]);

        $this->resetPeriodeForm();
    }

    public function deletePeriode(int $id): void
    {
        Gate::authorize('update', $this->psn);
        RoTargetPeriode::where('ro_id', $this->expandedPeriodeRoId)->findOrFail($id)->delete();
    }

    public function render()
    {
        $roIndukList = RoProyek::where('psn_id', $this->psn->id)
            ->whereNull('ro_induk_id')
            ->with(['anak', 'instansiPelaksana'])
            ->orderBy('id')
            ->get();

        $roIndukOptions = RoProyek::where('psn_id', $this->psn->id)
            ->where('tipe', 'RO')
            ->whereNull('ro_induk_id')
            ->pluck('nama_ro', 'id');

        $instansiOptions = RefInstansi::orderBy('nama_instansi')->pluck('nama_instansi', 'id');

        $periodeList = $this->expandedPeriodeRoId
            ? RoTargetPeriode::where('ro_id', $this->expandedPeriodeRoId)->orderByDesc('tahun')->orderByDesc('triwulan')->orderByDesc('bulan')->get()
            : collect();

        return view('livewire.admin.ro-proyek-manager', compact('roIndukList', 'roIndukOptions', 'instansiOptions', 'periodeList'));
    }
}
