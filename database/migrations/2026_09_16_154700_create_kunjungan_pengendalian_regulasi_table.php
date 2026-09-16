<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian_regulasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('regulasi_id');
            $table->string('status_klaim', 30)->nullable()->comment('Selesai / Proses / Direncanakan / Belum Ada');
            $table->string('status_temuan_lapangan', 30)->nullable();
            $table->string('bukti_dukung_ditemukan', 10)->nullable()->comment('Ya / Tidak');
            $table->string('kesesuaian', 40)->nullable()->comment('Konsisten / Ada Perbedaan - Perlu Klarifikasi');
            $table->text('catatan')->nullable();
            $table->index('kunjungan_id', 'idx_kpg_kunjungan');
            $table->index('regulasi_id', 'idx_kpg_regulasi');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_pengendalian')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('regulasi_id')->references('id')->on('kebutuhan_regulasi')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi Kebutuhan Regulasi -- Bagian F formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian_regulasi` ADD CONSTRAINT `chk_kpg_bukti` CHECK (`bukti_dukung_ditemukan` IS NULL OR `bukti_dukung_ditemukan` IN (\'Ya\',\'Tidak\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian_regulasi');
    }
};
