<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tabel', 50);
            $table->unsignedBigInteger('record_id');
            $table->string('aksi', 20)->comment('insert / update / delete');
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->json('nilai_lama')->nullable();
            $table->json('nilai_baru')->nullable();
            $table->index(['nama_tabel', 'record_id'], 'idx_audit_tabel_record');
            $table->index('pic_id', 'idx_audit_pic');
            $table->foreign('pic_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->comment('Jejak audit perubahan data lintas tabel');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `audit_log` ADD CONSTRAINT `chk_audit_aksi` CHECK (`aksi` IN (\'insert\',\'update\',\'delete\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
