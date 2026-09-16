<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_kriteria_perencanaan', function (Blueprint $table) {
            $table->id();
            $table->string('kelompok', 20)->comment('Utama / Pendukung / Kesiapan');
            $table->string('kode_kriteria', 5)->comment('U1-U3, P1-P6, K1-K5');
            $table->string('kode_sub', 5)->nullable()->comment('Urutan sub-butir dalam satu kriteria');
            $table->string('judul_kriteria', 255);
            $table->string('sub_butir', 500);
            $table->text('rubrik_penilaian')->nullable()->comment('Rubrik skor 0-3 (PMO) atau pertanyaan verifikasi Ya/Tidak');
            $table->string('tipe_penilaian', 15)->comment('YaTidak (Kriteria Utama) atau Skor0-3 (Pendukung/Kesiapan)');
            $table->boolean('kondisional')->default(false)->comment('TRUE untuk P4/P5/P6 (jenis pengusul) dan K3/K4 (jenis proyek)');
            $table->string('syarat_kondisional', 255)->nullable()->comment('mis. "Usulan K/L", "Infrastruktur Ekonomi"');
            $table->integer('urutan')->default(0);
            $table->index('kode_kriteria', 'idx_kriteria_kode');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master 14 kriteria + sub-butir Permen PPN 4/2025 Pasal 5');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `ref_kriteria_perencanaan` ADD CONSTRAINT `chk_kriteria_kelompok` CHECK (`kelompok` IN (\'Utama\',\'Pendukung\',\'Kesiapan\'))');
            DB::statement('ALTER TABLE `ref_kriteria_perencanaan` ADD CONSTRAINT `chk_kriteria_tipe` CHECK (`tipe_penilaian` IN (\'YaTidak\',\'Skor0-3\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_kriteria_perencanaan');
    }
};
