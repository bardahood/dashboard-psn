<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lanjutan analisis "Pedoman Project Profile PSN" (slide 8, RO/Proyek/
 * Aktivitas): "satuan diisi dengan unit fisik dari RO dan Persentase
 * penyelesaian RO. Dengan demikian, terdapat dua jenis target yang perlu
 * dilengkapi, yakni target fisik dan target persentase."
 *
 * Kolom `target` yang sudah ada dipertahankan sebagai "target fisik"
 * (dalam satuan ro_proyek.satuan) tanpa diganti nama -- menghindari
 * migrasi data pada kolom yang sudah dipakai -- dan ditambahkan kolom
 * baru `target_persen` sebagai pasangannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ro_target_periode', function (Blueprint $table) {
            $table->decimal('target_persen', 5, 2)->nullable()->after('target')
                ->comment('Target persentase penyelesaian RO periode ini -- pasangan target fisik, Pedoman Project Profile PSN');
        });
    }

    public function down(): void
    {
        Schema::table('ro_target_periode', function (Blueprint $table) {
            $table->dropColumn('target_persen');
        });
    }
};
