<?php

namespace Tests\Feature;

use App\Livewire\Admin\RisikoManager;
use App\Livewire\Admin\RoProyekManager;
use App\Livewire\Admin\SubResourceManager;
use App\Models\DasarHukumPsn;
use App\Models\Psn;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireSubResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        return $user;
    }

    public function test_sub_resource_manager_bisa_tambah_dan_hapus_dasar_hukum(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        Livewire::test(SubResourceManager::class, ['psn' => $psn, 'type' => 'dasar_hukum'])
            ->set('form.nama_regulasi', 'Perpres No. 68 Tahun 2026')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('dasar_hukum_psn', [
            'psn_id' => $psn->id,
            'nama_regulasi' => 'Perpres No. 68 Tahun 2026',
        ]);

        $record = DasarHukumPsn::first();

        Livewire::test(SubResourceManager::class, ['psn' => $psn, 'type' => 'dasar_hukum'])
            ->call('delete', $record->id);

        $this->assertDatabaseCount('dasar_hukum_psn', 0);
    }

    public function test_ro_proyek_manager_bisa_membuat_hierarki_dan_periode(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        $component = Livewire::test(RoProyekManager::class, ['psn' => $psn])
            ->set('form.nama_ro', 'RO Induk')
            ->set('form.tipe', 'RO')
            ->set('form.is_ro_kunci', true)
            ->set('form.target_akhir', '100 KM')
            ->set('form.lokasi', 'Provinsi Jawa Barat')
            ->call('save')
            ->assertHasNoErrors();

        $roInduk = RoProyek::where('nama_ro', 'RO Induk')->firstOrFail();
        $this->assertTrue($roInduk->is_ro_kunci);

        $component
            ->set('form.nama_ro', 'Aktivitas Turunan')
            ->set('form.tipe', 'Aktivitas')
            ->set('form.ro_induk_id', $roInduk->id)
            ->set('form.target_akhir', '50 KM')
            ->set('form.lokasi', 'Provinsi Jawa Barat')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ro_proyek', [
            'nama_ro' => 'Aktivitas Turunan',
            'ro_induk_id' => $roInduk->id,
            'tipe' => 'Aktivitas',
        ]);

        $component
            ->call('togglePeriode', $roInduk->id)
            ->set('periodeForm.tahun', 2026)
            ->set('periodeForm.tipe_periode', 'TRIWULANAN')
            ->set('periodeForm.triwulan', 1)
            ->set('periodeForm.target', 25)
            ->call('addPeriode')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ro_target_periode', [
            'ro_id' => $roInduk->id,
            'tahun' => 2026,
            'triwulan' => 1,
        ]);
    }

    public function test_risiko_manager_bisa_tambah_laporan_triwulanan(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        Livewire::test(RisikoManager::class, ['psn' => $psn])
            ->set('form.peristiwa_risiko', 'Keterlambatan pembangunan infrastruktur')
            ->set('form.level_risiko_awal', 'Tinggi')
            ->call('save')
            ->assertHasNoErrors();

        $risiko = RisikoPsn::firstOrFail();

        Livewire::test(RisikoManager::class, ['psn' => $psn])
            ->call('toggleStatus', $risiko->id)
            ->set('statusForm.tahun', 2026)
            ->set('statusForm.triwulan', 2)
            ->set('statusForm.status_perlakuan', 'On Progress')
            ->call('addStatus')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('risiko_status_periode', [
            'risiko_id' => $risiko->id,
            'tahun' => 2026,
            'triwulan' => 2,
            'status_perlakuan' => 'On Progress',
        ]);
    }

    public function test_viewer_internal_tidak_bisa_mengubah_sub_resource_psn(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');
        $this->actingAs($user);

        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        Livewire::test(SubResourceManager::class, ['psn' => $psn, 'type' => 'dasar_hukum'])
            ->assertForbidden();
    }
}
