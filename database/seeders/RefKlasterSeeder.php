<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefKlasterSeeder extends Seeder
{
    public function run(): void
    {
        $klaster = [
            'Konektivitas dan Infrastruktur Logistik Jalan',
            'Industrialisasi',
            'Swasembada Air',
            'Swasembada Energi',
            'Direktif Presiden',
            'Kawasan Ekonomi Khusus',
            'Perencanaan Wilayah dan Pengembangan Kawasan',
            'Konektivitas dan Infrastruktur Logistik Non-Jalan',
            'Perumahan dan Permukiman',
            'Hilirisasi',
            'Swasembada Pangan',
            'Kawasan Swasembada Pangan, Energi, dan Air Nasional',
            'Pembangunan Manusia dan Kebudayaan',
            'Transformasi Tata Kelola',
        ];

        $now = now();
        DB::table('ref_klaster')->insert(array_map(fn ($nama) => [
            'nama_klaster' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ], $klaster));
    }
}
