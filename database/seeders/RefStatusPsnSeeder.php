<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefStatusPsnSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('ref_status_psn')->insert([
            ['id' => 1, 'nama_status' => 'Proyek Dalam Tahap Penyiapan', 'urutan' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama_status' => 'Proyek Dalam Tahap Transaksi', 'urutan' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nama_status' => 'Proyek Dalam Tahap Konstruksi', 'urutan' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nama_status' => 'Proyek Beroperasi Sebagian', 'urutan' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'nama_status' => 'Kumulatif Proyek Selesai', 'urutan' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'nama_status' => 'Proyek Keluar dari PSN', 'urutan' => 6, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
