<?php

namespace Database\Seeders;

use App\Support\MatriksSandinganImporter;
use Illuminate\Database\Seeder;

/**
 * Mengisi tabel psn (dan tabel normalisasinya) dari file resmi "Matrik
 * Sandingan Data PSN 2026" yang dibundel bersama repo (database/seeders/data).
 */
class MatriksSandinganPsnSeeder extends Seeder
{
    public function run(MatriksSandinganImporter $importer): void
    {
        $path = database_path('seeders/data/Matrik_Sandingan_Data_PSN_2026.xlsx');

        if (! is_file($path)) {
            $this->command?->warn("Berkas Matrik Sandingan tidak ditemukan, dilewati: {$path}");

            return;
        }

        $hasil = $importer->import($path, now()->toDateString());

        $this->command?->info("Matrik Sandingan: {$hasil['psn']} PSN diimpor.");
    }
}
