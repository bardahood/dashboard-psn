<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psn_sumber_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->unsignedBigInteger('sumber_data_id');
            $table->boolean('tersedia')->default(false)->comment('TRUE = tanda centang (v), FALSE = X pada Matrik');
            $table->unique(['psn_id', 'sumber_data_id'], 'uq_psn_sumber');
            $table->index('sumber_data_id', 'idx_sd_sumber');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sumber_data_id')->references('id')->on('ref_sumber_data')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Normalisasi 4 kolom sumber (wide format) pada Matrik Sandingan menjadi baris panjang (long format)');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('psn_sumber_data');
    }
};
