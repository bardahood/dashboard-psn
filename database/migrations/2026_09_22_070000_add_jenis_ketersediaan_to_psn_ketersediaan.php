<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Update Matrik Sandingan (17 Sept 2026) memecah "Ketersediaan Data" yang
 * semula 1 kolom menjadi 2 dimensi terpisah: "Data Gambaran Umum Proyek" dan
 * "Data Project Profile Lengkap untuk Kebutuhan Evaluasi". psn_ketersediaan
 * sebelumnya hanya menampung 1 baris per psn per periode (unique psn_id +
 * periode_pemutakhiran) -- diperluas jadi 1 baris per psn per periode PER
 * jenis, agar kedua dimensi tersimpan terpisah tanpa tabel baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psn_ketersediaan', function (Blueprint $table) {
            $table->dropUnique('uq_psn_periode');
            $table->string('jenis_ketersediaan', 30)->nullable()->after('status_ketersediaan_id')
                ->comment('Gambaran Umum atau Project Profile Lengkap, Matrik Sandingan 17 Sept 2026');
            $table->unique(['psn_id', 'periode_pemutakhiran', 'jenis_ketersediaan'], 'uq_psn_periode_jenis');
        });

        DB::statement("ALTER TABLE `psn_ketersediaan` ADD CONSTRAINT `chk_pk_jenis` CHECK (`jenis_ketersediaan` IS NULL OR `jenis_ketersediaan` IN ('Gambaran Umum','Project Profile Lengkap'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `psn_ketersediaan` DROP CONSTRAINT `chk_pk_jenis`');

        Schema::table('psn_ketersediaan', function (Blueprint $table) {
            $table->dropUnique('uq_psn_periode_jenis');
            $table->dropColumn('jenis_ketersediaan');
            $table->unique(['psn_id', 'periode_pemutakhiran'], 'uq_psn_periode');
        });
    }
};
