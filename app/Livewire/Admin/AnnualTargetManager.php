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
                // Membedakan dari Indikator PP khusus PKPN (lihat config
                // 'indikator_pp' di bawah) -- satu tabel dipakai bersama
                // (komentar migrasi indikator_psn: "juga menampung Indikator
                // PP khusus PKPN"), dipisah lewat kolom jenis_indikator.
                'scope' => ['jenis_indikator' => null],
                'forceAttributes' => ['jenis_indikator' => null],
                'parentFields' => [
                    ['name' => 'nama_indikator', 'label' => 'Nama Indikator', 'type' => 'textarea', 'required' => true],
                    ['name' => 'satuan', 'label' => 'Satuan', 'type' => 'text'],
                    ['name' => 'baseline', 'label' => 'Baseline', 'type' => 'text'],
                    ['name' => 'baseline_tahun', 'label' => 'Tahun Baseline (khusus proyek berjalan sebelum 2026)', 'type' => 'number'],
                ],
            ],
            // Gambaran Umum, khusus PSN bertipe hierarki PKPN (Struktur Project
            // Profile: "Indikator PP (Khusus PKPN level PP)") -- struktur
            // field & target tahunan sama persis dengan Indikator Output/
            // Outcome, hanya beda kolom jenis_indikator agar tidak tercampur
            // dengan daftar Indikator Output/Outcome di tab Perencanaan.
            'indikator_pp' => [
                'label' => 'Indikator PP',
                'parentModel' => IndikatorPsn::class,
                'childModel' => IndikatorPsnTargetTahunan::class,
                'childFk' => 'indikator_id',
                'titleField' => 'nama_indikator',
                'hideStatusCapaian' => true,
                'scope' => ['jenis_indikator' => 'PP'],
                'forceAttributes' => ['jenis_indikator' => 'PP'],
                'parentFields' => [
                    ['name' => 'nama_indikator', 'label' => 'Nama Indikator PP', 'type' => 'textarea', 'required' => true],
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

    /**
     * Terapkan filter 'scope' config (mis. jenis_indikator) ke query parent
     * -- dipakai supaya config yang berbagi 1 tabel (indikator vs
     * indikator_pp) tidak saling bocor lewat manipulasi ID dari sisi client.
     */
    protected function applyScope($query): void
    {
        foreach ($this->typeConfig()['scope'] ?? [] as $kolom => $nilai) {
            $nilai === null ? $query->whereNull($kolom) : $query->where($kolom, $nilai);
        }
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
        $query = $model::where('psn_id', $this->psn->id);
        $this->applyScope($query);
        $record = $query->findOrFail($id);

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

        // Pertahanan server-side: kolom scope (mis. jenis_indikator) dipaksa
        // dari config, tidak pernah dari form -- config ini tidak punya field
        // untuk itu sehingga $data tidak berisi kolomnya sama sekali.
        $data = array_merge($data, $this->typeConfig()['forceAttributes'] ?? []);

        if ($this->editingParentId) {
            $query = $model::where('psn_id', $this->psn->id);
            $this->applyScope($query);
            $query->findOrFail($this->editingParentId)->update($data);
        } else {
            $model::create($data + ['psn_id' => $this->psn->id]);
        }

        $this->resetParentForm();
    }

    public function deleteParent(int $id): void
    {
        Gate::authorize('update', $this->psn);

        $model = $this->typeConfig()['parentModel'];
        $query = $model::where('psn_id', $this->psn->id);
        $this->applyScope($query);
        $query->findOrFail($id)->delete();

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

        $this->agregasiRealisasiTahunanTrisula($this->expandedParentId, (int) $this->twForm['tahun']);

        $this->resetTwForm();
    }

    public function deleteTw(int $id): void
    {
        Gate::authorize('update', $this->psn);

        $tw = TrisulaTargetPeriode::where('kontribusi_id', $this->expandedParentId)
            ->where('tipe_periode', 'TRIWULANAN')
            ->findOrFail($id);
        $tahun = $tw->tahun;
        $tw->delete();

        $this->agregasiRealisasiTahunanTrisula($this->expandedParentId, (int) $tahun);
    }

    /**
     * Realisasi TAHUNAN Kontribusi Trisula untuk tahun berjalan dihitung
     * otomatis dari jumlah realisasi TRIWULANAN tahun tsb -- Struktur Project
     * Profile: "Kontribusi Terhadap Trisula Pembangunan (Target Tahunan/
     * Agregat dari Target TW)", pola yang sama seperti realisasi RO/Proyek
     * (RoProyekManager::agregasiRealisasiTahunan()). Hanya berlaku utk tahun
     * berjalan agar tidak menimpa realisasi tahun lampau yang sudah
     * final/diaudit secara manual; target tahunan tidak ikut diagregasi
     * (tetap diisi manual top-down, hanya realisasi yang bottom-up dari TW).
     */
    private function agregasiRealisasiTahunanTrisula(int $kontribusiId, int $tahun): void
    {
        if ($tahun !== now()->year) {
            return;
        }

        $totalRealisasi = TrisulaTargetPeriode::where('kontribusi_id', $kontribusiId)
            ->where('tahun', $tahun)
            ->where('tipe_periode', 'TRIWULANAN')
            ->sum('realisasi');

        $tahunan = TrisulaTargetPeriode::firstOrNew([
            'kontribusi_id' => $kontribusiId,
            'tipe_periode' => 'TAHUNAN',
            'tahun' => $tahun,
        ]);
        $tahunan->realisasi = $totalRealisasi;
        $tahunan->persen_realisasi = ($tahunan->target && $totalRealisasi !== null)
            ? round(($totalRealisasi / $tahunan->target) * 100, 2)
            : $tahunan->persen_realisasi;
        $tahunan->save();
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

        $parentQuery = $parentModel::where('psn_id', $this->psn->id);
        $this->applyScope($parentQuery);
        $parents = $parentQuery->orderBy('id')->get();

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
