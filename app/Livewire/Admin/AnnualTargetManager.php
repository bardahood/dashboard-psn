<?php

namespace App\Livewire\Admin;

use App\Models\IndikatorPsn;
use App\Models\IndikatorPsnTargetTahunan;
use App\Models\PenerimaManfaatPsn;
use App\Models\PenerimaManfaatTargetTahunan;
use App\Models\Psn;
use App\Models\TrisulaKontribusiPsn;
use App\Models\TrisulaTargetPeriode;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Komponen untuk pola "master + target/realisasi tahunan" yang berulang pada
 * 3 tabel: indikator_psn, penerima_manfaat_psn, trisula_kontribusi_psn --
 * masing-masing dengan tabel anak *_target_tahunan/periode (kolom Baseline |
 * Target Akhir | 2026..2029 pada Project Profile).
 */
class AnnualTargetManager extends Component
{
    #[Locked]
    public Psn $psn;

    #[Locked]
    public string $type;

    public array $parentForm = [];

    public ?int $editingParentId = null;

    public ?int $expandedParentId = null;

    public array $yearForm = [];

    protected array $years = [2025, 2026, 2027, 2028, 2029, 2030];

    protected function config(): array
    {
        return [
            'indikator' => [
                'label' => 'Indikator Output/Outcome',
                'parentModel' => IndikatorPsn::class,
                'childModel' => IndikatorPsnTargetTahunan::class,
                'childFk' => 'indikator_id',
                'titleField' => 'nama_indikator',
                'parentFields' => [
                    ['name' => 'nama_indikator', 'label' => 'Nama Indikator', 'type' => 'textarea', 'required' => true],
                    ['name' => 'satuan', 'label' => 'Satuan', 'type' => 'text'],
                    ['name' => 'baseline', 'label' => 'Baseline', 'type' => 'text'],
                ],
            ],
            'penerima_manfaat' => [
                'label' => 'Penerima Manfaat',
                'parentModel' => PenerimaManfaatPsn::class,
                'childModel' => PenerimaManfaatTargetTahunan::class,
                'childFk' => 'penerima_manfaat_id',
                'titleField' => 'kategori_penerima',
                'parentFields' => [
                    ['name' => 'kategori_penerima', 'label' => 'Kategori Penerima', 'type' => 'text', 'required' => true],
                    ['name' => 'satuan', 'label' => 'Satuan', 'type' => 'text'],
                    ['name' => 'baseline', 'label' => 'Baseline', 'type' => 'text'],
                ],
            ],
            'trisula' => [
                'label' => 'Kontribusi Trisula Pembangunan',
                'parentModel' => TrisulaKontribusiPsn::class,
                'childModel' => TrisulaTargetPeriode::class,
                'childFk' => 'kontribusi_id',
                'titleField' => 'nama_indikator',
                'parentFields' => [
                    ['name' => 'kategori_trisula', 'label' => 'Kategori Trisula', 'type' => 'select', 'required' => true, 'options' => [
                        'Pertumbuhan Ekonomi' => 'Pertumbuhan Ekonomi Berkualitas (Investasi)',
                        'Kemiskinan' => 'Penurunan Kemiskinan dan Ketimpangan (Serapan Tenaga Kerja)',
                        'Sumber Daya Manusia' => 'Peningkatan Kualitas Sumber Daya Manusia (Indeks Modal Manusia)',
                    ]],
                    ['name' => 'sub_kategori_sdm', 'label' => 'Sub-Kategori IMM (khusus kategori SDM)', 'type' => 'select', 'options' => [
                        'Pendidikan' => 'Pendidikan (Harapan Lama Sekolah usia 4-18)',
                        'Kesehatan' => 'Kesehatan (Adult Survival Rate/Prevalensi Stunting)',
                    ]],
                    ['name' => 'nama_indikator', 'label' => 'Nama Indikator', 'type' => 'textarea', 'required' => true],
                    ['name' => 'sumber_dana', 'label' => 'Sumber Dana (khusus indikator Investasi)', 'type' => 'select', 'options' => ['APBN' => 'APBN', 'Non-APBN' => 'Non-APBN']],
                    ['name' => 'baseline', 'label' => 'Baseline 2025', 'type' => 'text'],
                ],
            ],
        ];
    }

