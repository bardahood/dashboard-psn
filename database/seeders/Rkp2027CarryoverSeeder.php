<?php

namespace Database\Seeders;

use App\Support\Rkp2027CarryoverAnalyzer;
use Illuminate\Database\Seeder;

/**
 * Setelah data PSN diimpor dari Matrik Sandingan (RKP Pemutakhiran 2026),
 * sandingkan dengan lampiran "Daftar PSN dalam RKP 2027" dan tuliskan
 * hasilnya sebagai kolom "RKP 2027" pada Matriks Sandingan (psn_sumber_data)
 * serta psn.kategori_usulan -- supaya langsung tampil setelah migrate:fresh
 * --seed, tanpa perlu menjalankan `psn:analisis-rkp2027 --terapkan` manual.
 */
class Rkp2027CarryoverSeeder extends Seeder
{
    public function run(Rkp2027CarryoverAnalyzer $analyzer): void
    {
        $path = database_path('seeders/data/Daftar_PSN_RKP_2027.docx');

        if (! is_file($path)) {
            $this->command?->warn("Berkas Daftar PSN RKP 2027 tidak ditemukan, dilewati: {$path}");

            return;
        }

        $hasil = $analyzer->analisis($path);
        $jumlahKategori = $analyzer->terapkanKategoriCarryover($hasil['carryover']);
        $jumlahMatriks = $analyzer->terapkanKeMatriksSandingan($hasil);

        $this->command?->info("RKP 2027: {$jumlahMatriks} PSN diperbarui pada Matriks Sandingan ({$jumlahKategori} di antaranya juga diberi kategori_usulan=Carryover).");
    }
}
