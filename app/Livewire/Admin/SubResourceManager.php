<?php

namespace App\Livewire\Admin;

use App\Models\CatatanMonev;
use App\Models\DasarHukumPsn;
use App\Models\InfoMemo;
use App\Models\KebutuhanRegulasi;
use App\Models\Psn;
use App\Models\PsnEvaluasiStatus;
use App\Models\PsnIsuLainnya;
use App\Models\PsnPenanggungJawab;
use App\Models\RefInstansi;
use App\Models\RefPic;
use App\Models\StakeholderPsn;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen CRUD generik, config-driven, untuk 7 sub-tabel profil PSN yang
 * bentuknya flat (satu form -- satu baris): dasar_hukum, stakeholder,
 * isu_lainnya, evaluasi_status, regulasi, info_memo, catatan_monev.
 */
class SubResourceManager extends Component
{
    use WithPagination;

    #[Locked]
    public Psn $psn;

    #[Locked]
    public string $type;

    public array $form = [];

    public ?int $editingId = null;

    protected function config(): array
    {
        $picOptions = fn () => RefPic::orderBy('nama_pic')->pluck('nama_pic', 'id')->all();
        $instansiOptions = fn () => \App\Models\RefInstansi::orderBy('nama_instansi')->pluck('nama_instansi', 'nama_instansi')->all();

        return [
            // Gambaran Umum: "Pengusul; Penanggung Jawab; Stakeholders Mapping;
            // Kerangka Kelembagaan" -- psn_penanggung_jawab (multi-instansi per
            // PSN) sudah ada di skema & diisi otomatis lewat impor Matrik
            // Sandingan, tapi sebelumnya tidak punya UI untuk dilihat/diubah
            // manual sama sekali.
            'penanggung_jawab' => [
                'label' => 'Penanggung Jawab',
                'model' => PsnPenanggungJawab::class,
                'order' => 'id',
                'fields' => [
                    ['name' => 'instansi_id', 'label' => 'Instansi Penanggung Jawab', 'type' => 'select', 'required' => true, 'options' => fn () => RefInstansi::orderBy('nama_instansi')->pluck('nama_instansi', 'id')->all()],
                ],
            ],
            'dasar_hukum' => [
                'label' => 'Dasar Hukum',
                'model' => DasarHukumPsn::class,
                'order' => 'tahun',
                'fields' => [
                    ['name' => 'nama_regulasi', 'label' => 'Nama Regulasi', 'type' => 'text', 'required' => true],
                    ['name' => 'nomor_regulasi', 'label' => 'Nomor Regulasi', 'type' => 'text'],
                    ['name' => 'tahun', 'label' => 'Tahun', 'type' => 'number'],
                    ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea'],
                ],
            ],
            'stakeholder' => [
                'label' => 'Stakeholder Mapping',
                'model' => StakeholderPsn::class,
                'order' => 'level_kelembagaan',
                'fields' => [
                    ['name' => 'nama_pemangku_kepentingan', 'label' => 'Aktor/Instansi', 'type' => 'select', 'required' => true, 'options' => $instansiOptions],
                    ['name' => 'kategori_aktor', 'label' => 'Jenis Aktor', 'type' => 'select', 'required' => true, 'options' => ['State' => 'State', 'Non-State' => 'Non-State']],
                    // Label "Level" sengaja diganti "Pengelompokan Fungsi" (dinilai membingungkan
                    // -- Risalah Rapat 21 Sept 2026); nilai kolom (1-4) dipertahankan agar
                    // data existing tidak berubah, hanya label pilihan yang diperjelas.
                    ['name' => 'level_kelembagaan', 'label' => 'Pengelompokan Fungsi', 'type' => 'select', 'options' => [
                        1 => 'Kebijakan/Regulasi/Pengarah',
                        2 => 'Fasilitator Wilayah',
                        3 => 'Operator/Investor/Off-taker',
                        4 => 'Partisipan/Penerima Manfaat/Riset',
                    ]],
                    ['name' => 'peran_deskripsi', 'label' => 'Peran/Fungsi', 'type' => 'textarea'],
                ],
            ],
            'isu_lainnya' => [
                'label' => 'Isu Lainnya',
                'model' => PsnIsuLainnya::class,
                'order' => 'nomor',
                'fields' => [
                    ['name' => 'nomor', 'label' => 'No.', 'type' => 'number', 'required' => true],
                    ['name' => 'deskripsi_isu', 'label' => 'Deskripsi Isu', 'type' => 'textarea', 'required' => true],
                    ['name' => 'kebutuhan_dukungan', 'label' => 'Kebutuhan Dukungan', 'type' => 'textarea'],
                ],
            ],
            // Label relabel dari "Evaluasi Kebutuhan Status PSN" mengikuti istilah
            // resmi paparan Project Profile (Risalah Rapat 21 Sept 2026:
            // "Kebutuhan Justifikasi diganti Kebutuhan Status PSN Tahun
            // Selanjutnya") -- struktur data tidak berubah, sudah persis sesuai
            // sejak awal (tahun_evaluasi + masih_butuh_status_psn + justifikasi).
            'evaluasi_status' => [
                'label' => 'Kebutuhan Status PSN Tahun Selanjutnya',
                'model' => PsnEvaluasiStatus::class,
                'order' => 'tahun_evaluasi',
                'fields' => [
                    ['name' => 'tahun_evaluasi', 'label' => 'Tahun Evaluasi', 'type' => 'number', 'required' => true],
                    ['name' => 'masih_butuh_status_psn', 'label' => 'Masih Butuh Status PSN?', 'type' => 'select', 'options' => [1 => 'Ya', 0 => 'Tidak']],
                    ['name' => 'justifikasi', 'label' => 'Justifikasi Kebutuhan Status PSN', 'type' => 'textarea'],
                ],
            ],
            'regulasi' => [
                'label' => 'Kebutuhan Regulasi',
                'model' => KebutuhanRegulasi::class,
                'order' => 'target_tahun_penyelesaian',
                'fields' => [
                    ['name' => 'nama_regulasi', 'label' => 'Nama Regulasi', 'type' => 'text', 'required' => true],
                    ['name' => 'justifikasi_kebutuhan', 'label' => 'Justifikasi Kebutuhan', 'type' => 'textarea'],
                    ['name' => 'target_tahun_penyelesaian', 'label' => 'Target Tahun Selesai', 'type' => 'number'],
                    ['name' => 'penanggung_jawab_id', 'label' => 'Penanggung Jawab', 'type' => 'select', 'options' => $picOptions],
                ],
            ],
            'info_memo' => [
                'label' => 'Info Memo',
                'model' => InfoMemo::class,
                'order' => '-tanggal_memo',
                'fields' => [
                    ['name' => 'judul_memo', 'label' => 'Judul Memo', 'type' => 'text', 'required' => true],
                    ['name' => 'tanggal_memo', 'label' => 'Tanggal Memo', 'type' => 'date', 'required' => true],
                    ['name' => 'isi_memo', 'label' => 'Isi Memo', 'type' => 'textarea'],
                    ['name' => 'dibuat_oleh_id', 'label' => 'Dibuat Oleh', 'type' => 'select', 'options' => $picOptions],
                ],
            ],
            'catatan_monev' => [
                'label' => 'Catatan Monev',
                'model' => CatatanMonev::class,
                'order' => '-tanggal_catatan',
                'fields' => [
                    ['name' => 'tanggal_catatan', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
                    ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'select', 'options' => ['Progres' => 'Progres', 'Kendala' => 'Kendala', 'Rekomendasi' => 'Rekomendasi']],
                    ['name' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea', 'required' => true],
                    ['name' => 'dicatat_oleh_id', 'label' => 'Dicatat Oleh', 'type' => 'select', 'options' => $picOptions],
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
        $this->resetForm();
    }

    protected function typeConfig(): array
    {
        return $this->config()[$this->type];
    }

    protected function resolveOptions(array $field): array
    {
        if (! isset($field['options'])) {
            return [];
        }

        return is_callable($field['options']) ? ($field['options'])() : $field['options'];
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [];
        foreach ($this->typeConfig()['fields'] as $field) {
            $this->form[$field['name']] = null;
        }
    }

    public function edit(int $id): void
    {
        $model = $this->typeConfig()['model'];
        $record = $model::where('psn_id', $this->psn->id)->findOrFail($id);

        $this->editingId = $id;
        foreach ($this->typeConfig()['fields'] as $field) {
            $this->form[$field['name']] = $record->{$field['name']};
        }
    }

    public function save(): void
    {
        Gate::authorize('update', $this->psn);

        $rules = [];
        foreach ($this->typeConfig()['fields'] as $field) {
            $rules['form.'.$field['name']] = ($field['required'] ?? false) ? 'required' : 'nullable';
        }
        $this->validate($rules);

        $model = $this->typeConfig()['model'];

        if ($this->editingId) {
            $record = $model::where('psn_id', $this->psn->id)->findOrFail($this->editingId);
            $record->update($this->form);
        } else {
            $model::create($this->form + ['psn_id' => $this->psn->id]);
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Gate::authorize('update', $this->psn);

        $model = $this->typeConfig()['model'];
        $model::where('psn_id', $this->psn->id)->findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function render()
    {
        $config = $this->typeConfig();
        $model = $config['model'];
        $order = $config['order'] ?? 'id';
        $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
        $column = ltrim($order, '-');

        $rows = $model::where('psn_id', $this->psn->id)->orderBy($column, $direction)->paginate(15);

        $optionsByField = [];
        foreach ($config['fields'] as $field) {
            if ($field['type'] === 'select') {
                $optionsByField[$field['name']] = $this->resolveOptions($field);
            }
        }

        return view('livewire.admin.sub-resource-manager', [
            'config' => $config,
            'rows' => $rows,
            'optionsByField' => $optionsByField,
        ]);
    }
}
