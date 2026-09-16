<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psn_penanggung_jawab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->unsignedBigInteger('instansi_id');
            $table->unique(['psn_id', 'instansi_id'], 'uq_psn_instansi');
            $table->index('instansi_id', 'idx_pj_instansi');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('instansi_id')->references('id')->on('ref_instansi')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Normalisasi 1NF: memecah kolom K_L_Penanggungjawab yang berisi multi-nilai');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('psn_penanggung_jawab');
    }
};
