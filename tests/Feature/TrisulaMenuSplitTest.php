<?php

namespace Tests\Feature;

use App\Livewire\Admin\AnnualTargetManager;
use App\Models\Psn;
use App\Models\TrisulaKontribusiPsn;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Koreksi menu profil PSN: Trisula BUKAN tab tersendiri (sempat dibuat begitu
 * pada restrukturisasi 5-tab sebelumnya, "karena sudah menggabungkan target
 * tahunan & triwulanan dalam satu komponen") -- dicek ulang terhadap dokumen
 * resmi "Struktur Project Profile" (sudah lebih dulu tercermin benar pada
 * halaman baca-saja /admin/project-profile) yang membagi Trisula: target
 * TAHUNAN masuk Perencanaan, target TRIWULANAN masuk Penjabaran Tahunan.
 * Tab Trisula & route admin.psn.trisula dihapus; AnnualTargetManager diberi
 * mode tampilan ('tahunan'/'triwulanan') supaya kedua tab bisa menampilkan
 * bagian Trisula yang relevan saja tanpa duplikasi komponen master data.
 */
class TrisulaMenuSplitTest extends TestCase
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

    public function test_route_trisula_tersendiri_sudah_dihapus(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.psn.trisula'));
        $this->get("/admin/psn/{$psn->id}/trisula")->assertNotFound();
    }

    public function test_tab_perencanaan_menampilkan_form_dan_grid_tahunan_trisula_tanpa_panel_triwulanan(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.psn.perencanaan', $psn))
            ->assertOk()
            ->assertSee('Tambah Kontribusi Trisula Pembangunan')
            ->assertDontSee('Target/Realisasi Triwulanan');
    }

    public function test_tab_penjabaran_menampilkan_panel_triwulanan_trisula_tanpa_form_master(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        TrisulaKontribusiPsn::create([
            'psn_id' => $psn->id,
            'kategori_trisula' => 'Sumber Daya Manusia',
            'nama_indikator' => 'Indikator SDM Contoh',
        ]);

        $this->get(route('admin.psn.penjabaran', $psn))
            ->assertOk()
            ->assertDontSee('Tambah Kontribusi Trisula Pembangunan')
            ->assertSee('Indikator SDM Contoh')
            ->assertSee('Kelola Target Triwulanan');
    }

    public function test_tab_penjabaran_trisula_kosong_mengarahkan_ke_tab_perencanaan(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.psn.penjabaran', $psn))
            ->assertOk()
            ->assertSee('Belum ada data Kontribusi Trisula Pembangunan')
            ->assertSee(route('admin.psn.perencanaan', $psn), false);
    }

    public function test_tampilan_tahunan_menyembunyikan_panel_triwulanan_walau_type_trisula(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $kontribusi = TrisulaKontribusiPsn::create([
            'psn_id' => $psn->id,
            'kategori_trisula' => 'Sumber Daya Manusia',
            'nama_indikator' => 'Indikator SDM Contoh',
        ]);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'trisula', 'tampilan' => 'tahunan'])
            ->call('toggleYears', $kontribusi->id)
            ->assertSee('Simpan Target Tahunan')
            ->assertDontSee('Tambah Target Triwulanan')
            ->assertSee('Ubah')
            ->assertSee('Hapus');
    }

    public function test_tampilan_triwulanan_menyembunyikan_grid_tahunan_dan_aksi_ubah_hapus_master(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $kontribusi = TrisulaKontribusiPsn::create([
            'psn_id' => $psn->id,
            'kategori_trisula' => 'Sumber Daya Manusia',
            'nama_indikator' => 'Indikator SDM Contoh',
        ]);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'trisula', 'tampilan' => 'triwulanan'])
            ->assertDontSee('Tambah Kontribusi Trisula Pembangunan')
            ->assertDontSee('Ubah')
            ->assertDontSee('Hapus')
            ->call('toggleYears', $kontribusi->id)
            ->assertDontSee('Simpan Target Tahunan')
            ->assertSee('Tambah Target Triwulanan')
            ->set('twForm.tahun', 2026)
            ->set('twForm.triwulan', 1)
            ->set('twForm.target', 10)
            ->call('saveTw')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('trisula_target_periode', [
            'kontribusi_id' => $kontribusi->id,
            'tipe_periode' => 'TRIWULANAN',
            'tahun' => 2026,
            'triwulan' => 1,
            'target' => 10,
        ]);
    }
}
