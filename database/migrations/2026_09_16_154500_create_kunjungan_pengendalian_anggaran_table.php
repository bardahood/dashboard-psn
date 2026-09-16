<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian_anggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('ro_id');
            $table->decimal('pembiayaan_rencana_juta_rp', 18, 2)->nullable();
            $table->decimal('realisasi_anggaran_klaim_juta_rp', 18, 2)->nullable();
            $table->decimal('realisasi_anggaran_verifikasi_juta_rp', 18, 2)->nullable();
            $table->decimal('persen_realisasi', 6, 2)->nullable();
            $table->string('bukti_dokumen_tersedia', 20)->nullable()->comment('Ya / Tidak / Sebagian');
            $table->string('kesesuaian', 20)->nullable();
            $table->text('catatan')->nullable();
            $table->index('kunjungan_id', 'idx_kpa_kunjungan');
            $table->index('ro_id', 'idx_kpa_ro');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_pengendalian')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ro_id')->references('id')->on('ro_proyek')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi realisasi anggaran per RO/Aktivitas -- Bagian D formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian_anggaran` ADD CONSTRAINT `chk_kpa_bukti` CHECK (`bukti_dokumen_tersedia` IS NULL OR `bukti_dokumen_tersedia` IN (\'Ya\',\'Tidak\',\'Sebagian\'))');
            DB::statement('ALTER TABLE `kunjungan_pengendalian_anggaran` ADD CONSTRAINT `chk_kpa_kesesuaian` CHECK (`kesesuaian` IS NULL OR `kesesuaian` IN (\'Sesuai\',\'Sebagian\',\'Tidak Sesuai\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian_anggaran');
    }
};
