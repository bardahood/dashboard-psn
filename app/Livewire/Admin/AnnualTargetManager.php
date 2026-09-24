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

    /**
     * Hanya berlaku efektif untuk type=trisula (Catatan Project Profile:
     * Trisula bukan tab tersendiri, tapi tersebar mengikuti struktur resmi
     * -- target TAHUNAN ada di tab Perencanaan, target TRIWULANAN di tab
     * Penjabaran):
     * - 'lengkap' (default, dipakai indikator/penerima_manfaat): form
     *   tambah/ubah + grid tahunan + (khusus trisula) panel triwulanan,
     *   semua dalam satu tempat -- perilaku lama sebelum pemisahan ini.
     * - 'tahunan' (trisula @ Perencanaan): form tambah/ubah/hapus + grid
     *   tahunan saja, panel triwulanan disembunyikan.
     * - 'triwulanan' (trisula @ Penjabaran): form tambah/ubah/hapus dan
     *   grid tahunan disembunyikan -- hanya daftar & panel triwulanan,
     *   karena mengelola master Kontribusi Trisula adalah tanggung jawab
     *   tab Perencanaan.
     */
    #[Locked]
    public string $tampilan = 'lengkap';

    public array $parentForm = [];

    public ?int $editingParentId = null;

    public ?int $expandedParentId = null;

    public array $yearForm = [];

    public array $twForm = [];

    protected array $years = [2025, 2026, 2027, 2028, 2029, 2030];

    /**
     * Indikator Trisula Kemiskinan & Pertumbuhan Ekonomi sudah baku/terkunci
     * (Risalah Rapat 21 Sept 2026); hanya Trisula SDM yang tetap free text.
     */
    public const PRESET_INDIKATOR_TRISULA = [
        'Kemiskinan' => 'Penyerapan Tenaga Kerja',
        'Pertumbuhan Ekonomi' => 'Capex dan Opex',
    ];

    protected function config(): array
    {
        return [
            'indikator' => [
                'label' => 'Indikator Output/Outcome',
                'parentModel' => IndikatorPsn::class,
                'childModel' => IndikatorPsnTargetTahunan::class,
                'childFk' => 'indikator_id',
                'titleField' => 'nama_indikator',
                'hideStatusCapaian' => true,
                'parentFields' => [
                    ['name' => 'nama_indikator', 'label' => 'Nama Indikator', 'type' => 'textarea', 'required' => true],
                    ['name' => 'satuan', 'label' => 'Satuan', 'type' => 'text'],
                    ['name' => 'baseline', 'label' => 'Baseline', 'type' => 'text'],
                    ['name' => 'baseline_tahun', 'label' => 'Tahun Baseline (khusus proyek berjalan sebelum 2026)', 'type' => 'number'],
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
                    ['name' => 'kategori_trisula', 'label' => 'Trisula', 'type' => 'select', 'required' => true, 'options' => [
                        'Pertumbuhan Ekonomi' => 'Pertumbuhan Ekonomi Berkualitas (Investasi)',
                        'Kemiskinan' => 'Penurunan Kemiskinan dan Ketimpangan (Serapan Tenaga Kerja)',
                        'Sumber Daya Manusia' => 'Peningkatan Kualitas Sumber Daya Manusia (Indeks Modal Manusia)',
                    ]],
                    ['name' => 'sub_kategori_sdm', 'label' => 'Sub-Kategori IMM (khusus kategori SDM)', 'type' => 'select', 'options' => [
                        'Pendidikan' => 'Pendidikan (Harapan Lama Sekolah usia 4-18)',
                        'Kesehatan' => 'Kesehatan (Adult Survival Rate/Prevalensi Stunting)',
                    ]],
                    ['name' => 'nama_indikator', 'label' => 'Indikator', 'type' => 'trisula_indikator', 'required' => true],
                    ['name' => 'satuan', 'label' => 'Satuan', 'type' => 'text'],
                    ['name' => 'sumber_dana', 'label' => 'Sumber Dana (khusus indikator Investasi)', 'type' => 'select', 'options' => ['APBN' => 'APBN', 'Non-APBN' => 'Non-APBN']],
                    ['name' => 'baseline', 'label' => 'Baseline 2025', 'type' => 'text'],
                ],
            ],
        ];
    }

    public function mount(Psn $psn, string $type, string $tampilan = 'lengkap'): void
    {
        abort_unless(array_key_exists($type, $this->config()), 404);
        Gate::authorize('update', $psn);

        $this->psn = $psn;
        $this->type = $type;
        $this->tampilan = $tampilan;
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

    /**
     * Saat kategori Trisula dipilih Kemiskinan/Pertumbuhan Ekonomi, langsung
     * kunci nama_indikator ke preset baku (Risalah Rapat 21 Sept 2026).
     */
    public function updatedParentFormKategoriTrisula(?string $value): void
    {
        if ($this->type !== 'trisula') {
            return;
        }

        $this->parentForm['nama_indikator'] = self::PRESET_INDIKATOR_TRISULA[$value] ?? null;
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
            // Pertahanan server-side: preset baku tidak boleh diubah lewat manipulasi form.
            if (isset(self::PRESET_INDIKATOR_TRISULA[$data['kategori_trisula']])) {
                $data['nama_indikator'] = self::PRESET_INDIKATOR_TRISULA[$data['kategori_trisula']];
            }
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
        $query = $childModel::where($config['childFk'], $parentId);
        if ($this->type === 'trisula') {
            $query->where('tipe_periode', 'TAHUNAN');
        }
        $existing = $query->get()->keyBy('tahun');

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

        if ($this->type === 'trisula') {
            $this->resetTwForm();
        }
    }

    public function resetTwForm(): void
    {
        $this->twForm = [
            'tahun' => now()->year,
            'triwulan' => null,
            'target' => null,
            'realisasi' => null,
            'status_capaian' => null,
        ];
    }

    /**
     * Target/realisasi Trisula per triwulan -- dipisah dari target tahunan
     * (Perencanaan) karena angka triwulanan bersifat agregat capaian
     * berjalan yang dipantau lewat siklus Pengendalian, bukan bagian dari
     * grid tahun 2025-2030 yang sama.
     */
    public function saveTw(): void
    {
        Gate::authorize('update', $this->psn);

        $this->validate([
            'twForm.tahun' => 'required|integer',
            'twForm.triwulan' => 'required|integer|between:1,4',
        ]);

        $persenRealisasi = ($this->twForm['target'] && $this->twForm['realisasi'] !== null)
            ? round(($this->twForm['realisasi'] / $this->twForm['target']) * 100, 2)
            : null;

        TrisulaTargetPeriode::updateOrCreate(
            [
                'kontribusi_id' => $this->expandedParentId,
                'tipe_periode' => 'TRIWULANAN',
                'tahun' => $this->twForm['tahun'],
                'triwulan' => $this->twForm['triwulan'],
            ],
            [
                'target' => $this->twForm['target'],
                'realisasi' => $this->twForm['realisasi'],
                'persen_realisasi' => $persenRealisasi,
                'status_capaian' => $this->twForm['status_capaian'],
            ]
        );

        $this->resetTwForm();
    }

    public function deleteTw(int $id): void
    {
        Gate::authorize('update', $this->psn);

        TrisulaTargetPeriode::where('kontribusi_id', $this->expandedParentId)
            ->where('tipe_periode', 'TRIWULANAN')
            ->findOrFail($id)->delete();
    }

    public function saveYears(): void
    {
        Gate::authorize('update', $this->psn);

        $config = $this->typeConfig();
        $childModel = $config['childModel'];
        $fk = $config['childFk'];

        $isTrisula = $config['childModel'] === TrisulaTargetPeriode::class;

        foreach ($this->yearForm as $year => $data) {
            $matchKeys = [$fk => $this->expandedParentId, 'tahun' => $year];
            if ($isTrisula) {
                // Kunci ini juga membedakan baris TAHUNAN dari baris TRIWULANAN
                // pada tahun yang sama, supaya updateOrCreate tidak salah timpa.
                $matchKeys['tipe_periode'] = 'TAHUNAN';
            }

            if ($data['target'] === null && $data['realisasi'] === null && $data['target_akhir'] === null) {
                $childModel::where($matchKeys)->delete();

                continue;
            }

            $persenRealisasi = ($data['target'] && $data['realisasi'] !== null)
                ? round(($data['realisasi'] / $data['target']) * 100, 2)
                : null;

            $payload = array_merge($data, [
                'persen_realisasi' => $persenRealisasi,
            ]);

            $childModel::updateOrCreate($matchKeys, $payload);
        }
    }

    public function render()
    {
        $config = $this->typeConfig();
        $parentModel = $config['parentModel'];

        $parents = $parentModel::where('psn_id', $this->psn->id)->orderBy('id')->get();

        $twList = ($this->type === 'trisula' && $this->expandedParentId)
            ? TrisulaTargetPeriode::where('kontribusi_id', $this->expandedParentId)
                ->where('tipe_periode', 'TRIWULANAN')
                ->orderByDesc('tahun')->orderByDesc('triwulan')->get()
            : collect();

        return view('livewire.admin.annual-target-manager', [
            'config' => $config,
            'parents' => $parents,
            'years' => $this->years,
            'twList' => $twList,
        ]);
    }
}
