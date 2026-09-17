<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_satu_akun_demo_per_role_dengan_password_default(): void
    {
        $this->seed(DatabaseSeeder::class);

        $peta = [
            'admin@bappenas.go.id' => 'Super Admin',
            'admin.pengendalian@bappenas.go.id' => 'Admin Pengendalian',
            'admin.perencanaan@bappenas.go.id' => 'Admin Perencanaan',
            'verifikator@bappenas.go.id' => 'Verifikator Lapangan',
            'kl.pelaksana@bappenas.go.id' => 'K/L Pelaksana',
            'viewer@bappenas.go.id' => 'Viewer Internal',
        ];

        foreach ($peta as $email => $role) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "User {$email} harus ada");
            $this->assertTrue($user->hasRole($role), "User {$email} harus punya role {$role}");
        }

        $this->assertSame(6, User::count());

        // Password default semua akun demo harus "password" agar bisa langsung login.
        $this->assertTrue(auth()->attempt(['email' => 'admin@bappenas.go.id', 'password' => 'password']));

        $klPelaksana = User::where('email', 'kl.pelaksana@bappenas.go.id')->first();
        $this->assertSame('Menteri Pekerjaan Umum', $klPelaksana->pic->instansi->nama_instansi);
    }
}
