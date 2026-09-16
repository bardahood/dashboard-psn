<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian_fisik', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('ro_id');
            $table->decimal('target_periode_ini', 18, 4)->nullable();
            $table->decimal('realisasi_fisik_klaim', 18, 4)->nullable();
            $table->decimal('realisasi_fisik_verifikasi', 18, 4)->nullable();
            $table->decimal('persen_capaian', 6, 2)->nullable();
            $table->string('kesesuaian', 20)->nullable()->comment('Sesuai / Sebagian / Tidak Sesuai');
            $table->text('catatan')->nullable();
            $table->index('kunjungan_id', 'idx_kpf_kunjungan');
            $table->index('ro_id', 'idx_kpf_ro');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_pengendalian')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ro_id')->references('id')->on('ro_proyek')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi capaian fisik per RO/Aktivitas -- Bagian C formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian_fisik` ADD CONSTRAINT `chk_kpf_kesesuaian` CHECK (`kesesuaian` IS NULL OR `kesesuaian` IN (\'Sesuai\',\'Sebagian\',\'Tidak Sesuai\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian_fisik');
    }
};
