<?php

namespace Database\Seeders;

use App\Models\RefInstansi;
use App\Models\RefPic;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Satu akun demo per role (Bagian 6 RBAC prompt) agar setiap role dapat
     * langsung dicoba/didemokan tanpa perlu membuat user manual dulu.
     * Password sama untuk semua ("password", default Laravel factory) --
     * WAJIB diganti sebelum dipakai di lingkungan produksi, lihat AKSES.md.
     */
    private const AKUN_DEMO = [
        ['nama' => 'Super Admin', 'email' => 'admin@bappenas.go.id', 'role' => 'Super Admin'],
        ['nama' => 'Admin Pengendalian', 'email' => 'admin.pengendalian@bappenas.go.id', 'role' => 'Admin Pengendalian'],
        ['nama' => 'Admin Perencanaan', 'email' => 'admin.perencanaan@bappenas.go.id', 'role' => 'Admin Perencanaan'],
        ['nama' => 'Verifikator Lapangan', 'email' => 'verifikator@bappenas.go.id', 'role' => 'Verifikator Lapangan'],
        ['nama' => 'PIC K/L Pelaksana', 'email' => 'kl.pelaksana@bappenas.go.id', 'role' => 'K/L Pelaksana', 'instansi' => 'Menteri Pekerjaan Umum'],
        ['nama' => 'Viewer Internal', 'email' => 'viewer@bappenas.go.id', 'role' => 'Viewer Internal'],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RefKlasterSeeder::class,
            RefProvinsiSeeder::class,
            RefSumberDataSeeder::class,
            RefStatusKetersediaanSeeder::class,
            RefStatusPsnSeeder::class,
            RefKriteriaPerencanaanSeeder::class,
            RefDokumenTeknisSeeder::class,
            RefDampakTrisulaSeeder::class,
            RoleSeeder::class,
            MatriksSandinganPsnSeeder::class,
        ]);

        foreach (self::AKUN_DEMO as $akun) {
            $instansiId = isset($akun['instansi'])
                ? RefInstansi::firstOrCreate(['nama_instansi' => $akun['instansi']])->id
                : null;

            $pic = RefPic::create([
                'nama_pic' => $akun['nama'],
                'email' => $akun['email'],
                'instansi_id' => $instansiId,
            ]);

            $user = User::factory()->create([
                'name' => $akun['nama'],
                'email' => $akun['email'],
                'pic_id' => $pic->id,
            ]);
            $user->assignRole($akun['role']);
        }
    }
}
