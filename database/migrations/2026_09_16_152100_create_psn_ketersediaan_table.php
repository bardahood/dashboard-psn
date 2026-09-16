<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psn_ketersediaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->unsignedBigInteger('status_ketersediaan_id');
            $table->text('keterangan')->nullable()->comment('mis. "Data Awal PSI, Data e-Monev PEKS3"');
            $table->date('periode_pemutakhiran')->comment('Menandai periode Matrik Sandingan ini berlaku');
            $table->index('psn_id', 'idx_ket_psn');
            $table->index('status_ketersediaan_id', 'idx_ket_status');
            $table->unique(['psn_id', 'periode_pemutakhiran'], 'uq_psn_periode');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('status_ketersediaan_id')->references('id')->on('ref_status_ketersediaan')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Riwayat ketersediaan data per PSN per periode');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('psn_ketersediaan');
    }
};
