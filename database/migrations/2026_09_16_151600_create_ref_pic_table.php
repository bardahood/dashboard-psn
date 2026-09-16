<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_pic', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pic', 150);
            $table->unsignedBigInteger('instansi_id')->nullable();
            $table->string('email', 150)->nullable();
            $table->index('instansi_id', 'idx_pic_instansi');
            $table->foreign('instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master personil -- dirujuk oleh info_memo, catatan_monev, hak_akses, audit_log');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_pic');
    }
};
