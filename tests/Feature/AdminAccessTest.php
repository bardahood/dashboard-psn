<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_diarahkan_ke_login_saat_mengakses_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_super_admin_bisa_mengakses_dashboard_dan_kelola_psn(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/admin/psn')->assertOk();
        $this->actingAs($user)->get('/admin/psn/create')->assertOk();
    }

    public function test_viewer_internal_tidak_bisa_membuat_psn_baru(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');

        $this->actingAs($user)->get('/admin/psn')->assertOk();
        $this->actingAs($user)->get('/admin/psn/create')->assertForbidden();
    }

    public function test_kl_pelaksana_hanya_bisa_mengubah_psn_miliknya_sendiri(): void
    {
        $instansiSaya = \App\Models\RefInstansi::create(['nama_instansi' => 'Kementerian Kelautan dan Perikanan']);
        $instansiLain = \App\Models\RefInstansi::create(['nama_instansi' => 'Kementerian PUPR']);

        $pic = \App\Models\RefPic::create(['nama_pic' => 'PIC KKP', 'instansi_id' => $instansiSaya->id]);
        $user = User::factory()->create(['pic_id' => $pic->id]);
        $user->assignRole('K/L Pelaksana');

        $psnSaya = Psn::create(['nama_psn' => 'PSN milik KKP', 'pengusul_instansi_id' => $instansiSaya->id]);
        $psnLain = Psn::create(['nama_psn' => 'PSN milik PUPR', 'pengusul_instansi_id' => $instansiLain->id]);

        $this->assertTrue($user->can('update', $psnSaya));
        $this->assertFalse($user->can('update', $psnLain));
    }
}
