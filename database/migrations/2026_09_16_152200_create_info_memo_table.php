<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_memo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('judul_memo', 255);
            $table->text('isi_memo')->nullable();
            $table->date('tanggal_memo');
            $table->unsignedBigInteger('dibuat_oleh_id')->nullable();
            $table->index('psn_id', 'idx_memo_psn');
            $table->index('dibuat_oleh_id', 'idx_memo_pic');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('dibuat_oleh_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('TABEL RIIL PSI (Resume Rapat 11 Sept 2026) -- Tabel Info Memo');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('info_memo');
    }
};
