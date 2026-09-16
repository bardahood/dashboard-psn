<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kebutuhan_regulasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('nama_regulasi', 255);
            $table->text('justifikasi_kebutuhan')->nullable();
            $table->smallInteger('target_tahun_penyelesaian')->nullable();
            $table->unsignedBigInteger('penanggung_jawab_id')->nullable();
            $table->index('psn_id', 'idx_regulasi_psn');
            $table->index('penanggung_jawab_id', 'idx_regulasi_pj');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('penanggung_jawab_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Kebutuhan regulasi per PSN -- objek yang diverifikasi pada Bagian F Instrumen Pengendalian');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('kebutuhan_regulasi');
    }
};
