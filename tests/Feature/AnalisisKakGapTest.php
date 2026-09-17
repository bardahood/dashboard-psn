<?php

namespace Tests\Feature;

use App\Models\KunjunganPerencanaan;
use App\Models\Psn;
use App\Models\PsnEvaluasiStatus;
use App\Models\RefDokumenTeknis;
use App\Models\RefKlaster;
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
}
