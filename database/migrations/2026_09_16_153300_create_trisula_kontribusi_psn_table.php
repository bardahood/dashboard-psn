<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trisula_kontribusi_psn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('kategori_trisula', 30)->comment('Kemiskinan / Pertumbuhan Ekonomi / Sumber Daya Manusia');
            $table->string('nama_indikator', 500);
            $table->string('sumber_dana', 15)->nullable()->comment('APBN / Non-APBN -- khusus indikator finansial (Capex/Opex)');
            $table->string('baseline', 100)->nullable();
            $table->index('psn_id', 'idx_tkp_psn');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Kontribusi terhadap Trisula Pembangunan (master)');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `trisula_kontribusi_psn` ADD CONSTRAINT `chk_tkp_kategori` CHECK (`kategori_trisula` IN (\'Kemiskinan\',\'Pertumbuhan Ekonomi\',\'Sumber Daya Manusia\'))');
            DB::statement('ALTER TABLE `trisula_kontribusi_psn` ADD CONSTRAINT `chk_tkp_sumberdana` CHECK (`sumber_dana` IS NULL OR `sumber_dana` IN (\'APBN\',\'Non-APBN\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('trisula_kontribusi_psn');
    }
};
