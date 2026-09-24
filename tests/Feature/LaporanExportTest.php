<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanExportTest extends TestCase
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

    public function test_super_admin_bisa_mengunduh_laporan_ringkasan_pdf(): void
    {
        $this->actingAsSuperAdmin();
        Psn::create(['nama_psn' => 'Contoh PSN']);

        $response = $this->get('/admin/laporan/ringkasan-pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_super_admin_bisa_mengunduh_matriks_sandingan_excel(): void
    {
        $this->actingAsSuperAdmin();
        Psn::create(['nama_psn' => 'Contoh PSN']);

        $response = $this->get('/admin/laporan/matriks-excel');

        $response->assertOk();
    }

    public function test_viewer_internal_tidak_punya_akses_laporan(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');
        $this->actingAs($user);

        $this->get('/admin/laporan')->assertForbidden();
    }
}