    public function mount(Psn $psn, string $type): void
    {
        abort_unless(array_key_exists($type, $this->config()), 404);
        Gate::authorize('update', $psn);

        $this->psn = $psn;
        $this->type = $type;
        $this->resetParentForm();
    }

    protected function typeConfig(): array
    {
        return $this->config()[$this->type];
    }

    public function resetParentForm(): void
    {
        $this->editingParentId = null;
        $this->parentForm = [];
        foreach ($this->typeConfig()['parentFields'] as $field) {
            $this->parentForm[$field['name']] = null;
        }
    }

    public function editParent(int $id): void
    {
        $model = $this->typeConfig()['parentModel'];
        $record = $model::where('psn_id', $this->psn->id)->findOrFail($id);

        $this->editingParentId = $id;
        foreach ($this->typeConfig()['parentFields'] as $field) {
            $this->parentForm[$field['name']] = $record->{$field['name']};
        }
    }

    public function saveParent(): void
    {
        Gate::authorize('update', $this->psn);

        $rules = [];
        foreach ($this->typeConfig()['parentFields'] as $field) {
            $rules['parentForm.'.$field['name']] = ($field['required'] ?? false) ? 'required' : 'nullable';
        }
        $this->validate($rules);

        $model = $this->typeConfig()['parentModel'];
        $data = $this->parentForm;

        if ($this->type === 'trisula') {
            $data['sumber_dana'] = $data['sumber_dana'] ?: null;
        }

        if ($this->editingParentId) {
            $model::where('psn_id', $this->psn->id)->findOrFail($this->editingParentId)->update($data);
        } else {
            $model::create($data + ['psn_id' => $this->psn->id]);
        }

        $this->resetParentForm();
    }

    public function deleteParent(int $id): void
    {
        Gate::authorize('update', $this->psn);

        $model = $this->typeConfig()['parentModel'];
        $model::where('psn_id', $this->psn->id)->findOrFail($id)->delete();

        if ($this->expandedParentId === $id) {
            $this->expandedParentId = null;
        }
    }

    public function toggleYears(int $parentId): void
    {
        if ($this->expandedParentId === $parentId) {
            $this->expandedParentId = null;

            return;
        }

        $this->expandedParentId = $parentId;

        $config = $this->typeConfig();
        $childModel = $config['childModel'];
        $existing = $childModel::where($config['childFk'], $parentId)->get()->keyBy('tahun');

        $this->yearForm = [];
        foreach ($this->years as $year) {
            $row = $existing->get($year);
            $this->yearForm[$year] = [
                'target_akhir' => $row->target_akhir ?? null,
                'target' => $row->target ?? null,
                'realisasi' => $row->realisasi ?? null,
                'status_capaian' => $row->status_capaian ?? null,
            ];
        }
    }

    public function saveYears(): void
    {
        Gate::authorize('update', $this->psn);

        $config = $this->typeConfig();
        $childModel = $config['childModel'];
        $fk = $config['childFk'];

        foreach ($this->yearForm as $year => $data) {
            if ($data['target'] === null && $data['realisasi'] === null && $data['target_akhir'] === null) {
                $childModel::where($fk, $this->expandedParentId)->where('tahun', $year)->delete();

                continue;
            }

            $persenRealisasi = ($data['target'] && $data['realisasi'] !== null)
                ? round(($data['realisasi'] / $data['target']) * 100, 2)
                : null;

            $payload = array_merge($data, [
                'persen_realisasi' => $persenRealisasi,
            ]);

            if ($config['childModel'] === TrisulaTargetPeriode::class) {
                $payload['tipe_periode'] = 'TAHUNAN';
            }

            $childModel::updateOrCreate([$fk => $this->expandedParentId, 'tahun' => $year], $payload);
        }
    }

    public function render()
    {
        $config = $this->typeConfig();
        $parentModel = $config['parentModel'];

        $parents = $parentModel::where('psn_id', $this->psn->id)->orderBy('id')->get();

        return view('livewire.admin.annual-target-manager', [
            'config' => $config,
            'parents' => $parents,
            'years' => $this->years,
        ]);
    }
}
