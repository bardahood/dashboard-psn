<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_verifikasi_kriteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('kriteria_id');
            $table->string('nilai_desk_review_pmo', 10)->nullable()->comment('Skor 0-3 PMO, atau kosong utk Kriteria Utama');
            $table->text('klaim_atau_temuan_dokumen')->nullable();
            $table->text('temuan_lapangan')->nullable();
            $table->string('nilai_hasil_verifikasi', 10)->nullable()->comment('Ya/Tidak (Utama) atau skor 0-3 (Pendukung/Kesiapan)');
            $table->text('catatan')->nullable();
            $table->unique(['kunjungan_id', 'kriteria_id'], 'uq_kunjungan_kriteria');
            $table->index('kriteria_id', 'idx_kvk_kriteria');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_perencanaan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kriteria_id')->references('id')->on('ref_kriteria_perencanaan')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi seluruh 14 kriteria (Utama/Pendukung/Kesiapan) -- data-driven');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_verifikasi_kriteria');
    }
};
