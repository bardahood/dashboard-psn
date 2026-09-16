<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefDampakTrisulaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('ref_dampak_trisula')->insert([
            ['nama_dampak' => 'Pertumbuhan Ekonomi', 'contoh_indikator' => 'Tambahan output/PDRB, produktivitas, investasi, pekerjaan, penghematan waktu/biaya logistik', 'created_at' => $now, 'updated_at' => $now],
            ['nama_dampak' => 'Penurunan Kemiskinan', 'contoh_indikator' => 'Rumah tangga miskin penerima manfaat, tambahan pendapatan, pekerjaan kelompok rentan, penurunan biaya layanan', 'created_at' => $now, 'updated_at' => $now],
            ['nama_dampak' => 'Peningkatan Kualitas SDM', 'contoh_indikator' => 'Akses pendidikan/kesehatan, akses layanan dasar, waktu tempuh, keterampilan, produktivitas, hasil layanan', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
