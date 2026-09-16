<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psn_evaluasi_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->smallInteger('tahun_evaluasi');
            $table->boolean('masih_butuh_status_psn')->nullable();
            $table->text('justifikasi')->nullable();
            $table->unique(['psn_id', 'tahun_evaluasi'], 'uq_psn_evaluasi_tahun');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Evaluasi tahunan: Kebutuhan Status PSN Tahun Selanjutnya/Justifikasi Kebutuhan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('psn_evaluasi_status');
    }
};
