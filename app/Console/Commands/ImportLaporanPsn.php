<?php

namespace App\Console\Commands;

use App\Support\LaporanPsnImporter;
use Illuminate\Console\Command;

class ImportLaporanPsn extends Command
{
    protected $signature = 'psn:import-laporan-psn
        {file? : Path file .xlsx laporan_PSN (katalog RO/Output Krisna, default: berkas bawaan database/seeders/data)}
        {--matrix= : Path file .xlsx matrix_pembangunan_rkp2026 untuk pengayaan jalur PN/PP/KP/ProP (default: berkas bawaan, lewati dengan --matrix=0)}';

    protected $description = 'Impor katalog RO/Output Krisna (.xlsx) ke ref_ro_krisna, ditautkan ke PSN lewat pencocokan nama';

    public function handle(LaporanPsnImporter $importer): int
    {
        $path = $this->argument('file')
            ?? database_path('seeders/data/Laporan_PSN.xlsx');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $this->info("Mengimpor {$path}...");

        $hasil = $importer->importKatalogRo($path);

        $this->table(
            ['Baris diimpor', 'Tertaut ke PSN', 'Tidak tertaut'],
            [[$hasil['baris_diimpor'], $hasil['baris_tertaut_psn'], $hasil['baris_tidak_tertaut']]]
        );

        $matrixPath = $this->option('matrix') ?? database_path('seeders/data/Matrix_Pembangunan_RKP2026.xlsx');

        if ($matrixPath !== '0' && is_file($matrixPath)) {
            $this->info("Memperkaya dari {$matrixPath}...");
            $hasilMatrix = $importer->pengayaanMatrixRkp($matrixPath);
            $this->table(['Baris diperkaya jalur PN/PP/KP/ProP'], [[$hasilMatrix['baris_diperkaya']]]);
        }

        $this->info('Impor selesai.');

        return self::SUCCESS;
    }
}
