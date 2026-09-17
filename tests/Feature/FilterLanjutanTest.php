<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\User;
use Database\Seeders\RefSumberDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FilterLanjutanTest extends TestCase
{
    use RefreshDatabase;

    public function test_matriks_sandingan_bisa_difilter_per_klaster_provinsi_dan_hanya_gap(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RefSumberDataSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        $klasterAir = RefKlaster::create(['nama_klaster' => 'Swasembada Air']);
        $klasterJalan = RefKlaster::create(['nama_klaster' => 'Konektivitas Jalan']);
        $jabar = RefProvinsi::create(['nama_provinsi' => 'Jawa Barat']);
        $ntt = RefProvinsi::create(['nama_provinsi' => 'Nusa Tenggara Timur']);

        $lengkap = Psn::create(['nama_psn' => 'Bendungan Lengkap', 'klaster_id' => $klasterAir->id, 'provinsi_id' => $jabar->id]);
        $gap = Psn::create(['nama_psn' => 'Jalan Ada Gap', 'klaster_id' => $klasterJalan->id, 'provinsi_id' => $ntt->id]);

        foreach ([1, 2, 3, 4] as $sumberId) {
            DB::table('psn_sumber_data')->insert(['psn_id' => $lengkap->id, 'sumber_data_id' => $sumberId, 'tersedia' => true]);
            DB::table('psn_sumber_data')->insert(['psn_id' => $gap->id, 'sumber_data_id' => $sumberId, 'tersedia' => $sumberId !== 3]);
        }

        $this->get('/admin/matriks-sandingan?klaster=Swasembada Air')
            ->assertOk()->assertSee('Bendungan Lengkap')->assertDontSee('Jalan Ada Gap');

        $this->get('/admin/matriks-sandingan?provinsi=Nusa Tenggara Timur')
            ->assertOk()->assertSee('Jalan Ada Gap')->assertDontSee('Bendungan Lengkap');

        $this->get('/admin/matriks-sandingan?hanya_gap=1')
            ->assertOk()->assertSee('Jalan Ada Gap')->assertDontSee('Bendungan Lengkap');
    }

    public function test_daftar_psn_publik_bisa_difilter_per_kl_penanggung_jawab(): void
    {
        $bappenas = RefInstansi::create(['nama_instansi' => 'Menteri PPN/Kepala Bappenas']);
        $pu = RefInstansi::create(['nama_instansi' => 'Menteri Pekerjaan Umum']);

        $psnA = Psn::create(['nama_psn' => 'Proyek Bappenas']);
        $psnB = Psn::create(['nama_psn' => 'Proyek PU']);

        DB::table('psn_penanggung_jawab')->insert(['psn_id' => $psnA->id, 'instansi_id' => $bappenas->id]);
        DB::table('psn_penanggung_jawab')->insert(['psn_id' => $psnB->id, 'instansi_id' => $pu->id]);

        $response = $this->get('/psn?instansi_id='.$bappenas->id);

        $response->assertOk();
        $response->assertSee('Proyek Bappenas');
        $response->assertDontSee('Proyek PU');
    }
}
