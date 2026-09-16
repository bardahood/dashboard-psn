<?php

namespace App\Livewire\Admin;

use App\Models\KunjunganIndeksBukti;
use App\Models\KunjunganPerencanaan;
use App\Models\KunjunganVerifikasiDokumenTeknis;
use App\Models\KunjunganVerifikasiKriteria;
use App\Models\KunjunganVerifikasiLokasi;
use App\Models\KunjunganVerifikasiTrisula;
use App\Models\Psn;
use App\Models\RefDampakTrisula;
use App\Models\RefDokumenTeknis;
use App\Models\RefInstansi;
use App\Models\RefKlaster;
use App\Models\RefKriteriaPerencanaan;
use App\Models\RefPic;
use App\Models\RefProvinsi;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Wizard Instrumen Kunjungan Lapangan bidang Perencanaan -- verifikasi usulan
 * PSN terhadap 14 kriteria Permen PPN/Bappenas No. 4/2025 (Bagian 7.2 prompt
 * pengembangan). Kriteria dirender dinamis dari ref_kriteria_perencanaan,
 * bukan di-hardcode di view.
 */
class KunjunganPerencanaanWizard extends Component
{
    protected const STEPS = [
        1 => 'Data Usulan',
        2 => 'Hasil PMO',
        3 => 'Kriteria Utama',
        4 => 'Kriteria Pendukung',
        5 => 'Kriteria Kesiapan',
        6 => 'Verifikasi Lokasi',
        7 => 'Dokumen Teknis',
        8 => 'Dampak Trisula',
        9 => 'Indeks Bukti',
        10 => 'Kesimpulan',
    ];

    public ?KunjunganPerencanaan $kunjungan = null;

    public int $step = 1;

    public array $usulan = [];

    /**
     * Penanda "usulan ini termasuk proyek infrastruktur", dipakai murni untuk
     * menentukan kriteria K3 vs K4 mana yang ditampilkan (kondisional).
     * Skema tidak punya kolom khusus untuk ini (hanya jenis_pengusul), jadi
     * disimpan sebagai state UI wizard saja, bukan kolom kunjungan_perencanaan.
     */
    public bool $isInfrastruktur = false;

    public array $pmo = [];

    public array $kriteriaJawaban = [];

    public array $lokasiForm = [];

    public array $dokumenTeknisJawaban = [];

    public array $trisulaJawaban = [];

    public array $buktiForm = [];

    public array $kesimpulan = [];

    public function mount(?KunjunganPerencanaan $kunjungan = null): void
    {
        if ($kunjungan?->exists) {
            Gate::authorize('psn.view');
            $this->kunjungan = $kunjungan;
            $this->loadUsulan();
            $this->loadPmo();
            $this->loadKriteriaJawaban();
            $this->loadDokumenTeknisJawaban();
            $this->loadTrisulaJawaban();
            $this->loadKesimpulan();
        } else {
            $this->usulan = [
                'nama_usulan_psn' => null,
                'nomor_kode_usulan' => null,
                'klaster_id' => null,
                'pengusul_instansi_id' => null,
                'jenis_pengusul' => null,
                'provinsi_id' => null,
                'lokasi_detail' => null,
                'indikasi_pendanaan' => null,
                'nilai_proyek_rp' => null,
                'kpu_terkait' => null,
                'verifikator_id' => null,
                'tanggal_kunjungan' => now()->format('Y-m-d'),
                'psn_id' => null,
            ];
        }
    }

    protected function kriteriaConfig(): \Illuminate\Support\Collection
    {
        return RefKriteriaPerencanaan::orderBy('urutan')->orderBy('kode_kriteria')->orderBy('kode_sub')->get()->groupBy('kelompok');
    }

