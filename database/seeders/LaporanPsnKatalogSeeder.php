<?php

namespace Database\Seeders;

use App\Support\LaporanPsnImporter;
use Illuminate\Database\Seeder;

/**
 * Impor katalog RO/Output resmi Krisna (laporan_PSN.xlsx) ke ref_ro_krisna,
 * ditautkan ke PSN lewat pencocokan nama, lalu diperkaya jalur PN/PP/KP/ProP
 * dari matrix_pembangunan_rkp2026.xlsx (Risalah Rapat 21 Sept 2026: "RO
 * pilihannya dropdown, pilihan ditarik dari krisna") -- supaya dropdown
 * Krisna pada RoProyekManager langsung terisi setelah migrate:fresh --seed.
 */
class LaporanPsnKatalogSeeder extends Seeder
{
    public function run(LaporanPsnImporter $importer): void
    {
        $path = database_path('seeders/data/Laporan_PSN.xlsx');

        if (! is_file($path)) {
            $this->command?->warn("Berkas Laporan PSN (katalog Krisna) tidak ditemukan, dilewati: {$path}");

            return;
        }

        $hasil = $importer->importKatalogRo($path);
        $this->command?->info("Katalog RO Krisna: {$hasil['baris_diimpor']} baris diimpor, {$hasil['baris_tertaut_psn']} tertaut ke PSN.");

        $matrixPath = database_path('seeders/data/Matrix_Pembangunan_RKP2026.xlsx');
        if (is_file($matrixPath)) {
            $hasilMatrix = $importer->pengayaanMatrixRkp($matrixPath);
            $this->command?->info("Katalog RO Krisna: {$hasilMatrix['baris_diperkaya']} baris diperkaya jalur PN/PP/KP/ProP.");
        }

        $sandinganPath = database_path('seeders/data/Hasil_Sandingan_Laporan_PSN_dan_Matrix_Pemb_rkp2026.xlsx');
        if (is_file($sandinganPath)) {
            $hasilSandingan = $importer->importHasilSandingan($sandinganPath);
            $this->command?->info("Hasil Sandingan: {$hasilSandingan['baris_diimpor']} baris RO tambahan diimpor untuk {$hasilSandingan['psn_tertaut']} PSN, {$hasilSandingan['baris_diperkaya_kode']} baris lama diperkaya kode RKP ({$hasilSandingan['baris_dilewati_duplikat']} duplikat dilewati).");
        }
    }
}
