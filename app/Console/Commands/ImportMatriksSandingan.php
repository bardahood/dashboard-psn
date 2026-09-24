<?php

namespace App\Console\Commands;

use App\Support\MatriksSandinganImporter;
use Illuminate\Console\Command;

class ImportMatriksSandingan extends Command
{
    protected $signature = 'psn:import-matriks
        {file? : Path file .xlsx Matrik Sandingan (default: berkas bawaan database/seeders/data)}
        {--periode= : Tanggal periode pemutakhiran, format YYYY-MM-DD (default: hari ini)}
        {--kode= : Path file .xlsx Master Data PSN Kode untuk mengisi kode_rkp (default: berkas bawaan database/seeders/data, lewati dengan --kode=0)}';

    protected $description = 'Impor data PSN dari file Matrik Sandingan (.xlsx) ke tabel psn beserta normalisasinya';

    public function handle(MatriksSandinganImporter $importer): int
    {
        $path = $this->argument('file')
            ?? database_path('seeders/data/Matrik_Sandingan_Data_PSN_2026.xlsx');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $periode = $this->option('periode') ?? now()->toDateString();

        $this->info("Mengimpor {$path} (periode pemutakhiran: {$periode})...");

        $hasil = $importer->import($path, $periode);

        $this->table(
            ['PSN dibuat', 'Baris K/L Penanggung Jawab', 'Baris Sumber Data', 'Baris Ketersediaan', 'Baris dilewati (nama kosong)'],
            [[$hasil['psn'], $hasil['penanggung_jawab'], $hasil['sumber_data'], $hasil['ketersediaan'], $hasil['dilewati']]]
        );

        $kodePath = $this->option('kode') ?? database_path('seeders/data/Master_Data_PSN_Kode.xlsx');

        if ($kodePath !== '0' && is_file($kodePath)) {
            $this->info("Mengisi kode_rkp dari {$kodePath}...");
            $hasilKode = $importer->importKodeRkp($kodePath);
            $this->table(['Kode Cocok', 'Kode Tidak Cocok'], [[$hasilKode['cocok'], $hasilKode['tidak_cocok']]]);
        }

        $this->info('Impor selesai.');

        return self::SUCCESS;
    }
}
