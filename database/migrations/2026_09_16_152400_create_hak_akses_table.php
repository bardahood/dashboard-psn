<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hak_akses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pic_id');
            $table->unsignedBigInteger('instansi_id')->nullable();
            $table->string('level_akses', 30)->comment('mis. Admin / Editor / Viewer');
            $table->boolean('is_active')->default(true);
            $table->index('pic_id', 'idx_akses_pic');
            $table->index('instansi_id', 'idx_akses_instansi');
            $table->foreign('pic_id')->references('id')->on('ref_pic')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('TABEL RIIL PSI (Resume Rapat 11 Sept 2026) -- Tabel Hak Akses');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `hak_akses` ADD CONSTRAINT `chk_akses_level` CHECK (`level_akses` IN (\'Admin\',\'Editor\',\'Viewer\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('hak_akses');
    }
};
