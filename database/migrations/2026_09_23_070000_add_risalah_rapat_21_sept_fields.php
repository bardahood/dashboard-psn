<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tindak lanjut Risalah Rapat 21 Sept 2026 (Koordinasi Lanjutan Pembahasan
 * Project Profile) -- lihat README bagian "Tindak Lanjut Risalah Rapat 21
 * Sept 2026" untuk daftar lengkap perubahan & alasan tiap kolom.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            $table->text('data_teknis')->nullable()->after('output_akhir')
                ->comment('Data teknis proyek (mis. luas kawasan, panjang jalan) -- Risalah Rapat 21 Sept 2026, pengganti "Spesifikasi Teknis"');
            $table->unsignedTinyInteger('bulan_penyelesaian')->nullable()->after('tahun_penyelesaian')
                ->comment('Bulan 1-12, pasangan tahun_penyelesaian -- Risalah Rapat 21 Sept 2026');
            $table->string('nama_sub_proyek', 255)->nullable()->after('nama_psn')
                ->comment('Membedakan program yang sama dengan sub-proyek/komponen berbeda -- Risalah Rapat 21 Sept 2026');
        });
        DB::statement('ALTER TABLE `psn` ADD CONSTRAINT `chk_psn_bulan_penyelesaian` CHECK (`bulan_penyelesaian` IS NULL OR `bulan_penyelesaian` BETWEEN 1 AND 12)');

        Schema::table('indikator_psn', function (Blueprint $table) {
            $table->smallInteger('baseline_tahun')->nullable()->after('baseline')
                ->comment('Tahun baseline berlaku (utk proyek berjalan sebelum 2026) -- Risalah Rapat 21 Sept 2026');
        });

        Schema::table('ro_proyek', function (Blueprint $table) {
            $table->smallInteger('baseline_tahun')->nullable()->after('baseline')
                ->comment('Tahun baseline berlaku -- Risalah Rapat 21 Sept 2026');
        });

        Schema::table('trisula_kontribusi_psn', function (Blueprint $table) {
            $table->string('satuan', 50)->nullable()->after('nama_indikator')
                ->comment('Satuan indikator kontribusi Trisula -- Risalah Rapat 21 Sept 2026');
        });

        Schema::table('risiko_psn', function (Blueprint $table) {
            $table->unsignedBigInteger('pelaksana_perlakuan_id')->nullable()->after('penanggung_jawab_id')
                ->comment('Pihak pelaksana perlakuan risiko, dibedakan dari risk owner (penanggung_jawab_id) -- Risalah Rapat 21 Sept 2026');
            $table->foreign('pelaksana_perlakuan_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('risiko_psn', function (Blueprint $table) {
            $table->dropForeign(['pelaksana_perlakuan_id']);
            $table->dropColumn('pelaksana_perlakuan_id');
        });

        Schema::table('trisula_kontribusi_psn', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });

        Schema::table('ro_proyek', function (Blueprint $table) {
            $table->dropColumn('baseline_tahun');
        });

        Schema::table('indikator_psn', function (Blueprint $table) {
            $table->dropColumn('baseline_tahun');
        });

        DB::statement('ALTER TABLE `psn` DROP CONSTRAINT `chk_psn_bulan_penyelesaian`');
        Schema::table('psn', function (Blueprint $table) {
            $table->dropColumn(['data_teknis', 'bulan_penyelesaian', 'nama_sub_proyek']);
        });
    }
};
