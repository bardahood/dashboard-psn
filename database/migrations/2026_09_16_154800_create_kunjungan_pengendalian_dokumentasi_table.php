<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian_dokumentasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->integer('nomor');
            $table->string('deskripsi', 255)->nullable();
            $table->string('nama_file_tautan', 500)->nullable()->comment('Path/URL via Laravel Storage');
            $table->string('kategori', 50)->nullable();
            $table->index('kunjungan_id', 'idx_kpd_kunjungan');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_pengendalian')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Dokumentasi pendukung kunjungan Pengendalian -- Bagian H formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian_dokumentasi` ADD CONSTRAINT `chk_kpd_kategori` CHECK (`kategori` IS NULL OR `kategori` IN (\'Foto Lapangan\',\'Berita Acara\',\'Dokumen Realisasi Anggaran\',\'Dokumen Teknis\',\'Dokumen Regulasi\',\'Lainnya\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian_dokumentasi');
    }
};
