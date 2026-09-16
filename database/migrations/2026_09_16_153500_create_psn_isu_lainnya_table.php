<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psn_isu_lainnya', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->integer('nomor');
            $table->text('deskripsi_isu');
            $table->text('kebutuhan_dukungan')->nullable();
            $table->index('psn_id', 'idx_pil_psn');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Deskripsi Isu Lainnya (di luar Peristiwa Risiko) dan Kebutuhan Dukungan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('psn_isu_lainnya');
    }
};
