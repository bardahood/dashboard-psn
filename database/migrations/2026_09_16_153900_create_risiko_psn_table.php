<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risiko_psn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('peristiwa_risiko', 500);
            $table->string('kategori_risiko', 50)->nullable()->comment('mis. Regulasi, Teknis, Finansial, Lingkungan');
            $table->string('level_risiko_awal', 20)->nullable()->comment('Rendah / Sedang / Tinggi / Sangat Tinggi');
            $table->text('perlakuan_rencana')->nullable();
            $table->string('risiko_residual_harapan', 20)->nullable()->comment('Target level risiko residual ex-ante');
            $table->index('psn_id', 'idx_risiko_psn');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Register risiko per PSN -- objek yang diverifikasi pada Bagian E Instrumen Pengendalian');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `risiko_psn` ADD CONSTRAINT `chk_risiko_level` CHECK (`level_risiko_awal` IS NULL OR `level_risiko_awal` IN (\'Rendah\',\'Sedang\',\'Tinggi\',\'Sangat Tinggi\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('risiko_psn');
    }
};
