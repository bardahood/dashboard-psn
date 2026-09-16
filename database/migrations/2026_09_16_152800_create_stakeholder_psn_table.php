<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stakeholder_psn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('nama_pemangku_kepentingan', 255);
            $table->string('kategori_aktor', 15)->comment('State atau Non-State');
            $table->unsignedTinyInteger('level_kelembagaan')->nullable()->comment('1=Kebijakan/Regulasi, 2=Fasilitator Wilayah, 3=Operator/Investor/Off-taker, 4=Partisipan/Penerima Manfaat/Riset');
            $table->text('peran_deskripsi')->nullable();
            $table->index('psn_id', 'idx_stp_psn');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Stakeholder Mapping & Kerangka Kelembagaan 4-level -- Gambaran Umum');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `stakeholder_psn` ADD CONSTRAINT `chk_stp_kategori` CHECK (`kategori_aktor` IN (\'State\',\'Non-State\'))');
            DB::statement('ALTER TABLE `stakeholder_psn` ADD CONSTRAINT `chk_stp_level` CHECK (`level_kelembagaan` IS NULL OR `level_kelembagaan` BETWEEN 1 AND 4)');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholder_psn');
    }
};
