<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dasar_hukum_psn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('nama_regulasi', 255);
            $table->string('nomor_regulasi', 100)->nullable();
            $table->smallInteger('tahun')->nullable();
            $table->text('keterangan')->nullable();
            $table->index('psn_id', 'idx_dhp_psn');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Dasar hukum/regulasi BERLAKU yang menjadi landasan PSN');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('dasar_hukum_psn');
    }
};
