<?php

namespace Tests\Feature;

use App\Livewire\Admin\KunjunganPengendalianWizard;
use App\Models\KunjunganPengendalian;
use App\Models\Psn;
use App\Models\RisikoPsn;
use App\Models\RoProyek;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KunjunganPengendalianWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdminPengendalian(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin Pengendalian');
        $this->actingAs($user);

        return $user;
    }

    public function test_wizard_bisa_membuat_kunjungan_baru_dan_mengisi_bagian_a_sampai_c(): void
    {
        $this->actingAsAdminPengendalian();
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);
        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'RO A', 'tipe' => 'RO']);

        $component = Livewire::test(KunjunganPengendalianWizard::class)
            ->set('identitas.psn_id', $psn->id)
            ->set('identitas.tanggal_kunjungan', '2026-09-16')
            ->call('saveIdentitas')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kunjungan_pengendalian', ['psn_id' => $psn->id]);

        $component
            ->call('saveKelembagaan')
            ->assertSet('step', 3);

        $this->assertDatabaseCount('kunjungan_pengendalian_kelembagaan', 5);

        $component
            ->set('fisikForm.ro_id', $ro->id)
            ->set('fisikForm.target_periode_ini', 100)
            ->set('fisikForm.realisasi_fisik_klaim', 80)
            ->set('fisikForm.realisasi_fisik_verifikasi', 78)
            ->call('addFisik')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kunjungan_pengendalian_fisik', [
            'ro_id' => $ro->id,
            'kesesuaian' => 'Sesuai',
        ]);
    }

    public function test_skor_dan_rekomendasi_otomatis_berubah_mengikuti_data_verifikasi(): void
    {
        $this->actingAsAdminPengendalian();
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);
        $ro = RoProyek::create(['psn_id' => $psn->id, 'nama_ro' => 'RO A', 'tipe' => 'RO']);
        $risiko = RisikoPsn::create(['psn_id' => $psn->id, 'peristiwa_risiko' => 'Uji', 'risiko_residual_harapan' => 'Sedang']);

        $kunjungan = KunjunganPengendalian::create(['psn_id' => $psn->id, 'tanggal_kunjungan' => now()]);

        $component = Livewire::test(KunjunganPengendalianWizard::class, ['kunjungan' => $kunjungan]);

        $component
            ->set('fisikForm.ro_id', $ro->id)
            ->set('fisikForm.target_periode_ini', 100)
            ->set('fisikForm.realisasi_fisik_klaim', 80)
            ->set('fisikForm.realisasi_fisik_verifikasi', 80)
            ->call('addFisik');

        $component
            ->set('risikoForm.risiko_id', $risiko->id)
            ->set('risikoForm.risiko_residual_aktual', 'Rendah')
            ->call('addRisiko');

        $kunjungan->refresh();
        // fisik Sesuai (100) + risiko Sesuai/Lebih Baik (100) => rata-rata 100
        $this->assertEquals(100.0, $kunjungan->skorKeseluruhan());
        $this->assertEquals('Aktif Dikendalikan Sesuai Rencana', $kunjungan->rekomendasiOtomatis());
    }

    public function test_dokumentasi_bisa_diunggah_dan_disimpan_ke_storage(): void
    {
        Storage::fake('public');
        $this->actingAsAdminPengendalian();
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);
        $kunjungan = KunjunganPengendalian::create(['psn_id' => $psn->id, 'tanggal_kunjungan' => now()]);

        $file = UploadedFile::fake()->create('berita-acara.pdf', 100);

        Livewire::test(KunjunganPengendalianWizard::class, ['kunjungan' => $kunjungan])
            ->set('dokumentasiFile', $file)
            ->set('dokumentasiForm.kategori', 'Berita Acara')
            ->call('addDokumentasi')
            ->assertHasNoErrors();

        $doc = $kunjungan->dokumentasi()->first();
        $this->assertNotNull($doc);
        $this->assertEquals(1, $doc->nomor);
        Storage::disk('public')->assertExists($doc->nama_file_tautan);
    }

    public function test_viewer_internal_tidak_punya_akses_ke_instrumen_pengendalian(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');
        $this->actingAs($user);

        $this->get('/admin/kunjungan-pengendalian')->assertForbidden();
        $this->get('/admin/kunjungan-pengendalian/create')->assertForbidden();
    }
}
