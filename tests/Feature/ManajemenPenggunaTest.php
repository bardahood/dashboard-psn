<?php

namespace Tests\Feature;

use App\Models\HakAkses;
use App\Models\RefPic;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManajemenPenggunaTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $this->seed(RoleSeeder::class);
        $pic = RefPic::create(['nama_pic' => 'Super Admin Test', 'email' => 'super@test.local']);
        $user = User::factory()->create(['pic_id' => $pic->id]);
        $user->assignRole('Super Admin');
        HakAkses::create(['pic_id' => $pic->id, 'level_akses' => 'Admin', 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_super_admin_bisa_membuat_pengguna_baru_lengkap_dengan_hak_akses_dan_role(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post('/admin/pengguna', [
            'nama' => 'Pengguna Baru',
            'email' => 'baru@bappenas.go.id',
            'password' => 'password123',
            'instansi_id' => '',
            'level_akses' => 'Editor',
            'role' => 'Admin Pengendalian',
            'is_active' => '1',
        ]);

        $user = User::where('email', 'baru@bappenas.go.id')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Admin Pengendalian'));
        $this->assertNotNull($user->pic);

        $akses = HakAkses::where('pic_id', $user->pic->id)->first();
        $this->assertSame('Editor', $akses->level_akses);
        $this->assertTrue($akses->is_active);

        $response->assertRedirect(route('admin.pengguna.edit', $user));
    }

    public function test_super_admin_bisa_menonaktifkan_akses_pengguna_lain(): void
    {
        $this->actingAsSuperAdmin();

        $pic = RefPic::create(['nama_pic' => 'Target', 'email' => 'target@bappenas.go.id']);
        $target = User::factory()->create(['name' => 'Target', 'email' => 'target@bappenas.go.id', 'pic_id' => $pic->id]);
        $target->assignRole('Viewer Internal');
        HakAkses::create(['pic_id' => $pic->id, 'level_akses' => 'Viewer', 'is_active' => true]);

        $this->put("/admin/pengguna/{$target->id}", [
            'nama' => 'Target', 'email' => 'target@bappenas.go.id', 'password' => '',
            'instansi_id' => '', 'level_akses' => 'Viewer', 'role' => 'Viewer Internal',
        ])->assertRedirect();

        $this->assertFalse(HakAkses::where('pic_id', $pic->id)->first()->is_active);
    }

    public function test_super_admin_tidak_bisa_menonaktifkan_akun_sendiri(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $response = $this->put("/admin/pengguna/{$admin->id}", [
            'nama' => $admin->name, 'email' => $admin->email, 'password' => '',
            'instansi_id' => '', 'level_akses' => 'Admin', 'role' => 'Super Admin',
        ]);

        $response->assertSessionHasErrors('is_active');
        $this->assertTrue(HakAkses::where('pic_id', $admin->pic_id)->first()->is_active);
    }

    public function test_akun_dengan_hak_akses_nonaktif_diblokir_dan_dilogout_oleh_middleware(): void
    {
        $this->seed(RoleSeeder::class);
        $pic = RefPic::create(['nama_pic' => 'Nonaktif', 'email' => 'nonaktif@test.local']);
        $user = User::factory()->create(['pic_id' => $pic->id]);
        $user->assignRole('Viewer Internal');
        HakAkses::create(['pic_id' => $pic->id, 'level_akses' => 'Viewer', 'is_active' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertForbidden();
        $this->assertGuest();
    }

    public function test_viewer_internal_tidak_punya_akses_ke_manajemen_pengguna(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');
        $this->actingAs($user);

        $this->get('/admin/pengguna')->assertForbidden();
    }
}
