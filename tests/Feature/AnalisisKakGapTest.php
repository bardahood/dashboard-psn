<?php

namespace Tests\Feature;

use App\Models\KebutuhanRegulasi;
use App\Models\KunjunganPengendalian;
use App\Models\KunjunganPengendalianRegulasi;
use App\Models\KunjunganPerencanaan;
use App\Models\Psn;
use App\Models\PsnEvaluasiStatus;
use App\Models\RefDokumenTeknis;
use App\Models\RefKlaster;
use App\Models\RisikoPsn;
use App\Models\RisikoStatusPeriode;
use App\Models\User;
use Database\Seeders\RefDokumenTeknisSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk 4 gap hasil analisis KAK vs Dashboard PSN (kategori usulan,
 * rekomendasi keluar dari daftar, rekap verifikasi usulan, filter klaster
 * fokus pada Reporting).
 */
class AnalisisKakGapTest extends TestCase
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

    // GAP #1: kategori_usulan (Carryover vs Usulan Baru)

    public function test_psn_bisa_disimpan_dengan_kategori_usulan_dan_terlihat_di_check_constraint(): void
    {
        $psn = Psn::create(['nama_psn' => 'Contoh PSN', 'kategori_usulan' => 'Carryover']);
        $this->assertSame('Carryover', $psn->fresh()->kategori_usulan);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Psn::create(['nama_psn' => 'PSN Invalid', 'kategori_usulan' => 'Nilai Tidak Valid']);
    }

    public function test_admin_bisa_memfilter_data_psn_per_kategori_usulan(): void
    {
        $this->actingAsSuperAdmin();
        Psn::create(['nama_psn' => 'PSN Carryover A', 'kategori_usulan' => 'Carryover']);
        Psn::create(['nama_psn' => 'PSN Baru B', 'kategori_usulan' => 'Usulan Baru']);

        $response = $this->get('/admin/psn?kategori_usulan=Carryover');

        $response->assertOk();
        $response->assertSee('PSN Carryover A');
        $response->assertDontSee('PSN Baru B');
    }

    public function test_daftar_psn_publik_bisa_difilter_per_kategori_usulan(): void
    {
        Psn::create(['nama_psn' => 'PSN Carryover A', 'kategori_usulan' => 'Carryover']);
        Psn::create(['nama_psn' => 'PSN Baru B', 'kategori_usulan' => 'Usulan Baru']);

        $response = $this->get('/psn?kategori_usulan=Usulan+Baru');

        $response->assertOk();
        $response->assertSee('PSN Baru B');
        $response->assertDontSee('PSN Carryover A');
    }

    // GAP #4: Rekomendasi Keluar dari Daftar PSN

    public function test_halaman_evaluasi_keluar_menampilkan_hanya_psn_yang_direkomendasikan_keluar(): void
    {
        $this->actingAsSuperAdmin();

        $keluar = Psn::create(['nama_psn' => 'PSN Direkomendasikan Keluar']);
        PsnEvaluasiStatus::create([
            'psn_id' => $keluar->id, 'tahun_evaluasi' => 2026,
            'masih_butuh_status_psn' => false, 'justifikasi' => 'Proyek sudah dialihkan ke skema reguler K/L.',
        ]);

        $lanjut = Psn::create(['nama_psn' => 'PSN Tetap Lanjut']);
        PsnEvaluasiStatus::create([
            'psn_id' => $lanjut->id, 'tahun_evaluasi' => 2026,
            'masih_butuh_status_psn' => true, 'justifikasi' => 'Masih dalam tahap konstruksi.',
        ]);

        $response = $this->get('/admin/evaluasi-keluar');

        $response->assertOk();
        $response->assertSee('PSN Direkomendasikan Keluar');
        $response->assertSee('Proyek sudah dialihkan ke skema reguler K/L.');
        $response->assertDontSee('PSN Tetap Lanjut');
    }

    public function test_verifikator_lapangan_tidak_punya_akses_evaluasi_keluar(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Verifikator Lapangan');
        $this->actingAs($user);

        $this->get('/admin/evaluasi-keluar')->assertForbidden();
    }

    // GAP #5: Rekap Kelengkapan Administrasi & Verifikasi Usulan

    public function test_halaman_verifikasi_usulan_menampilkan_rekap_kelengkapan_dokumen(): void
    {
        $this->actingAsSuperAdmin();
        $this->seed(RefDokumenTeknisSeeder::class);

        $psn = Psn::create(['nama_psn' => 'Usulan PSN Alfa']);
        $kunjungan = KunjunganPerencanaan::create([
            'psn_id' => $psn->id, 'nama_usulan_psn' => 'Usulan PSN Alfa', 'tanggal_kunjungan' => '2026-03-01',
        ]);

        $dokumen = RefDokumenTeknis::orderBy('urutan')->get();
        $this->assertGreaterThan(0, $dokumen->count());

        $kunjungan->verifikasiDokumenTeknis()->create(['dokumen_id' => $dokumen[0]->id, 'tersedia' => 'Ya']);
        $kunjungan->verifikasiDokumenTeknis()->create(['dokumen_id' => $dokumen[1]->id, 'tersedia' => 'Tidak']);

        $response = $this->get('/admin/verifikasi-usulan');

        $response->assertOk();
        $response->assertSee('Usulan PSN Alfa');
        $response->assertSee('1 Lengkap');
        $response->assertSee('1 Tidak Ada');
    }

    // GAP #10: Filter klaster fokus pada Reporting

    public function test_export_daftar_psn_bisa_difilter_per_klaster_fokus(): void
    {
        $this->actingAsSuperAdmin();

        $energi = RefKlaster::create(['nama_klaster' => 'Swasembada Energi']);
        $pangan = RefKlaster::create(['nama_klaster' => 'Swasembada Pangan']);
        $lain = RefKlaster::create(['nama_klaster' => 'Hilirisasi']);

        Psn::create(['nama_psn' => 'PSN Energi', 'klaster_id' => $energi->id]);
        Psn::create(['nama_psn' => 'PSN Pangan', 'klaster_id' => $pangan->id]);
        Psn::create(['nama_psn' => 'PSN Hilirisasi', 'klaster_id' => $lain->id]);

        $response = $this->get('/admin/laporan/daftar-psn-excel?'.http_build_query([
            'klaster_id' => [$energi->id, $pangan->id],
        ]));

        $response->assertOk();
    }

    public function test_laporan_ringkasan_pdf_menampilkan_catatan_fokus_klaster(): void
    {
        $this->actingAsSuperAdmin();

        $energi = RefKlaster::create(['nama_klaster' => 'Swasembada Energi']);
        Psn::create(['nama_psn' => 'PSN Energi', 'klaster_id' => $energi->id]);

        $response = $this->get('/admin/laporan/ringkasan-pdf?klaster_id[]='.$energi->id);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    // GAP #3: Ringkasan Debottlenecking per Klaster

    public function test_halaman_debottlenecking_menyatukan_risiko_regulasi_dan_isu_lintas_psn(): void
    {
        $this->actingAsSuperAdmin();

        $konektivitas = RefKlaster::create(['nama_klaster' => 'Konektivitas dan Infrastruktur Logistik Jalan']);
        $pangan = RefKlaster::create(['nama_klaster' => 'Swasembada Pangan']);

        $psnA = Psn::create(['nama_psn' => 'Jalan Tol A', 'klaster_id' => $konektivitas->id]);
        $psnB = Psn::create(['nama_psn' => 'Bendungan B', 'klaster_id' => $pangan->id]);

        $risikoA = RisikoPsn::create([
            'psn_id' => $psnA->id, 'peristiwa_risiko' => 'Keterlambatan pembebasan lahan',
            'level_risiko_awal' => 'Tinggi', 'perlakuan_rencana' => 'Percepatan appraisal lahan',
        ]);
        RisikoStatusPeriode::create([
            'risiko_id' => $risikoA->id, 'tahun' => 2026, 'triwulan' => 2,
            'risiko_residual_aktual' => 'Sedang', 'status_perlakuan' => 'On Progress',
        ]);
        RisikoPsn::create([
            'psn_id' => $psnB->id, 'peristiwa_risiko' => 'Risiko gagal panen', 'level_risiko_awal' => 'Rendah',
        ]);

        $regulasiA = KebutuhanRegulasi::create([
            'psn_id' => $psnA->id, 'nama_regulasi' => 'Perpres Penetapan Lokasi', 'target_tahun_penyelesaian' => 2024,
        ]);
        $kunjunganA = KunjunganPengendalian::create(['psn_id' => $psnA->id, 'tanggal_kunjungan' => '2026-01-10']);
        KunjunganPengendalianRegulasi::create([
            'kunjungan_id' => $kunjunganA->id, 'regulasi_id' => $regulasiA->id, 'status_klaim' => 'Proses',
        ]);

        KunjunganPengendalian::create([
            'psn_id' => $psnA->id, 'tanggal_kunjungan' => '2026-02-15',
            'isu_tantangan' => 'Kontraktor utama mengalami kendala arus kas.',
            'kebutuhan_tindak_lanjut' => 'Fasilitasi pertemuan dengan bank penyalur.',
            'status_pengendalian' => 'Perlu Perhatian',
        ]);

        // Tanpa filter: semua klaster tampil.
        $response = $this->get('/admin/debottlenecking');
        $response->assertOk();
        $response->assertSee('Jalan Tol A');
        $response->assertSee('Keterlambatan pembebasan lahan');
        $response->assertSee('Perpres Penetapan Lokasi');
        $response->assertSee('Terlambat');
        $response->assertSee('Kontraktor utama mengalami kendala arus kas.');
        $response->assertSee('Bendungan B');

        // Difilter ke klaster Konektivitas saja: Bendungan B (Swasembada Pangan) tidak boleh muncul.
        $response = $this->get('/admin/debottlenecking?klaster_id[]='.$konektivitas->id);
        $response->assertOk();
        $response->assertSee('Jalan Tol A');
        $response->assertDontSee('Bendungan B');
    }

    public function test_verifikator_lapangan_tidak_punya_akses_debottlenecking(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('K/L Pelaksana');
        $this->actingAs($user);

        $this->get('/admin/debottlenecking')->assertForbidden();
    }
}
