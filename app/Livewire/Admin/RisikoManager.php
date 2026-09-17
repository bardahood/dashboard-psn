<?php

namespace App\Livewire\Admin;

use App\Models\Psn;
use App\Models\RefPic;
use App\Models\RisikoPsn;
use App\Models\RisikoStatusPeriode;
use App\Models\RoProyek;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Register risiko per PSN (risiko_psn) + pelaporan rutin triwulanan
 * (risiko_status_periode) -- terpisah dari snapshot verifikasi kunjungan
 * pengendalian (kunjungan_pengendalian_risiko), sesuai prinsip kunci skema.
 */
class RisikoManager extends Component
{
    protected const LEVELS = ['Rendah' => 'Rendah', 'Sedang' => 'Sedang', 'Tinggi' => 'Tinggi', 'Sangat Tinggi' => 'Sangat Tinggi'];

    protected const STATUS_PERLAKUAN = ['Selesai' => 'Selesai', 'On Progress' => 'On Progress', 'Belum ada Tindak Lanjut' => 'Belum ada Tindak Lanjut'];

    #[Locked]
    public Psn $psn;

    public array $form = [];

    public ?int $editingId = null;

    public ?int $expandedStatusRisikoId = null;

    public array $statusForm = [];

    public function mount(Psn $psn): void
    {
        Gate::authorize('update', $psn);
        $this->psn = $psn;
        $this->resetForm();
        $this->resetStatusForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'peristiwa_risiko' => null,
            'kategori_risiko' => null,
            'level_risiko_awal' => null,
            'perlakuan_rencana' => null,
            'risiko_residual_harapan' => null,
            'penanggung_jawab_id' => null,
            'target_mulai' => null,
            'target_selesai' => null,
            'ro_id' => null,
            'is_titik_kritis' => false,
            'tahun_pelaksanaan_perlakuan' => null,
        ];
    }

    public function edit(int $id): void
    {
        $risiko = RisikoPsn::where('psn_id', $this->psn->id)->findOrFail($id);
        $this->editingId = $id;
        $this->form = $risiko->only([
            'peristiwa_risiko', 'kategori_risiko', 'level_risiko_awal', 'perlakuan_rencana', 'risiko_residual_harapan',
            'penanggung_jawab_id', 'target_mulai', 'target_selesai', 'ro_id', 'is_titik_kritis', 'tahun_pelaksanaan_perlakuan',
        ]);
        $this->form['target_mulai'] = $risiko->target_mulai?->format('Y-m-d');
        $this->form['target_selesai'] = $risiko->target_selesai?->format('Y-m-d');
    }

    public function save(): void
    {
        Gate::authorize('update', $this->psn);

        $this->validate([
            'form.peristiwa_risiko' => 'required|string',
        ]);

        if ($this->editingId) {
            RisikoPsn::where('psn_id', $this->psn->id)->findOrFail($this->editingId)->update($this->form);
        } else {
            RisikoPsn::create($this->form + ['psn_id' => $this->psn->id]);
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Gate::authorize('update', $this->psn);
        RisikoPsn::where('psn_id', $this->psn->id)->findOrFail($id)->delete();

        if ($this->expandedStatusRisikoId === $id) {
            $this->expandedStatusRisikoId = null;
        }
    }

    public function resetStatusForm(): void
    {
        $this->statusForm = [
            'tahun' => now()->year,
            'triwulan' => 1,
            'progres_pelaksanaan_persen' => null,
            'risiko_residual_aktual' => null,
            'status_perlakuan' => null,
            'bukti_dukung' => null,
            'catatan' => null,
        ];
    }

    public function toggleStatus(int $risikoId): void
    {
        $this->expandedStatusRisikoId = $this->expandedStatusRisikoId === $risikoId ? null : $risikoId;
        $this->resetStatusForm();
    }

    public function addStatus(): void
    {
        Gate::authorize('update', $this->psn);

        $this->validate([
            'statusForm.tahun' => 'required|integer',
            'statusForm.triwulan' => 'required|integer|between:1,4',
        ]);

        RisikoStatusPeriode::updateOrCreate(
            ['risiko_id' => $this->expandedStatusRisikoId, 'tahun' => $this->statusForm['tahun'], 'triwulan' => $this->statusForm['triwulan']],
            $this->statusForm
        );

        $this->resetStatusForm();
    }

    public function deleteStatus(int $id): void
    {
        Gate::authorize('update', $this->psn);
        RisikoStatusPeriode::where('risiko_id', $this->expandedStatusRisikoId)->findOrFail($id)->delete();
    }

    public function render()
    {
        $risikoList = RisikoPsn::where('psn_id', $this->psn->id)->with(['penanggungJawab', 'ro'])->orderByDesc('id')->get();

        $statusList = $this->expandedStatusRisikoId
            ? RisikoStatusPeriode::where('risiko_id', $this->expandedStatusRisikoId)->orderByDesc('tahun')->orderByDesc('triwulan')->get()
            : collect();

        return view('livewire.admin.risiko-manager', [
            'risikoList' => $risikoList,
            'statusList' => $statusList,
            'levels' => self::LEVELS,
            'statusPerlakuanOptions' => self::STATUS_PERLAKUAN,
            'picOptions' => RefPic::orderBy('nama_pic')->pluck('nama_pic', 'id'),
            'roOptions' => RoProyek::where('psn_id', $this->psn->id)->orderBy('nama_ro')->pluck('nama_ro', 'id'),
        ]);
    }
}
