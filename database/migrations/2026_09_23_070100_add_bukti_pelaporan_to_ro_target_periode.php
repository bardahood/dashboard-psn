<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Risalah Rapat 21 Sept 2026 (Penjabaran): "Tambahkan field bukti pelaporan
 * (upload dokumen)" pada Target/Realisasi per periode RO/kegiatan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ro_target_periode', function (Blueprint $table) {
            $table->string('bukti_pelaporan_path', 500)->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('ro_target_periode', function (Blueprint $table) {
            $table->dropColumn('bukti_pelaporan_path');
        });
    }
};
