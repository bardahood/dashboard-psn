<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_dokumen_teknis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dokumen', 150);
            $table->integer('urutan')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master 9 dokumen teknis (FS, DED, AMDAL, dst.) yang diverifikasi ketersediaannya');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_dokumen_teknis');
    }
};
