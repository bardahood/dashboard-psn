<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_indeks_bukti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->string('id_bukti', 30)->nullable()->comment('mis. KL-K4-001');
            $table->string('kriteria_terkait', 100)->nullable();
            $table->string('nama_dokumen', 255)->nullable();
            $table->string('pemilik_data', 150)->nullable();
            $table->string('lokasi_bukti', 255)->nullable()->comment('halaman/foto/koordinat');
            $table->text('simpulan_singkat')->nullable();
            $table->string('status_verifikasi', 30)->nullable()->comment('Belum diterima/Diterima/Diverifikasi/Perlu perbaikan/Kedaluwarsa');
            $table->text('tindak_lanjut')->nullable();
            $table->index('kunjungan_id', 'idx_kib_kunjungan');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_perencanaan')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Indeks bukti lapangan -- format baku Panduan Pengisian Project Profile');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_indeks_bukti');
    }
};
