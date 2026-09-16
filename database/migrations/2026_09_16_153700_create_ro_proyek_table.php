<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ro_proyek', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->unsignedBigInteger('ro_induk_id')->nullable()->comment('Self-reference: NULL jika RO induk, terisi jika ini Aktivitas turunan');
            $table->string('nama_ro', 255);
            $table->string('tipe', 20)->default('RO')->comment('RO atau Aktivitas');
            $table->boolean('is_ro_kunci')->default(false)->comment('Penanda RO Kunci/Critical Path');
            $table->string('satuan', 50)->nullable();
            $table->string('baseline', 100)->nullable();
            $table->string('target_akhir', 100)->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->unsignedBigInteger('instansi_pelaksana_id')->nullable();
            $table->index('psn_id', 'idx_ro_psn');
            $table->index('ro_induk_id', 'idx_ro_induk');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ro_induk_id')->references('id')->on('ro_proyek')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('instansi_pelaksana_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('RO/Proyek/Aktivitas per PSN -- objek yang diverifikasi pada Bagian C-D Instrumen Pengendalian');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `ro_proyek` ADD CONSTRAINT `chk_ro_tipe` CHECK (`tipe` IN (\'RO\',\'Aktivitas\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('ro_proyek');
    }
};
