<?php

namespace App\Console\Commands;

use App\Support\Rkp2027CarryoverAnalyzer;
use Illuminate\Console\Command;

class AnalisisRkp2027Carryover extends Command
{
    protected $signature = 'psn:analisis-rkp2027
        {file? : Path file .docx Daftar PSN dalam RKP 2027 (default: berkas bawaan database/seeders/data)}
        {--terapkan : Terapkan hasil (isi kategori_usulan=Carryover untuk PSN yang cocok dan belum berkategori)}';

    protected $description = 'Sandingkan Daftar PSN RKP 2027 dengan data PSN dashboard untuk identifikasi proyek carryover';

    public function handle(Rkp2027CarryoverAnalyzer $analyzer): int
    {
        $path = $this->argument('file')
            ?? database_path('seeders/data/Daftar_PSN_RKP_2027.docx');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $this->info("Menganalisis {$path}...");

        $hasil = $analyzer->analisis($path);

        $this->table(
            ['Kategori', 'Jumlah'],
            [
                ['Carryover (cocok dengan data existing, skor >= '.Rkp2027CarryoverAnalyzer::SKOR_AMBANG_COCOK.')', $hasil['carryover']->count()],
                ['Perlu ditinjau manual (kemungkinan usulan baru/redaksional)', $hasil['perlu_ditinjau']->count()],
                ['Tidak ditemukan lagi di RKP 2027 (kandidat keluar dari daftar)', $hasil['tidak_ditemukan_lagi']->count()],
            ]
        );

        if ($this->option('terapkan')) {
            $jumlahDiperbarui = $analyzer->terapkanKategoriCarryover($hasil['carryover']);
            $this->info("kategori_usulan='Carryover' diterapkan pada {$jumlahDiperbarui} PSN (yang sebelumnya masih kosong).");
        } else {
            $this->comment('Jalankan dengan --terapkan untuk mengisi kolom kategori_usulan berdasarkan hasil ini.');
        }

        return self::SUCCESS;
    }
}
