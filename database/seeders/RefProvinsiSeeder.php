<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefProvinsiSeeder extends Seeder
{
    /**
     * 38 provinsi resmi + "Nasional" untuk PSN berskala nasional/lintas provinsi
     * (lihat komentar kolom psn.provinsi_id pada skema).
     */
    public function run(): void
    {
        $provinsi = [
            'Nasional',
            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau',
            'Jambi', 'Sumatera Selatan', 'Bangka Belitung', 'Bengkulu', 'Lampung',
            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Banten',
            'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
            'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
            'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Gorontalo', 'Sulawesi Barat',
            'Maluku', 'Maluku Utara',
            'Papua', 'Papua Barat', 'Papua Barat Daya', 'Papua Tengah', 'Papua Pegunungan', 'Papua Selatan',
        ];

        $now = now();
        DB::table('ref_provinsi')->insert(array_map(fn ($nama) => [
            'nama_provinsi' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ], $provinsi));
    }
}
