<?php

namespace Database\Seeders;

use App\Support\LaporanPsnImporter;
use Illuminate\Database\Seeder;

/**
 * Isi RO/Proyek (ro_proyek) dari katalog Krisna untuk PSN yang profilnya
 * masih kosong (Risalah Rapat 21 Sept 2026: "Contoh PSN jalan tol wajib
 * terisi progress per-ruas jalan di bagian RO/kegiatan karena sudah ada
 * datanya") -- lihat LaporanPsnImporter::seedRoProyekDariKatalog() untuk
 * aturan lengkap (tidak pernah menimpa RO yang sudah diisi manual).
 */
class RoProyekDariKrisnaSeeder extends Seeder
{
    public function run(LaporanPsnImporter $importer): void
    {
        $hasil = $importer->seedRoProyekDariKatalog();

        $this->command?->info("RO dari Krisna: {$hasil['ro_dibuat']} baris RO dibuat untuk {$hasil['psn_diisi']} PSN yang profilnya masih kosong.");
    }
}
