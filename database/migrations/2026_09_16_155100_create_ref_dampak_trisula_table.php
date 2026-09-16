<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_dampak_trisula', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dampak', 100)->comment('Pertumbuhan Ekonomi / Penurunan Kemiskinan / Peningkatan Kualitas SDM');
            $table->text('contoh_indikator')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master 3 kategori dampak Trisula Pembangunan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_dampak_trisula');
    }
};
