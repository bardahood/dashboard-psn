<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_monev', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->text('catatan');
            $table->string('kategori', 50)->nullable()->comment('mis. Progres, Kendala, Rekomendasi');
            $table->date('tanggal_catatan');
            $table->unsignedBigInteger('dicatat_oleh_id')->nullable();
            $table->index('psn_id', 'idx_monev_psn');
            $table->index('dicatat_oleh_id', 'idx_monev_pic');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('dicatat_oleh_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('TABEL RIIL PSI (Resume Rapat 11 Sept 2026) -- Tabel Catatan Monev');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_monev');
    }
};
