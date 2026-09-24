<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pembaruan lampiran "Master Data PSN Kode" (24 Sept 2026) menambahkan 2
 * kolom baru pada sheet Kode_PSI/PSN: Peks (pengelompokan unit PEKS
 * penanggung jawab internal, mis. "PEKS 4", kadang gabungan mis. "PEKS 2
 * dan PEKS 4") dan Unit_Kerja (nama Direktorat spesifik, mis. "Direktorat
 * Kesehatan dan Gizi Masyarakat") -- belum ada representasinya di skema.
 * Disimpan bebas teks (bukan FK ke tabel referensi baru) karena Peks hanya
 * ~5 varian termasuk gabungan (tidak benar-benar enum tunggal) dan
 * Unit_Kerja adalah sub-unit internal granular yang tidak cocok
 * disamakan dengan ref_instansi (itu instansi K/L eksternal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            $table->string('peks', 100)->nullable()->after('kode_rkp')
                ->comment('Unit PEKS internal penanggung jawab (Master Data PSN Kode), mis. "PEKS 4"');
            $table->string('unit_kerja', 255)->nullable()->after('peks')
                ->comment('Direktorat/unit kerja spesifik (Master Data PSN Kode)');
        });
    }

    public function down(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            $table->dropColumn(['peks', 'unit_kerja']);
        });
    }
};
