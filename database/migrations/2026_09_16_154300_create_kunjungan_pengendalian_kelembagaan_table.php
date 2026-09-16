<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian_kelembagaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->string('peran', 30)->comment('Pengusul / Penanggung Jawab / Pengelola / Kontraktor / Supervisi');
            $table->unsignedBigInteger('instansi_tercatat_id')->nullable();
            $table->string('instansi_aktual', 255)->nullable()->comment('Temuan lapangan, bisa berbeda dari instansi_tercatat_id');
            $table->string('sesuai', 20)->nullable()->comment('Ya / Tidak / Sebagian');
            $table->text('catatan')->nullable();
            $table->index('kunjungan_id', 'idx_kpk_kunjungan');
            $table->index('instansi_tercatat_id', 'idx_kpk_instansi');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_pengendalian')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('instansi_tercatat_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi kelembagaan & stakeholder mapping -- Bagian B formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian_kelembagaan` ADD CONSTRAINT `chk_kpk_peran` CHECK (`peran` IN (\'Pengusul\',\'Penanggung Jawab\',\'Pengelola\',\'Kontraktor\',\'Supervisi\'))');
            DB::statement('ALTER TABLE `kunjungan_pengendalian_kelembagaan` ADD CONSTRAINT `chk_kpk_sesuai` CHECK (`sesuai` IS NULL OR `sesuai` IN (\'Ya\',\'Tidak\',\'Sebagian\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian_kelembagaan');
    }
};