    /**
     * Kriteria kondisional (P4/P5/P6, K3/K4) hanya tampil bila sesuai
     * jenis_pengusul yang dipilih atau penanda proyek infrastruktur.
     */
    public function kriteriaTerlihat(RefKriteriaPerencanaan $kriteria): bool
    {
        if (! $kriteria->kondisional) {
            return true;
        }

        $peta = [
            'Usulan K/L' => $this->usulan['jenis_pengusul'] === 'KL',
            'Usulan Pemda' => $this->usulan['jenis_pengusul'] === 'Pemda',
            'Usulan BUMN/Swasta' => $this->usulan['jenis_pengusul'] === 'BUMN & Swasta',
            'Infrastruktur Ekonomi' => $this->isInfrastruktur,
            'Usulan Infrastruktur' => $this->isInfrastruktur,
        ];

        return $peta[$kriteria->syarat_kondisional] ?? true;
    }

    public function goToStep(int $step): void
    {
        if ($step > 1 && ! $this->kunjungan) {
            $this->addError('step', 'Simpan Data Usulan terlebih dahulu.');

            return;
        }

        $this->step = $step;

        match ($step) {
            2 => $this->loadPmo(),
            3, 4, 5 => $this->loadKriteriaJawaban(),
            7 => $this->loadDokumenTeknisJawaban(),
            8 => $this->loadTrisulaJawaban(),
            10 => $this->loadKesimpulan(),
            default => null,
        };
    }

    // ============ STEP 1: DATA USULAN ============

    protected function loadUsulan(): void
    {
        $this->usulan = $this->kunjungan->only([
            'nama_usulan_psn', 'nomor_kode_usulan', 'klaster_id', 'pengusul_instansi_id',
            'jenis_pengusul', 'provinsi_id', 'lokasi_detail', 'indikasi_pendanaan',
            'nilai_proyek_rp', 'kpu_terkait', 'verifikator_id', 'psn_id',
        ]);
        $this->usulan['tanggal_kunjungan'] = optional($this->kunjungan->tanggal_kunjungan)->format('Y-m-d');
    }

    public function saveUsulan(): void
    {
        $this->validate([
            'usulan.nama_usulan_psn' => 'required|string',
            'usulan.tanggal_kunjungan' => 'required|date',
            'usulan.jenis_pengusul' => 'nullable|in:KL,Pemda,BUMN & Swasta',
        ]);

        if ($this->kunjungan) {
            $this->kunjungan->update($this->usulan);
        } else {
            $this->kunjungan = KunjunganPerencanaan::create($this->usulan);
        }

        // Muat seluruh state langkah berikutnya sekaligus di sini (bukan hanya
        // langkah PMO) agar wizard tetap konsisten walau method save langkah
        // tertentu dipanggil langsung tanpa melalui urutan navigasi UI biasa.
        $this->loadPmo();
        $this->loadKriteriaJawaban();
        $this->loadDokumenTeknisJawaban();
        $this->loadTrisulaJawaban();
        $this->step = 2;
    }

    // ============ STEP 2: HASIL PMO ============

    protected function loadPmo(): void
    {
        $this->pmo = $this->kunjungan->only([
            'hasil_pmo_rpjmn_program_prioritas', 'hasil_pmo_rpjmn_phtc', 'hasil_pmo_selesai_2029',
            'status_keputusan_saat_ini', 'catatan_pembahasan_pmo', 'skor_pmo_sementara',
        ]);
    }

    public function savePmo(): void
    {
        $this->kunjungan->update($this->pmo);
        $this->loadKriteriaJawaban();
        $this->step = 3;
    }

    // ============ STEP 3-5: KRITERIA ============

    protected function loadKriteriaJawaban(): void
    {
        $existing = $this->kunjungan->verifikasiKriteria()->get()->keyBy('kriteria_id');

        foreach ($this->kriteriaConfig()->flatten() as $kriteria) {
            $row = $existing->get($kriteria->id);
            $this->kriteriaJawaban[$kriteria->id] = [
                'nilai_desk_review_pmo' => $row->nilai_desk_review_pmo ?? null,
                'klaim_atau_temuan_dokumen' => $row->klaim_atau_temuan_dokumen ?? null,
                'temuan_lapangan' => $row->temuan_lapangan ?? null,
                'nilai_hasil_verifikasi' => $row->nilai_hasil_verifikasi ?? null,
                'catatan' => $row->catatan ?? null,
            ];
        }
    }

