<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_verifikasi_trisula', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('dampak_id');
            $table->text('indikator_klaim_dokumen')->nullable();
            $table->text('temuan_lapangan_spotcheck')->nullable();
            $table->string('kondisi_awal_terverifikasi', 20)->nullable()->comment('Ya / Sebagian / Tidak');
            $table->string('atribusi_masuk_akal', 20)->nullable();
            $table->text('catatan')->nullable();
            $table->unique(['kunjungan_id', 'dampak_id'], 'uq_kunjungan_dampak');
            $table->index('dampak_id', 'idx_kvt_dampak');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_perencanaan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('dampak_id')->references('id')->on('ref_dampak_trisula')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Spot-check dampak Trisula Pembangunan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_verifikasi_trisula');
    }
};
