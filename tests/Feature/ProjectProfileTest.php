<?php

namespace Tests\Feature;

use App\Models\IndikatorPsn;
use App\Models\IndikatorPsnTargetTahunan;
use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefKlaster;
use App\Models\RefPic;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use App\Models\RoTargetPeriode;
use App\Models\StakeholderPsn;
use App\Models\TrisulaKontribusiPsn;
use App\Models\TrisulaTargetPeriode;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman "Project Profile" -- rekap baca-saja seluruh muatan Project Profile
 * PSN, disusun mengikuti struktur resmi paparan "Update Project Profile"
 * (Perencanaan vs Penjabaran Tahunan). Test ini memverifikasi matriks
 * (semua PSN) dan halaman detail (per PSN) menampilkan data dari seluruh
 * bagian yang relevan.
 */
class ProjectProfileTest extends TestCase
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

    public function test_matriks_project_profile_menampilkan_daftar_psn(): void
    {
        $this->actingAsSuperAdmin();
        $klaster = RefKlaster::create(['nama_klaster' => 'Energi']);

        $psnLengkap = Psn::create([
            'nama_psn' => 'PSN Lengkap',
            'klaster_id' => $klaster->id,
            'tujuan_utama' => 'Tujuan utama yang cukup panjang untuk lolos validasi',
            'output_akhir' => 'Output akhir yang cukup panjang untuk lolos validasi',
        ]);
        RoProyek::create(['psn_id' => $psnLengkap->id, 'nama_ro' => 'RO 1']);
        RisikoPsn::create(['psn_id' => $psnLengkap->id, 'peristiwa_risiko' => 'Risiko 1']);
        IndikatorPsn::create(['psn_id' => $psnLengkap->id, 'nama_indikator' => 'Indikator 1']);
        TrisulaKontribusiPsn::create(['psn_id' => $psnLengkap->id, 'kategori_trisula' => 'Kemiskinan', 'nama_indikator' => 'Penyerapan Tenaga Kerja']);

        $psnKosong = Psn::create(['nama_psn' => 'PSN Kosong']);

        $response = $this->get(route('admin.project-profile.index'));

        $response->assertOk();
        $response->assertSee('PSN Lengkap');
        $response->assertSee('PSN Kosong');
        $response->assertSee(route('admin.project-profile.show', $psnLengkap), false);
    }

    /**
     * Kolom daftar Profile PSN disesuaikan dengan diagram referensi yang
     * dilampirkan: No, Kode PSN, Nama PSN, Sub Proyek, Lokasi, Klaster PSN,
     * Klaster PKPN, Status PSN, Pendanaan, Pengusul, Penanggung Jawab,
     * Pengelola, Kontraktor, Supervisi, Tahun Selesai.
     */
    public function test_daftar_project_profile_menampilkan_kolom_sesuai_diagram(): void
    {
        $this->actingAsSuperAdmin();
        $klaster = RefKlaster::create(['nama_klaster' => 'Direktif Presiden']);
        $status = \App\Models\RefStatusPsn::create(['nama_status' => 'Proyek Dalam Tahap Transaksi', 'urutan' => 1]);
        $provinsi = \App\Models\RefProvinsi::create(['nama_provinsi' => 'Aceh']);
        $pengusul = RefInstansi::create(['nama_instansi' => 'Kepala Badan Gizi Nasional']);
        $pengelola = RefInstansi::create(['nama_instansi' => 'Menteri Pekerjaan Umum']);
        $kontraktor = RefInstansi::create(['nama_instansi' => 'PT Kontraktor Contoh']);
        $supervisi = RefInstansi::create(['nama_instansi' => 'PT Supervisi Contoh']);
        $pj1 = RefInstansi::create(['nama_instansi' => 'Kepala Badan Gizi Nasional PJ']);
        $pj2 = RefInstansi::create(['nama_instansi' => 'Menteri Sosial']);

        $psn = Psn::create([
            'nama_psn' => 'Makan Bergizi Gratis',
            'nama_sub_proyek' => 'Sub Proyek Contoh',
            'kode_rkp' => 'DP.1-2026.1-01',
            'klaster_id' => $klaster->id,
            'status_psn_id' => $status->id,
            'provinsi_id' => $provinsi->id,
            'kabupaten_kota' => 'Kab. Aceh Selatan',
            'tipe_hierarki' => 'PKPN',
            'indikasi_sumber_pendanaan' => 'APBD',
            'pengusul_instansi_id' => $pengusul->id,
            'pengelola_instansi_id' => $pengelola->id,
            'kontraktor_instansi_id' => $kontraktor->id,
            'supervisi_instansi_id' => $supervisi->id,
            'tahun_penyelesaian' => 2027,
        ]);
        \App\Models\PsnPenanggungJawab::create(['psn_id' => $psn->id, 'instansi_id' => $pj1->id]);
        \App\Models\PsnPenanggungJawab::create(['psn_id' => $psn->id, 'instansi_id' => $pj2->id]);

        $response = $this->get(route('admin.project-profile.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['No', 'Kode PSN', 'Nama PSN', 'Sub Proyek', 'Lokasi', 'Klaster PSN', 'Klaster PKPN', 'Status PSN', 'Pendanaan', 'Pengusul', 'Penanggung Jawab', 'Pengelola', 'Kontraktor', 'Supervisi', 'Tahun Selesai']);
        $response->assertSee('DP.1-2026.1-01');
        $response->assertSee('Sub Proyek Contoh');
        $response->assertSee('Aceh - Kab. Aceh Selatan');
        $response->assertSee('Direktif Presiden');
        $response->assertSee('PKPN');
        $response->assertSee('Proyek Dalam Tahap Transaksi');
        $response->assertSee('APBD');
        $response->assertSee('Kepala Badan Gizi Nasional PJ, Menteri Sosial');
        $response->assertSee('PT Kontraktor Contoh');
        $response->assertSee('PT Supervisi Contoh');
        $response->assertSee('2027');
        $response->assertSee(route('admin.project-profile.show', $psn), false);
        $response->assertSee(route('admin.psn.gambaran-umum', $psn), false);
    }

    public function test_daftar_project_profile_bisa_diurutkan_dan_diatur_jumlah_per_halaman(): void
    {
        $this->actingAsSuperAdmin();
        Psn::create(['nama_psn' => 'Bendungan B', 'tahun_penyelesaian' => 2028]);
        Psn::create(['nama_psn' => 'Bendungan A', 'tahun_penyelesaian' => 2027]);

        $response = $this->get(route('admin.project-profile.index', ['sort' => 'nama_psn', 'direction' => 'asc', 'per_page' => 25]));

        $response->assertOk();
        $response->assertSeeInOrder(['Bendungan A', 'Bendungan B']);
    }

    public function test_download_xls_project_profile_mengikuti_filter_klaster_aktif(): void
    {
        $this->actingAsSuperAdmin();
        $energi = RefKlaster::create(['nama_klaster' => 'Energi']);
        $pangan = RefKlaster::create(['nama_klaster' => 'Pangan']);
        Psn::create(['nama_psn' => 'PSN Energi', 'klaster_id' => $energi->id]);
        Psn::create(['nama_psn' => 'PSN Pangan', 'klaster_id' => $pangan->id]);

        $response = $this->get(route('admin.project-profile.export', ['klaster_id' => $energi->id]));

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }

    public function test_matriks_project_profile_bisa_difilter_per_klaster(): void
    {
        $this->actingAsSuperAdmin();
        $energi = RefKlaster::create(['nama_klaster' => 'Energi']);
        $pangan = RefKlaster::create(['nama_klaster' => 'Pangan']);

        Psn::create(['nama_psn' => 'PSN Energi', 'klaster_id' => $energi->id]);
        Psn::create(['nama_psn' => 'PSN Pangan', 'klaster_id' => $pangan->id]);

        $response = $this->get(route('admin.project-profile.index', ['klaster_id' => $energi->id]));

        $response->assertOk();
        $response->assertSee('PSN Energi');
        $response->assertDontSee('PSN Pangan');
    }

    public function test_detail_project_profile_menampilkan_seluruh_bagian_perencanaan(): void
    {
        $this->actingAsSuperAdmin();
        $instansi = RefInstansi::create(['nama_instansi' => 'Kementerian PUPR']);
        $pic = RefPic::create(['nama_pic' => 'Budi Santoso', 'email' => 'budi@bappenas.go.id']);

        $psn = Psn::create([
            'nama_psn' => 'Bendungan Contoh',
            'nama_sub_proyek' => 'Paket 1',
            'tujuan_utama' => 'Tujuan utama yang cukup panjang untuk lolos validasi',
            'output_akhir' => 'Output akhir yang cukup panjang untuk lolos validasi',
            'pengusul_instansi_id' => $instansi->id,
        ]);

        StakeholderPsn::create(['psn_id' => $psn->id, 'nama_pemangku_kepentingan' => 'Kementerian PUPR', 'kategori_aktor' => 'State', 'level_kelembagaan' => 1]);

        $indikator = IndikatorPsn::create(['psn_id' => $psn->id, 'nama_indikator' => 'Persentase Penyelesaian Fisik', 'satuan' => 'Persen', 'baseline' => '0']);
        IndikatorPsnTargetTahunan::create(['indikator_id' => $indikator->id, 'tahun' => 2026, 'target_akhir' => 100, 'target' => 50, 'realisasi' => 20]);

        $trisula = TrisulaKontribusiPsn::create(['psn_id' => $psn->id, 'kategori_trisula' => 'Kemiskinan', 'nama_indikator' => 'Penyerapan Tenaga Kerja', 'satuan' => 'Orang']);
        TrisulaTargetPeriode::create(['kontribusi_id' => $trisula->id, 'tipe_periode' => 'TAHUNAN', 'tahun' => 2026, 'target' => 100, 'realisasi' => 40]);
        TrisulaTargetPeriode::create(['kontribusi_id' => $trisula->id, 'tipe_periode' => 'TRIWULANAN', 'tahun' => 2026, 'triwulan' => 2, 'target' => 25, 'realisasi' => 20]);

        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'Pembangunan Bendungan Utama', 'tipe' => 'RO', 'target_akhir' => '100', 'lokasi' => 'Jawa Barat']);
        RoTargetPeriode::create(['ro_id' => $ro->id, 'tahun' => 2026, 'tipe_periode' => 'TRIWULANAN', 'triwulan' => 2, 'target' => 30, 'realisasi_fisik' => 25]);

        RisikoPsn::create([
            'psn_id' => $psn->id,
            'peristiwa_risiko' => 'Keterlambatan pembebasan lahan',
            'level_risiko_awal' => 'Tinggi',
            'penanggung_jawab_id' => $pic->id,
            'ro_id' => $ro->id,
            'is_titik_kritis' => true,
        ]);

        $response = $this->get(route('admin.project-profile.show', $psn));

        $response->assertOk();
        $response->assertSee('Paket 1');
        $response->assertSee('Persentase Penyelesaian Fisik');
        $response->assertSee('Penyerapan Tenaga Kerja');
        $response->assertSee('Pembangunan Bendungan Utama');
        $response->assertSee('Keterlambatan pembebasan lahan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Critical Path');
        $response->assertSee('Kementerian PUPR');
    }

    public function test_detail_project_profile_penjabaran_tahunan_menampilkan_periode_sesuai_filter_tahun(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'RO Contoh', 'tipe' => 'RO', 'target_akhir' => '100', 'lokasi' => 'Jawa Barat']);

        RoTargetPeriode::create(['ro_id' => $ro->id, 'tahun' => 2026, 'tipe_periode' => 'TRIWULANAN', 'triwulan' => 1, 'target' => 25, 'permasalahan' => 'Cuaca ekstrem menghambat konstruksi']);
        RoTargetPeriode::create(['ro_id' => $ro->id, 'tahun' => 2027, 'tipe_periode' => 'TRIWULANAN', 'triwulan' => 1, 'target' => 50, 'permasalahan' => 'Isu tahun berikutnya']);

        $response2026 = $this->get(route('admin.project-profile.show', $psn).'?tahun=2026');
        $response2026->assertOk();
        $response2026->assertSee('Cuaca ekstrem menghambat konstruksi');
        $response2026->assertDontSee('Isu tahun berikutnya');

        $response2027 = $this->get(route('admin.project-profile.show', $psn).'?tahun=2027');
        $response2027->assertOk();
        $response2027->assertSee('Isu tahun berikutnya');
        $response2027->assertDontSee('Cuaca ekstrem menghambat konstruksi');
    }

    public function test_viewer_tanpa_akses_psn_view_ditolak(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user);

        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.project-profile.index'))->assertForbidden();
        $this->get(route('admin.project-profile.show', $psn))->assertForbidden();
    }
}
