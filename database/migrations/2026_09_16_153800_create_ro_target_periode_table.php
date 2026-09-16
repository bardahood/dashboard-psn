<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ro_target_periode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ro_id');
            $table->smallInteger('tahun');
            $table->string('tipe_periode', 15)->comment('TAHUNAN / TRIWULANAN / BULANAN');
            $table->unsignedTinyInteger('triwulan')->nullable();
            $table->unsignedTinyInteger('bulan')->nullable();
            $table->decimal('target', 18, 4)->nullable();
            $table->decimal('pembiayaan_rencana_juta_rp', 18, 2)->nullable();
            $table->decimal('realisasi_fisik', 18, 4)->nullable()->comment('Klaim Pelaksana -- dibandingkan hasil verifikasi lapangan');
            $table->decimal('realisasi_anggaran_juta_rp', 18, 2)->nullable();
            $table->string('indikasi_sumber_pendanaan', 100)->nullable()->comment('Permen PPN 4/2025 Pasal 18: APBN/APBD/BUMN/BU-Swasta/lainnya');
            $table->string('status', 30)->nullable();
            $table->text('permasalahan')->nullable();
            $table->text('kebutuhan_dukungan')->nullable();
            $table->text('keterangan')->nullable();
            $table->index('ro_id', 'idx_rotp_ro');
            $table->foreign('ro_id')->references('id')->on('ro_proyek')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Target & realisasi (klaim) RO per periode');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `ro_target_periode` ADD CONSTRAINT `chk_rotp_tipe` CHECK (`tipe_periode` IN (\'TAHUNAN\',\'TRIWULANAN\',\'BULANAN\'))');
            DB::statement('ALTER TABLE `ro_target_periode` ADD CONSTRAINT `chk_rotp_tw` CHECK (`triwulan` IS NULL OR `triwulan` BETWEEN 1 AND 4)');
            DB::statement('ALTER TABLE `ro_target_periode` ADD CONSTRAINT `chk_rotp_bulan` CHECK (`bulan` IS NULL OR `bulan` BETWEEN 1 AND 12)');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('ro_target_periode');
    }
};
