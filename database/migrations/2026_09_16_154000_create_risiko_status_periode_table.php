<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risiko_status_periode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risiko_id');
            $table->smallInteger('tahun');
            $table->unsignedTinyInteger('triwulan');
            $table->decimal('progres_pelaksanaan_persen', 5, 2)->nullable();
            $table->string('risiko_residual_aktual', 20)->nullable()->comment('Rendah/Sedang/Tinggi/Sangat Tinggi');
            $table->string('status_perlakuan', 30)->nullable()->comment('Selesai / On Progress / Belum ada Tindak Lanjut');
            $table->text('bukti_dukung')->nullable();
            $table->text('catatan')->nullable();
            $table->unique(['risiko_id', 'tahun', 'triwulan'], 'uq_risiko_periode');
            $table->foreign('risiko_id')->references('id')->on('risiko_psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Pelaporan RUTIN triwulanan progres perlakuan risiko oleh Pelaksana');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `risiko_status_periode` ADD CONSTRAINT `chk_rsp_tw` CHECK (`triwulan` BETWEEN 1 AND 4)');
            DB::statement('ALTER TABLE `risiko_status_periode` ADD CONSTRAINT `chk_rsp_residual` CHECK (`risiko_residual_aktual` IS NULL OR `risiko_residual_aktual` IN (\'Rendah\',\'Sedang\',\'Tinggi\',\'Sangat Tinggi\'))');
            DB::statement('ALTER TABLE `risiko_status_periode` ADD CONSTRAINT `chk_rsp_status` CHECK (`status_perlakuan` IS NULL OR `status_perlakuan` IN (\'Selesai\',\'On Progress\',\'Belum ada Tindak Lanjut\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('risiko_status_periode');
    }
};
