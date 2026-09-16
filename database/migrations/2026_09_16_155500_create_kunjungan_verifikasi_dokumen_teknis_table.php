<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_verifikasi_dokumen_teknis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('dokumen_id');
            $table->string('tersedia', 10)->nullable()->comment('Ya / Sebagian / Tidak');
            $table->string('tanggal_versi_dokumen', 50)->nullable();
            $table->string('kesesuaian_kondisi_lapangan', 20)->nullable();
            $table->text('catatan')->nullable();
            $table->unique(['kunjungan_id', 'dokumen_id'], 'uq_kunjungan_dokumen');
            $table->index('dokumen_id', 'idx_kvdt_dokumen');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_perencanaan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('dokumen_id')->references('id')->on('ref_dokumen_teknis')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi ketersediaan & kemutakhiran dokumen teknis');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_verifikasi_dokumen_teknis` ADD CONSTRAINT `chk_kvdt_tersedia` CHECK (`tersedia` IS NULL OR `tersedia` IN (\'Ya\',\'Sebagian\',\'Tidak\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_verifikasi_dokumen_teknis');
    }
};
