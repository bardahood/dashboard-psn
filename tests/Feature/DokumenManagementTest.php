<?php

namespace Tests\Feature;

use App\Models\KunjunganPengendalian;
use App\Models\KunjunganPengendalianDokumentasi;
use App\Models\Psn;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokumenManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pengendalian_bisa_melihat_dan_memfilter_dokumen_lintas_psn(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin Pengendalian');
        $this->actingAs($user);

        $psnA = Psn::create(['nama_psn' => 'Bendungan Alfa']);
        $psnB = Psn::create(['nama_psn' => 'Jalan Tol Beta']);

        $kunjunganA = KunjunganPengendalian::create(['psn_id' => $psnA->id, 'tanggal_kunjungan' => '2026-01-10']);
        $kunjunganB = KunjunganPengendalian::create(['psn_id' => $psnB->id, 'tanggal_kunjungan' => '2026-02-15']);

        KunjunganPengendalianDokumentasi::create([
            'kunjungan_id' => $kunjunganA->id, 'nomor' => 1,
            'deskripsi' => 'Foto progres fisik', 'kategori' => 'Foto Lapangan',
            'nama_file_tautan' => 'kunjungan-pengendalian/1/foto.jpg',
        ]);
        KunjunganPengendalianDokumentasi::create([
            'kunjungan_id' => $kunjunganB->id, 'nomor' => 1,
            'deskripsi' => 'Berita acara serah terima', 'kategori' => 'Berita Acara',
            'nama_file_tautan' => 'kunjungan-pengendalian/2/ba.pdf',
        ]);

        $response = $this->get('/admin/dokumen');
        $response->assertOk();
        $response->assertSee('Bendungan Alfa');
        $response->assertSee('Jalan Tol Beta');

        $response = $this->get('/admin/dokumen?psn=Alfa');
        $response->assertOk();
        $response->assertSee('Bendungan Alfa');
        $response->assertDontSee('Jalan Tol Beta');

        $response = $this->get('/admin/dokumen?kategori=Berita Acara');
        $response->assertOk();
        $response->assertSee('Jalan Tol Beta');
        $response->assertDontSee('Bendungan Alfa');
    }

    public function test_viewer_internal_tidak_punya_akses_dokumen(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');
        $this->actingAs($user);

        $this->get('/admin/dokumen')->assertForbidden();
    }
}
