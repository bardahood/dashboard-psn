<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_provinsi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_provinsi', 100);
            $table->unique('nama_provinsi', 'uq_provinsi_nama');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master provinsi; termasuk nilai Nasional untuk PSN berskala nasional (kolom Lokasi_Prov)');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_provinsi');
    }
};
