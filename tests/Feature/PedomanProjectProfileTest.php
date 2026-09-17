<?php

namespace Tests\Feature;

use App\Livewire\Admin\AnnualTargetManager;
use App\Livewire\Admin\RisikoManager;
use App\Models\Psn;
use App\Models\RefPic;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use App\Models\TrisulaKontribusiPsn;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test hasil analisis kesesuaian dengan "Pedoman Project Profile PSN"
 * (dokumen resmi yang dilampirkan): PJ Risiko & target tenggat, Critical
 * Path berbasis Risiko (clustering ke RO), sub-kategori Indeks Modal
 * Manusia pada Trisula SDM, dan Visualisasi Kerangka Kelembagaan.
 */
class PedomanProjectProfileTest extends TestCase
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

    public function test_profil_risiko_bisa_diisi_pj_target_tenggat_dan_ditandai_titik_kritis_pada_ro_tertentu(): void
    {
        $this->actingAsSuperAdmin();

        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $pic = RefPic::create(['nama_pic' => 'Budi Santoso', 'email' => 'budi@bappenas.go.id']);
        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'Pembangunan Bendungan Utama']);

        Livewire::test(RisikoManager::class, ['psn' => $psn])
            ->set('form.peristiwa_risiko', 'Keterlambatan pembebasan lahan')
            ->set('form.level_risiko_awal', 'Tinggi')
            ->set('form.penanggung_jawab_id', $pic->id)
            ->set('form.target_mulai', '2026-01-01')
            ->set('form.target_selesai', '2026-06-30')
            ->set('form.ro_id', $ro->id)
            ->set('form.is_titik_kritis', true)
            ->set('form.tahun_pelaksanaan_perlakuan', 2026)
            ->call('save')
            ->assertHasNoErrors();

        $risiko = RisikoPsn::firstOrFail();
        $this->assertSame($pic->id, $risiko->penanggung_jawab_id);
        $this->assertSame('2026-01-01', $risiko->target_mulai->toDateString());
        $this->assertSame('2026-06-30', $risiko->target_selesai->toDateString());
        $this->assertSame($ro->id, $risiko->ro_id);
        $this->assertTrue($risiko->is_titik_kritis);
        $this->assertSame(2026, $risiko->tahun_pelaksanaan_perlakuan);
    }

    public function test_debottlenecking_menampilkan_titik_kritis_dan_pj_risiko(): void
    {
        $this->actingAsSuperAdmin();

        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $pic = RefPic::create(['nama_pic' => 'Budi Santoso', 'email' => 'budi@bappenas.go.id']);
        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'Pembangunan Bendungan Utama']);

        RisikoPsn::create([
            'psn_id' => $psn->id, 'peristiwa_risiko' => 'Keterlambatan pembebasan lahan',
            'level_risiko_awal' => 'Tinggi', 'penanggung_jawab_id' => $pic->id, 'ro_id' => $ro->id,
            'is_titik_kritis' => true,
        ]);

        $response = $this->get('/admin/debottlenecking');

        $response->assertOk();
        $response->assertSee('Titik Kritis');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Pembangunan Bendungan Utama');
    }

    public function test_kontribusi_trisula_sdm_bisa_diisi_sub_kategori_pendidikan_atau_kesehatan(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'trisula'])
            ->set('parentForm.kategori_trisula', 'Sumber Daya Manusia')
            ->set('parentForm.sub_kategori_sdm', 'Kesehatan')
            ->set('parentForm.nama_indikator', 'Persentase rumah tangga dengan akses air minum SPAM')
            ->call('saveParent')
            ->assertHasNoErrors();

        $kontribusi = TrisulaKontribusiPsn::firstOrFail();
        $this->assertSame('Sumber Daya Manusia', $kontribusi->kategori_trisula);
        $this->assertSame('Kesehatan', $kontribusi->sub_kategori_sdm);
    }

    public function test_diagram_kerangka_kelembagaan_bisa_diunggah_ditampilkan_dan_dihapus(): void
    {
        Storage::fake('public');
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $file = UploadedFile::fake()->image('kerangka-kelembagaan.png');

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'diagram_kelembagaan' => $file,
        ])->assertRedirect();

        $psn->refresh();
        $this->assertNotNull($psn->diagram_kelembagaan_path);
        Storage::disk('public')->assertExists($psn->diagram_kelembagaan_path);

        // Tampil di halaman publik.
        $this->get("/psn/{$psn->id}")->assertOk()->assertSee('Visualisasi Kerangka Kelembagaan');

        $pathLama = $psn->diagram_kelembagaan_path;

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'hapus_diagram_kelembagaan' => '1',
        ])->assertRedirect();

        $psn->refresh();
        $this->assertNull($psn->diagram_kelembagaan_path);
        Storage::disk('public')->assertMissing($pathLama);
    }

    public function test_upload_diagram_kelembagaan_ditolak_bila_bukan_gambar(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $file = UploadedFile::fake()->create('dokumen.pdf', 100);

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'diagram_kelembagaan' => $file,
        ])->assertSessionHasErrors('diagram_kelembagaan');
    }
}
