<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefSumberDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('ref_sumber_data')->insert([
            ['id' => 1, 'nama_sumber' => 'RKP Pemutakhiran 2026 (Perpres 68)', 'deskripsi' => 'Daftar PSN resmi RKP Pemutakhiran 2026 -- 298 PSN', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama_sumber' => 'Data PEKS3', 'deskripsi' => 'Direktorat Pembiayaan, Ekonomi Kreatif, Sains dan Statistik -- 298 PSN', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nama_sumber' => 'Data PSI', 'deskripsi' => 'Direktorat Pembiayaan Strategis dan Inovatif -- 296 PSN (dimutakhirkan menjadi 298, Resume Rapat 11 Sept 2026)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nama_sumber' => 'Permenko', 'deskripsi' => 'Peraturan Menteri Koordinator -- 145 PSN beririsan RKP, 90 PSN di luar RKP', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'nama_sumber' => 'RKP 2027', 'deskripsi' => 'Suplemen Daftar PSN dalam RKP 2027 -- hasil pencocokan otomatis (fuzzy matching) terhadap data existing, lihat /admin/analisis-rkp2027', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
