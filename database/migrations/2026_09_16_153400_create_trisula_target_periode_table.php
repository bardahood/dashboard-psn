<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trisula_target_periode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kontribusi_id');
            $table->smallInteger('tahun');
            $table->string('tipe_periode', 15)->default('TAHUNAN')->comment('TAHUNAN (Perencanaan) atau TRIWULANAN (agregat, Pengendalian)');
            $table->unsignedTinyInteger('triwulan')->nullable();
            $table->string('target_akhir', 100)->nullable();
            $table->decimal('target', 18, 4)->nullable();
            $table->decimal('realisasi', 18, 4)->nullable();
            $table->decimal('persen_realisasi', 6, 2)->nullable();
            $table->decimal('persen_terhadap_target_akhir', 6, 2)->nullable();
            $table->string('status_capaian', 30)->nullable();
            $table->index('kontribusi_id', 'idx_ttp_kontribusi');
            $table->foreign('kontribusi_id')->references('id')->on('trisula_kontribusi_psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Target & realisasi Trisula per periode -- tahunan (Perencanaan) & triwulanan (Pengendalian)');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `trisula_target_periode` ADD CONSTRAINT `chk_ttp_tipe` CHECK (`tipe_periode` IN (\'TAHUNAN\',\'TRIWULANAN\'))');
            DB::statement('ALTER TABLE `trisula_target_periode` ADD CONSTRAINT `chk_ttp_tw` CHECK (`triwulan` IS NULL OR `triwulan` BETWEEN 1 AND 4)');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('trisula_target_periode');
    }
};
