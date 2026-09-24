<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GAP #1 (analisis KAK vs Dashboard PSN): KAK Laporan Penyusunan Daftar PSN
 * eksplisit meminta pemisahan "PSN berjalan (carryover)" vs "usulan baru" --
 * skema asli tidak punya kolom ini karena Matrik Sandingan sumber juga tidak
 * membedakannya. Nullable & tanpa default supaya data lama (hasil impor)
 * tidak dipaksa terisi (prinsip validasi longgar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            $table->string('kategori_usulan', 20)->nullable()->after('status_psn_id')
                ->comment('Carryover (PSN berjalan) atau Usulan Baru -- Bagian 3a KAK Laporan Penyusunan Daftar PSN');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `psn` ADD CONSTRAINT `chk_psn_kategori_usulan` CHECK (`kategori_usulan` IS NULL OR `kategori_usulan` IN (\'Carryover\',\'Usulan Baru\'))');
        }
    }

    public function down(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE `psn` DROP CONSTRAINT `chk_psn_kategori_usulan`');
            }
            $table->dropColumn('kategori_usulan');
        });
    }
};
