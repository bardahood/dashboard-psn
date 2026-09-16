<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerima_manfaat_target_tahunan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerima_manfaat_id');
            $table->smallInteger('tahun');
            $table->string('target_akhir', 100)->nullable();
            $table->decimal('target', 18, 4)->nullable();
            $table->decimal('realisasi', 18, 4)->nullable();
            $table->decimal('persen_realisasi', 6, 2)->nullable();
            $table->decimal('persen_terhadap_target_akhir', 6, 2)->nullable();
            $table->string('status_capaian', 30)->nullable();
            $table->unique(['penerima_manfaat_id', 'tahun'], 'uq_penerima_tahun');
            $table->foreign('penerima_manfaat_id')->references('id')->on('penerima_manfaat_psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Target & realisasi tahunan penerima manfaat');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('penerima_manfaat_target_tahunan');
    }
};