    protected function saveKriteriaKelompok(string $kelompok, int $nextStep): void
    {
        $kriteriaIds = $this->kriteriaConfig()->get($kelompok, collect())->pluck('id');

        foreach ($kriteriaIds as $id) {
            if (! $this->kriteriaTerlihat(RefKriteriaPerencanaan::find($id))) {
                continue;
            }

            KunjunganVerifikasiKriteria::updateOrCreate(
                ['kunjungan_id' => $this->kunjungan->id, 'kriteria_id' => $id],
                $this->kriteriaJawaban[$id]
            );
        }

        $this->step = $nextStep;
    }

    public function saveUtama(): void
    {
        $this->saveKriteriaKelompok('Utama', 4);
    }

    public function savePendukung(): void
    {
        $this->saveKriteriaKelompok('Pendukung', 5);
    }

    public function saveKesiapan(): void
    {
        $this->saveKriteriaKelompok('Kesiapan', 6);
    }

    // ============ STEP 6: VERIFIKASI LOKASI ============

    public function resetLokasiForm(): void
    {
        $this->lokasiForm = ['aspek' => null, 'klaim_dokumen' => null, 'temuan_lapangan' => null, 'sesuai' => null, 'catatan' => null];
    }

    public function addLokasi(): void
    {
        $this->validate(['lokasiForm.aspek' => 'required|string']);
        KunjunganVerifikasiLokasi::create($this->lokasiForm + ['kunjungan_id' => $this->kunjungan->id]);
        $this->resetLokasiForm();
    }

