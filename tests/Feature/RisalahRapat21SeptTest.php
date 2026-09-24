<?php

namespace Tests\Feature;

use App\Livewire\Admin\AnnualTargetManager;
use App\Livewire\Admin\RisikoManager;
use App\Livewire\Admin\RoProyekManager;
use App\Livewire\Admin\SubResourceManager;
use App\Models\IndikatorPsn;
use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefPic;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use App\Models\RoTargetPeriode;
use App\Models\StakeholderPsn;
use App\Models\TrisulaKontribusiPsn;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test hasil implementasi tindak lanjut Risalah Rapat 21 September 2026:
 * field baru pada Gambaran Umum (Sub Proyek, Data Teknis, Bulan
 * Penyelesaian), validasi minimal karakter, preset Trisula
 * Kemiskinan/Pertumbuhan Ekonomi, validasi wajib + bukti pelaporan pada
 * RO/Proyek, kategori risiko dropdown + PJ Perlakuan, dan Stakeholder
 * Mapping berbasis dropdown instansi.
 */
class RisalahRapat21SeptTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        return $user;
    }

    public function test_gambaran_umum_bisa_diisi_sub_proyek_data_teknis_dan_bulan_penyelesaian(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'nama_sub_proyek' => 'Paket 1 - Bendungan Utama',
            'sumber_input' => 'Manual',
            'tahun_penyelesaian' => 2028,
            'bulan_penyelesaian' => 6,
            'data_teknis' => 'Kapasitas tampung 50 juta m3, tinggi bendungan 80 meter.',
        ])->assertRedirect();

        $psn->refresh();
        $this->assertSame('Paket 1 - Bendungan Utama', $psn->nama_sub_proyek);
        $this->assertSame(6, $psn->bulan_penyelesaian);
        $this->assertSame('Kapasitas tampung 50 juta m3, tinggi bendungan 80 meter.', $psn->data_teknis);

        $this->get(route('admin.psn.gambaran-umum', $psn))
            ->assertOk()
            ->assertSee('Paket 1 - Bendungan Utama')
            ->assertSee('Juni')
            ->assertSee('2028')
            ->assertSee('Kapasitas tampung 50 juta m3');
    }

    public function test_bulan_penyelesaian_ditolak_bila_di_luar_rentang_1_sampai_12(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'bulan_penyelesaian' => 13,
        ])->assertSessionHasErrors('bulan_penyelesaian');
    }

    public function test_output_akhir_tujuan_utama_dan_urgensi_ditolak_bila_kurang_dari_20_karakter(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'output_akhir' => 'terlalu pendek',
            'tujuan_utama' => 'terlalu pendek',
            'urgensi' => 'terlalu pendek',
        ])->assertSessionHasErrors(['output_akhir', 'tujuan_utama', 'urgensi']);
    }

    public function test_indikator_bisa_diisi_tahun_baseline(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'indikator'])
            ->set('parentForm.nama_indikator', 'Persentase penyelesaian fisik')
            ->set('parentForm.baseline', '10')
            ->set('parentForm.baseline_tahun', 2024)
            ->call('saveParent')
            ->assertHasNoErrors();

        $indikator = IndikatorPsn::firstOrFail();
        $this->assertSame(2024, $indikator->baseline_tahun);
    }

    public function test_trisula_kemiskinan_dan_pertumbuhan_ekonomi_mengunci_indikator_ke_preset_baku(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'trisula'])
            ->set('parentForm.kategori_trisula', 'Kemiskinan')
            ->assertSet('parentForm.nama_indikator', 'Penyerapan Tenaga Kerja')
            ->set('parentForm.satuan', 'Orang')
            ->call('saveParent')
            ->assertHasNoErrors();

        $kontribusi = TrisulaKontribusiPsn::firstOrFail();
        $this->assertSame('Penyerapan Tenaga Kerja', $kontribusi->nama_indikator);
        $this->assertSame('Orang', $kontribusi->satuan);
    }

    public function test_trisula_preset_indikator_dipaksa_server_side_walau_form_dimanipulasi(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        // Simulasikan payload yang mencoba mengubah nama_indikator preset
        // secara langsung (mis. lewat manipulasi request) tanpa melalui
        // updatedParentFormKategoriTrisula(); saveParent() harus tetap
        // menimpanya kembali ke nilai baku.
        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'trisula'])
            ->set('parentForm.kategori_trisula', 'Pertumbuhan Ekonomi')
            ->set('parentForm.nama_indikator', 'Nilai coba-coba yang tidak baku')
            ->call('saveParent')
            ->assertHasNoErrors();

        $kontribusi = TrisulaKontribusiPsn::firstOrFail();
        $this->assertSame('Capex dan Opex', $kontribusi->nama_indikator);
    }

    public function test_trisula_sdm_tetap_bebas_diisi_tanpa_preset(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'trisula'])
            ->set('parentForm.kategori_trisula', 'Sumber Daya Manusia')
            ->set('parentForm.nama_indikator', 'Indeks Modal Manusia bebas isi')
            ->call('saveParent')
            ->assertHasNoErrors();

        $kontribusi = TrisulaKontribusiPsn::firstOrFail();
        $this->assertSame('Indeks Modal Manusia bebas isi', $kontribusi->nama_indikator);
    }

    public function test_ro_proyek_wajib_diisi_target_akhir_dan_lokasi(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->set('form.nama_ro', 'RO Tanpa Target')
            ->set('form.tipe', 'RO')
            ->call('save')
            ->assertHasErrors(['form.target_akhir', 'form.lokasi']);

        $this->assertDatabaseCount('ro_proyek', 0);
    }

    public function test_ro_proyek_bisa_diisi_baseline_tahun_dan_satuan(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->set('form.nama_ro', 'RO Lengkap')
            ->set('form.tipe', 'RO')
            ->set('form.target_akhir', '100 KM')
            ->set('form.lokasi', 'Provinsi Jawa Barat')
            ->set('form.satuan', 'Unit')
            ->set('form.baseline', '10 KM')
            ->set('form.baseline_tahun', 2023)
            ->call('save')
            ->assertHasNoErrors();

        $ro = RoProyek::firstOrFail();
        $this->assertSame('Unit', $ro->satuan);
        $this->assertSame(2023, $ro->baseline_tahun);
    }

    public function test_ro_proyek_menolak_periode_bila_total_target_melebihi_target_akhir(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $ro = RoProyek::create([
            'psn_id' => $psn->id,
            'nama_ro' => 'RO Terbatas',
            'target_akhir' => '100',
            'lokasi' => 'Provinsi Jawa Barat',
        ]);

        $component = Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->call('togglePeriode', $ro->id)
            ->set('periodeForm.tahun', 2026)
            ->set('periodeForm.tipe_periode', 'TRIWULANAN')
            ->set('periodeForm.triwulan', 1)
            ->set('periodeForm.target', 60)
            ->call('addPeriode')
            ->assertHasNoErrors();

        $component
            ->set('periodeForm.tahun', 2026)
            ->set('periodeForm.tipe_periode', 'TRIWULANAN')
            ->set('periodeForm.triwulan', 2)
            ->set('periodeForm.target', 50)
            ->call('addPeriode')
            ->assertHasErrors('periodeForm.target');

        // Hanya 1 baris TRIWULANAN yang berhasil tersimpan; baris kedua ditolak.
        // (Baris TAHUNAN agregat otomatis untuk tahun berjalan tidak dihitung di sini.)
        $this->assertSame(1, RoTargetPeriode::where('ro_id', $ro->id)->where('tipe_periode', 'TRIWULANAN')->count());
    }

    public function test_ro_proyek_bukti_pelaporan_bisa_diunggah_dan_dihapus_bersama_periode(): void
    {
        Storage::fake('public');
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $ro = RoProyek::create([
            'psn_id' => $psn->id,
            'nama_ro' => 'RO Bukti',
            'target_akhir' => '100',
            'lokasi' => 'Provinsi Jawa Barat',
        ]);

        $file = UploadedFile::fake()->create('laporan-tw1.pdf', 200, 'application/pdf');

        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->call('togglePeriode', $ro->id)
            ->set('periodeForm.tahun', 2026)
            ->set('periodeForm.tipe_periode', 'TRIWULANAN')
            ->set('periodeForm.triwulan', 1)
            ->set('periodeForm.target', 10)
            ->set('periodeForm.bukti_pelaporan', $file)
            ->call('addPeriode')
            ->assertHasNoErrors();

        $periode = RoTargetPeriode::where('ro_id', $ro->id)->firstOrFail();
        $this->assertNotNull($periode->bukti_pelaporan_path);
        Storage::disk('public')->assertExists($periode->bukti_pelaporan_path);

        $pathLama = $periode->bukti_pelaporan_path;

        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->call('togglePeriode', $ro->id)
            ->call('deletePeriode', $periode->id);

        $this->assertNull(RoTargetPeriode::find($periode->id));
        Storage::disk('public')->assertMissing($pathLama);
    }

    public function test_realisasi_ro_tahun_berjalan_teragregasi_otomatis_dari_triwulanan(): void
    {
        $this->travelTo(now()->setDate(2026, 6, 1));

        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $ro = RoProyek::create([
            'psn_id' => $psn->id,
            'nama_ro' => 'RO Agregat',
            'target_akhir' => '1000',
            'lokasi' => 'Provinsi Jawa Barat',
        ]);

        $component = Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->call('togglePeriode', $ro->id)
            ->set('periodeForm.tahun', 2026)
            ->set('periodeForm.tipe_periode', 'TRIWULANAN')
            ->set('periodeForm.triwulan', 1)
            ->set('periodeForm.target', 100)
            ->set('periodeForm.realisasi_fisik', 30)
            ->set('periodeForm.realisasi_anggaran_juta_rp', 500)
            ->call('addPeriode')
            ->assertHasNoErrors();

        $component
            ->set('periodeForm.tahun', 2026)
            ->set('periodeForm.tipe_periode', 'TRIWULANAN')
            ->set('periodeForm.triwulan', 2)
            ->set('periodeForm.target', 100)
            ->set('periodeForm.realisasi_fisik', 20)
            ->set('periodeForm.realisasi_anggaran_juta_rp', 300)
            ->call('addPeriode')
            ->assertHasNoErrors();

        $tahunan = RoTargetPeriode::where('ro_id', $ro->id)->where('tahun', 2026)->where('tipe_periode', 'TAHUNAN')->firstOrFail();
        $this->assertSame('50.00', (string) $tahunan->realisasi_fisik);
        $this->assertSame('800.00', (string) $tahunan->realisasi_anggaran_juta_rp);
    }

    public function test_risiko_bisa_diisi_kategori_dropdown_dan_pj_perlakuan_terpisah_dari_pj_risiko(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $picOwner = RefPic::create(['nama_pic' => 'Owner Risiko', 'email' => 'owner@bappenas.go.id']);
        $picPelaksana = RefPic::create(['nama_pic' => 'Pelaksana Perlakuan', 'email' => 'pelaksana@bappenas.go.id']);

        Livewire::test(RisikoManager::class, ['psn' => $psn])
            ->set('form.peristiwa_risiko', 'Keterlambatan perizinan lingkungan')
            ->set('form.kategori_risiko', 'Perizinan')
            ->set('form.penanggung_jawab_id', $picOwner->id)
            ->set('form.pelaksana_perlakuan_id', $picPelaksana->id)
            ->call('save')
            ->assertHasNoErrors();

        $risiko = RisikoPsn::firstOrFail();
        $this->assertSame('Perizinan', $risiko->kategori_risiko);
        $this->assertSame($picOwner->id, $risiko->penanggung_jawab_id);
        $this->assertSame($picPelaksana->id, $risiko->pelaksana_perlakuan_id);
        $this->assertSame('Pelaksana Perlakuan', $risiko->pelaksanaPerlakuan->nama_pic);
    }

    public function test_stakeholder_mapping_aktor_dipilih_dari_dropdown_instansi_referensi(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        RefInstansi::create(['nama_instansi' => 'Kementerian PUPR']);

        Livewire::test(SubResourceManager::class, ['psn' => $psn, 'type' => 'stakeholder'])
            ->set('form.nama_pemangku_kepentingan', 'Kementerian PUPR')
            ->set('form.kategori_aktor', 'State')
            ->set('form.level_kelembagaan', 3)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stakeholder_psn', [
            'psn_id' => $psn->id,
            'nama_pemangku_kepentingan' => 'Kementerian PUPR',
            'level_kelembagaan' => 3,
        ]);
    }

    public function test_pelaksana_ro_proyek_difilter_dari_stakeholder_mapping_psn_terkait(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $pupr = RefInstansi::create(['nama_instansi' => 'Kementerian PUPR']);
        RefInstansi::create(['nama_instansi' => 'Kementerian Perhubungan']);

        StakeholderPsn::create([
            'psn_id' => $psn->id,
            'nama_pemangku_kepentingan' => 'Kementerian PUPR',
            'kategori_aktor' => 'State',
        ]);

        $rendered = Livewire::test(RoProyekManager::class, ['psn' => $psn]);

        $options = $rendered->viewData('instansiOptions');
        $this->assertTrue($options->has($pupr->id));
        $this->assertCount(1, $options);
    }

    public function test_tombol_lanjutkan_ke_tab_berikutnya_muncul_dan_tertaut_dengan_benar(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.psn.gambaran-umum', $psn))
            ->assertOk()
            ->assertSee('Lanjutkan ke Perencanaan')
            ->assertSee(route('admin.psn.perencanaan', $psn), false);

        $this->get(route('admin.psn.dokumen', $psn))
            ->assertOk()
            ->assertDontSee('Lanjutkan ke');
    }

    public function test_pilih_krisna_mengisi_cepat_nama_satuan_lokasi_dan_target_akhir_ro(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Jalan Tol Semarang - Demak']);
        $pupr = RefInstansi::create(['nama_instansi' => 'Menteri Pekerjaan Umum']);

        $krisna = \App\Models\RefRoKrisna::create([
            'project_psn' => 'G10-Jalan Tol Semarang - Demak',
            'project_rkp' => '3903-Pembangunan Jalan Bebas Hambatan - TOL SEMARANG - DEMAK 1B',
            'kementerian' => 'KEMENTERIAN PEKERJAAN UMUM',
            'ro' => '001-Pembangunan Jalan Bebas Hambatan',
            'lokasi_ro' => 'TOL SEMARANG - DEMAK 1B',
            'volume' => 2.6,
            'satuan' => 'km',
            'psn_id' => $psn->id,
        ]);

        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->set('form.tipe', 'RO')
            ->set('krisnaTerpilihId', $krisna->id)
            ->assertSet('form.nama_ro', 'Pembangunan Jalan Bebas Hambatan - TOL SEMARANG - DEMAK 1B')
            ->assertSet('form.satuan', 'km')
            ->assertSet('form.lokasi', 'TOL SEMARANG - DEMAK 1B')
            ->assertSet('form.target_akhir', '2.6')
            ->assertSet('form.instansi_pelaksana_id', $pupr->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ro_proyek', [
            'psn_id' => $psn->id,
            'nama_ro' => 'Pembangunan Jalan Bebas Hambatan - TOL SEMARANG - DEMAK 1B',
            'lokasi' => 'TOL SEMARANG - DEMAK 1B',
            'satuan' => 'km',
        ]);
    }

    public function test_ro_tanpa_katalog_krisna_tetap_bisa_diisi_manual(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'PSN Tanpa Katalog Krisna']);

        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->assertViewHas('krisnaOptions', fn ($options) => $options->isEmpty())
            ->set('form.nama_ro', 'RO Manual Bebas Teks')
            ->set('form.tipe', 'RO')
            ->set('form.target_akhir', '10 unit')
            ->set('form.lokasi', 'Lokasi Manual')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ro_proyek', ['psn_id' => $psn->id, 'nama_ro' => 'RO Manual Bebas Teks']);
    }

    public function test_nama_ro_tampil_sebagai_dropdown_krisna_secara_default_dan_bisa_dialihkan_manual(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Jalan Tol Semarang - Demak']);

        \App\Models\RefRoKrisna::create([
            'project_psn' => 'G10-Jalan Tol Semarang - Demak',
            'project_rkp' => '3903-Pembangunan Jalan Bebas Hambatan - TOL SEMARANG - DEMAK 1B',
            'kementerian' => 'KEMENTERIAN PEKERJAAN UMUM',
            'ro' => '001-Pembangunan Jalan Bebas Hambatan',
            'lokasi_ro' => 'TOL SEMARANG - DEMAK 1B',
            'volume' => 2.6,
            'satuan' => 'km',
            'psn_id' => $psn->id,
        ]);

        // Default: tipe=RO & katalog Krisna tersedia -> Nama RO tampil sebagai
        // dropdown (bukan input teks bebas), sesuai Risalah Rapat 21 Sept 2026.
        Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->assertSet('modeManualRo', false)
            ->assertSee('dropdown, ditarik dari katalog Krisna')
            ->assertSee('Pilih RO dari Katalog Krisna')
            ->assertDontSee('Pilih dari dropdown katalog Krisna')
            // beralih ke isian manual (RO tak ter-tagging / Proyek / Non-RO)
            ->set('modeManualRo', true)
            ->assertDontSee('dropdown, ditarik dari katalog Krisna')
            ->assertSee('Pilih dari dropdown katalog Krisna')
            // tipe Aktivitas selalu bebas teks meski PSN ini punya katalog Krisna
            ->set('modeManualRo', false)
            ->set('form.tipe', 'Aktivitas')
            ->assertDontSee('dropdown, ditarik dari katalog Krisna');
    }
}
