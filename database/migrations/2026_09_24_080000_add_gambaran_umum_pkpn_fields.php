<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hasil cek kesesuaian tab "Gambaran Umum" terhadap diagram resmi Struktur
 * Project Profile yang dilampirkan (kotak-kotak: Status PSN, Klaster PSN,
 * Klaster PKPN, Nama, Urgensi & Dasar Hukum, Tujuan Utama, Diagram Kerangka
 * Kerja Logis, Indikator PP (Khusus PKPN level PP), Pengusul/Penanggung
 * Jawab/Stakeholders Mapping/Kerangka Kelembagaan, Lokasi, Indikasi Sumber
 * Pendanaan, Tahun Penyelesaian & Output Akhir, Nilai Investasi). Sebagian
 * besar sudah tercermin di skema (Status PSN, Klaster, Tipe Hierarki utk
 * Klaster PKPN, Kode RKP utk Diagram Kerangka Kerja Logis, Pengusul,
 * psn_penanggung_jawab, Stakeholder Mapping, diagram_kelembagaan_path,
 * Lokasi, Tahun & Output Akhir, Nilai Investasi) -- dua yang benar-benar
 * belum ada ditambahkan di sini:
 *
 * 1. Indikasi Sumber Pendanaan (level PSN/Gambaran Umum) -- sebelumnya
 *    hanya ada per-periode RO (ro_target_periode.indikasi_sumber_pendanaan,
 *    Permen PPN 4/2025 Pasal 18), belum ada ringkasan di level PSN.
 * 2. Indikator PP khusus PKPN -- komentar pada migrasi indikator_psn
 *    ("juga menampung Indikator PP khusus PKPN") menunjukkan tabel ini
 *    memang dirancang menampung dua jenis indikator, tapi belum ada kolom
 *    pembeda; akibatnya tidak ada cara memisahkan Indikator PP (Gambaran
 *    Umum, khusus PKPN) dari Indikator Output/Outcome biasa (Perencanaan).
 *    NULL = Output/Outcome (default, data lama tidak berubah), 'PP' = baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            $table->string('indikasi_sumber_pendanaan', 30)->nullable()->after('nilai_investasi_non_apbn_rp')
                ->comment('Ringkasan level PSN, kategori sama dengan ro_target_periode: APBN/APBD/BUMN/BU-Swasta/Lainnya');
        });

        Schema::table('indikator_psn', function (Blueprint $table) {
            $table->string('jenis_indikator', 20)->nullable()->after('psn_id')
                ->comment('NULL = Indikator Output/Outcome (Perencanaan), PP = Indikator PP khusus PKPN (Gambaran Umum)');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `psn` ADD CONSTRAINT `chk_psn_indikasi_sumber_pendanaan` CHECK (`indikasi_sumber_pendanaan` IS NULL OR `indikasi_sumber_pendanaan` IN (\'APBN\',\'APBD\',\'BUMN\',\'BU-Swasta\',\'Lainnya\'))');
            DB::statement('ALTER TABLE `indikator_psn` ADD CONSTRAINT `chk_indp_jenis` CHECK (`jenis_indikator` IS NULL OR `jenis_indikator` = \'PP\')');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `psn` DROP CONSTRAINT `chk_psn_indikasi_sumber_pendanaan`');
            DB::statement('ALTER TABLE `indikator_psn` DROP CONSTRAINT `chk_indp_jenis`');
        }

        Schema::table('psn', function (Blueprint $table) {
            $table->dropColumn('indikasi_sumber_pendanaan');
        });

        Schema::table('indikator_psn', function (Blueprint $table) {
            $table->dropColumn('jenis_indikator');
        });
    }
};
