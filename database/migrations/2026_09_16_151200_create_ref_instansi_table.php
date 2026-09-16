<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_instansi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi', 255);
            $table->unique('nama_instansi', 'uq_instansi_nama');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master K/L penanggung jawab, sesuai kolom K_L_Penanggungjawab pada Matrik Sandingan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_instansi');
    }
};
