<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\RoProyek;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cek kesesuaian tab "Perencanaan" terhadap diagram resmi Struktur Project
 * Profile yang dilampirkan: Indikator (Target Tahunan/Agregat dari TW),
 * Kontribusi Terhadap Trisula Pembangunan (Target Tahunan/Agregat dari
 * Target TW), Penerima Manfaat (Target Tahunan), Peristiwa/Kategori/Level/
 * Perlakuan/PJ Risiko, Critical Path RO/Proyek/Non RO (Tahunan).
 *
 * Indikator, Kontribusi Trisula (tahunan), Penerima Manfaat, dan seluruh
 * field Risiko (peristiwa_risiko/kategori_risiko/level_risiko_awal/
 * perlakuan_rencana/penanggung_jawab_id) sudah tercermin persis -- lihat
 * TrisulaMenuSplitTest utk agregat Trisula dari TW dan RisalahRapat21Sept
 * Test utk field Risiko. Yang diperbaiki di sini murni istilah "Critical
 * Path" pada ringkasan RO/Proyek/Non RO (sebelumnya berjudul generik "RO/
 * Proyek/Non RO (Ringkasan Tahunan)" tanpa penanda Critical Path pada baris
 * Aktivitas turunan, padahal RO/ProyekManager sendiri sudah memakai istilah
 * "Critical Path" utk badge is_ro_kunci pada level Aktivitas).
 */
class PerencanaanProjectProfileTest extends TestCase
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

    public function test_ringkasan_ro_proyek_berjudul_critical_path_sesuai_diagram(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.psn.perencanaan', $psn))
            ->assertOk()
            ->assertSee('Critical Path RO/Proyek/Non RO (Tahunan)');
    }

    public function test_badge_critical_path_tampil_untuk_aktivitas_turunan_bertanda_kunci(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'RO Induk', 'tipe' => 'RO']);
        RoProyek::create([
            'psn_id' => $psn->id,
            'nama_ro' => 'Aktivitas Kritis',
            'tipe' => 'Aktivitas',
            'ro_induk_id' => $ro->id,
            'is_ro_kunci' => true,
        ]);
        RoProyek::create([
            'psn_id' => $psn->id,
            'nama_ro' => 'Aktivitas Biasa',
            'tipe' => 'Aktivitas',
            'ro_induk_id' => $ro->id,
            'is_ro_kunci' => false,
        ]);

        $response = $this->get(route('admin.psn.perencanaan', $psn))->assertOk();
        $response->assertSeeInOrder(['Aktivitas Kritis', 'Critical Path']);
        $response->assertSee('Aktivitas Biasa');
    }
}
