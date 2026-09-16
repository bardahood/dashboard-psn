<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_sumber_data', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sumber', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->unique('nama_sumber', 'uq_sumber_nama');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master 4 sumber data yang direkonsiliasi pada Matrik Sandingan (RKP/PEKS3/PSI/Permenko)');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_sumber_data');
    }
};
