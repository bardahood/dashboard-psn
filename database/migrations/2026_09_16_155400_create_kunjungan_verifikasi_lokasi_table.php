<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_verifikasi_lokasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->string('aspek', 255);
            $table->text('klaim_dokumen')->nullable();
            $table->text('temuan_lapangan')->nullable();
            $table->string('sesuai', 20)->nullable()->comment('Sesuai / Sebagian / Tidak Sesuai');
            $table->text('catatan')->nullable();
            $table->index('kunjungan_id', 'idx_kvl_kunjungan');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_perencanaan')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi kesesuaian peta & tata ruang');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_verifikasi_lokasi` ADD CONSTRAINT `chk_kvl_sesuai` CHECK (`sesuai` IS NULL OR `sesuai` IN (\'Sesuai\',\'Sebagian\',\'Tidak Sesuai\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_verifikasi_lokasi');
    }
};
