<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\User;
use App\Support\DocxTableParser;
use App\Support\Rkp2027CarryoverAnalyzer;
use Database\Seeders\MatriksSandinganPsnSeeder;
use Database\Seeders\RefKlasterSeeder;
use Database\Seeders\RefProvinsiSeeder;
use Database\Seeders\RefStatusKetersediaanSeeder;
use Database\Seeders\RefSumberDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menyandingkan lampiran "Daftar PSN dalam RKP 2027" (bundled .docx) dengan
 * data PSN dashboard (hasil impor Matrik Sandingan, bersumber dari RKP
 * Pemutakhiran 2026) untuk identifikasi proyek carryover ke 2027.
 */
class Rkp2027CarryoverAnalyzerTest extends TestCase
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

    protected function seedRefDataDanMatriks(): void
    {
        $this->seed(RefKlasterSeeder::class);
        $this->seed(RefProvinsiSeeder::class);
        $this->seed(RefSumberDataSeeder::class);
        $this->seed(RefStatusKetersediaanSeeder::class);
        $this->seed(MatriksSandinganPsnSeeder::class);
    }

    public function test_docx_table_parser_membaca_seluruh_tabel_lampiran_rkp_2027(): void
    {
        $path = database_path('seeders/data/Daftar_PSN_RKP_2027.docx');
        $this->assertFileExists($path);

        $blocks = app(DocxTableParser::class)->parseBlocks($path);
        $tables = array_filter($blocks, fn ($b) => $b['type'] === 'table');

        $this->assertCount(24, $tables);
        $this->assertSame(346, array_sum(array_map(fn ($t) => count($t['rows']), $tables)));
    }

    public function test_analisis_carryover_mengelompokkan_psn_sesuai_kecocokan_dengan_daftar_rkp_2027(): void
    {
        $this->seedRefDataDanMatriks();
        $this->assertSame(380, Psn::count());

        $path = database_path('seeders/data/Daftar_PSN_RKP_2027.docx');
        $hasil = app(Rkp2027CarryoverAnalyzer::class)->analisis($path);

        $this->assertSame(288, $hasil['carryover']->count());
        $this->assertSame(15, $hasil['perlu_ditinjau']->count());
        $this->assertSame(98, $hasil['tidak_ditemukan_lagi']->count());

        // "Makan Bergizi Gratis" ada di kedua sumber -> harus terklasifikasi carryover.
        $mbg = Psn::where('nama_psn', 'Makan Bergizi Gratis')->firstOrFail();
        $this->assertTrue($hasil['carryover']->pluck('psn_id')->contains($mbg->id));
    }

    public function test_terapkan_kategori_carryover_mengisi_kolom_kategori_usulan_tanpa_menimpa_isian_manual(): void
    {
        $this->seedRefDataDanMatriks();

        $mbg = Psn::where('nama_psn', 'Makan Bergizi Gratis')->firstOrFail();
        $sudahDiisi = Psn::where('nama_psn', 'Pembangunan Sekolah Rakyat')->firstOrFail();
        $sudahDiisi->update(['kategori_usulan' => 'Usulan Baru']);

        $path = database_path('seeders/data/Daftar_PSN_RKP_2027.docx');
        $analyzer = app(Rkp2027CarryoverAnalyzer::class);
        $hasil = $analyzer->analisis($path);
        $analyzer->terapkanKategoriCarryover($hasil['carryover']);

        $this->assertSame('Carryover', $mbg->fresh()->kategori_usulan);
        // Isian manual sebelumnya tidak boleh tertimpa.
        $this->assertSame('Usulan Baru', $sudahDiisi->fresh()->kategori_usulan);
    }

    public function test_terapkan_ke_matriks_sandingan_mengisi_kolom_rkp_2027_untuk_seluruh_psn(): void
    {
        $this->seedRefDataDanMatriks();

        $path = database_path('seeders/data/Daftar_PSN_RKP_2027.docx');
        $analyzer = app(Rkp2027CarryoverAnalyzer::class);
        $hasil = $analyzer->analisis($path);
        $jumlah = $analyzer->terapkanKeMatriksSandingan($hasil);

        $this->assertSame(380, $jumlah);
        $this->assertSame(282, DB::table('v_psn_sandingan_sumber')->where('rkp_2027', true)->count());
        $this->assertSame(98, DB::table('v_psn_sandingan_sumber')->where('rkp_2027', false)->count());

        $mbg = Psn::where('nama_psn', 'Makan Bergizi Gratis')->firstOrFail();
        $this->assertSame(1, DB::table('v_psn_sandingan_sumber')->where('psn_id', $mbg->id)->where('rkp_2027', true)->count());
    }

    public function test_halaman_admin_analisis_rkp2027_menampilkan_ringkasan_dan_bisa_menerapkan_kategori(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedRefDataDanMatriks();

        $response = $this->get('/admin/analisis-rkp2027');
        $response->assertOk();
        $response->assertSee('Analisis Carryover RKP 2027');
        $response->assertSee('288');

        $this->post('/admin/analisis-rkp2027/terapkan')->assertRedirect('/admin/analisis-rkp2027');

        $mbg = Psn::where('nama_psn', 'Makan Bergizi Gratis')->firstOrFail();
        $this->assertSame('Carryover', $mbg->fresh()->kategori_usulan);
        $this->assertSame(282, DB::table('v_psn_sandingan_sumber')->where('rkp_2027', true)->count());
    }

    public function test_halaman_matriks_sandingan_menampilkan_kolom_rkp_2027(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedRefDataDanMatriks();

        $path = database_path('seeders/data/Daftar_PSN_RKP_2027.docx');
        $analyzer = app(Rkp2027CarryoverAnalyzer::class);
        $analyzer->terapkanKeMatriksSandingan($analyzer->analisis($path));

        $response = $this->get('/admin/matriks-sandingan?q=Makan+Bergizi+Gratis');
        $response->assertOk();
        $response->assertSee('RKP 2027');
    }

    public function test_halaman_analisis_rkp2027_ditolak_tanpa_izin_profil_manage(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('K/L Pelaksana');
        $this->actingAs($user);

        $this->get('/admin/analisis-rkp2027')->assertForbidden();
    }
}