    public function deleteLokasi(int $id): void
    {
        KunjunganVerifikasiLokasi::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id)->delete();
    }

    // ============ STEP 7: DOKUMEN TEKNIS ============

    protected function loadDokumenTeknisJawaban(): void
    {
        $existing = $this->kunjungan->verifikasiDokumenTeknis()->get()->keyBy('dokumen_id');

        foreach (RefDokumenTeknis::orderBy('urutan')->get() as $dokumen) {
            $row = $existing->get($dokumen->id);
            $this->dokumenTeknisJawaban[$dokumen->id] = [
                'tersedia' => $row->tersedia ?? null,
                'tanggal_versi_dokumen' => $row->tanggal_versi_dokumen ?? null,
                'kesesuaian_kondisi_lapangan' => $row->kesesuaian_kondisi_lapangan ?? null,
                'catatan' => $row->catatan ?? null,
            ];
        }
    }

    public function saveDokumenTeknis(): void
    {
        foreach ($this->dokumenTeknisJawaban as $dokumenId => $data) {
            KunjunganVerifikasiDokumenTeknis::updateOrCreate(
                ['kunjungan_id' => $this->kunjungan->id, 'dokumen_id' => $dokumenId],
                $data
            );
        }

        $this->loadTrisulaJawaban();
        $this->step = 8;
    }

    // ============ STEP 8: TRISULA ============

    protected function loadTrisulaJawaban(): void
    {
        $existing = $this->kunjungan->verifikasiTrisula()->get()->keyBy('dampak_id');

        foreach (RefDampakTrisula::orderBy('id')->get() as $dampak) {
            $row = $existing->get($dampak->id);
            $this->trisulaJawaban[$dampak->id] = [
                'indikator_klaim_dokumen' => $row->indikator_klaim_dokumen ?? null,
                'temuan_lapangan_spotcheck' => $row->temuan_lapangan_spotcheck ?? null,
                'kondisi_awal_terverifikasi' => $row->kondisi_awal_terverifikasi ?? null,
                'atribusi_masuk_akal' => $row->atribusi_masuk_akal ?? null,
                'catatan' => $row->catatan ?? null,
            ];
        }
    }

    public function saveTrisula(): void
    {
        foreach ($this->trisulaJawaban as $dampakId => $data) {
            KunjunganVerifikasiTrisula::updateOrCreate(
                ['kunjungan_id' => $this->kunjungan->id, 'dampak_id' => $dampakId],
                $data
            );
        }

        $this->step = 9;
    }

    // ============ STEP 9: INDEKS BUKTI ============

    public function resetBuktiForm(): void
    {
        $this->buktiForm = ['id_bukti' => null, 'kriteria_terkait' => null, 'nama_dokumen' => null, 'pemilik_data' => null, 'lokasi_bukti' => null, 'simpulan_singkat' => null, 'status_verifikasi' => null, 'tindak_lanjut' => null];
    }

    public function addBukti(): void
    {
        KunjunganIndeksBukti::create($this->buktiForm + ['kunjungan_id' => $this->kunjungan->id]);
        $this->resetBuktiForm();
    }

    public function deleteBukti(int $id): void
    {
        KunjunganIndeksBukti::where('kunjungan_id', $this->kunjungan->id)->findOrFail($id)->delete();
    }

    // ============ STEP 10: KESIMPULAN ============

    protected function loadKesimpulan(): void
    {
        $this->kunjungan->refresh();
        $this->kesimpulan = $this->kunjungan->only([
            'fokus_verifikasi_lapangan', 'pihak_pengusul_ditemui', 'narasumber_teknis_lain',
            'dasar_justifikasi', 'dokumen_masih_diperlukan', 'batas_waktu_pemenuhan',
            'diverifikasi_oleh_id', 'mengetahui_id', 'rekomendasi_keseluruhan',
        ]);
        $this->kesimpulan['batas_waktu_pemenuhan'] = optional($this->kunjungan->batas_waktu_pemenuhan)->format('Y-m-d');

        if (! $this->kesimpulan['rekomendasi_keseluruhan']) {
            $this->kesimpulan['rekomendasi_keseluruhan'] = $this->kunjungan->rekomendasiOtomatis();
        }
    }

    public function saveKesimpulan(): void
    {
        $this->kunjungan->update($this->kesimpulan);
        session()->flash('status', 'Instrumen kunjungan perencanaan berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.kunjungan-perencanaan-wizard', [
            'steps' => self::STEPS,
            'kriteriaByKelompok' => $this->kriteriaConfig(),
            'psnOptions' => Psn::orderBy('nama_psn')->pluck('nama_psn', 'id'),
            'klasterOptions' => RefKlaster::orderBy('nama_klaster')->pluck('nama_klaster', 'id'),
            'instansiOptions' => RefInstansi::orderBy('nama_instansi')->pluck('nama_instansi', 'id'),
            'provinsiOptions' => RefProvinsi::orderBy('nama_provinsi')->pluck('nama_provinsi', 'id'),
            'picOptions' => RefPic::orderBy('nama_pic')->pluck('nama_pic', 'id'),
            'dokumenTeknisList' => RefDokumenTeknis::orderBy('urutan')->get(),
            'dampakTrisulaList' => RefDampakTrisula::orderBy('id')->get(),
            'lokasiList' => $this->kunjungan ? $this->kunjungan->verifikasiLokasi()->get() : collect(),
            'buktiList' => $this->kunjungan ? $this->kunjungan->indeksBukti()->get() : collect(),
            'gateUtamaGagal' => $this->kunjungan?->gateUtamaGagal() ?? false,
            'skorPendukung' => $this->kunjungan?->skorPendukung(),
            'skorKesiapan' => $this->kunjungan?->skorKesiapan(),
            'skorLokasi' => $this->kunjungan?->skorLokasi(),
            'skorTrisula' => $this->kunjungan?->skorTrisula(),
            'skorKeseluruhan' => $this->kunjungan?->skorKeseluruhan(),
            'rekomendasiOtomatis' => $this->kunjungan?->rekomendasiOtomatis(),
        ]);
    }
}
