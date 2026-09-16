<?php

namespace Database\Seeders;

use App\Models\RefPic;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
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
        ]);

        $pic = RefPic::create([
            'nama_pic' => 'Super Admin',
            'email' => 'admin@bappenas.go.id',
        ]);

        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@bappenas.go.id',
            'pic_id' => $pic->id,
        ]);
        $admin->assignRole('Super Admin');
    }
}
