<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hasil analisis kesesuaian dengan "Pedoman Project Profile PSN" (dokumen
 * pedoman resmi yang dilampirkan) terhadap skema yang sudah berjalan.
 * Sebagian besar komponen pedoman (Stakeholder Mapping & Kerangka
 * Kelembagaan 4-level, Lokasi per-RO, Indikasi Sumber Pendanaan per
 * periode, target Trisula tahunan+triwulanan) sudah tercermin persis di
 * skema -- migration ini menambahkan field yang benar-benar belum ada:
 *
 * 1. Profil Risiko (Bagian "Perencanaan PSN" slide 8): pedoman meminta
 *    "PJ Risiko" dan "target mulai dan target selesai dari
 *    perlakuan/rencana penyelesaian" -- risiko_psn sebelumnya hanya
 *    punya peristiwa/level/perlakuan tanpa penanggung jawab & tenggat.
 * 2. Critical Path berbasis Risiko (slide 9): pedoman meminta risiko
 *    dikelompokkan ke Proyek/RO tertentu, ditandai titik kritis, dan
 *    diberi tahun pelaksanaan perlakuan -- risiko_psn sebelumnya tidak
 *    tertaut ke ro_proyek sama sekali.
 * 3. Kontribusi Trisula SDM (slide 7): pedoman mensyaratkan pemilihan
 *    kategori Pendidikan atau Kesehatan sebelum indikator bebas teks
 *    diisi -- trisula_kontribusi_psn sebelumnya tidak punya kolom ini.
 * 4. Visualisasi Kerangka Kelembagaan (slide 3-4): pedoman meminta
 *    diagram skematik hubungan antar pihak -- psn belum punya tempat
 *    menyimpan file diagram tsb.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risiko_psn', function (Blueprint $table) {
            $table->unsignedBigInteger('penanggung_jawab_id')->nullable()->after('risiko_residual_harapan')
                ->comment('PJ Risiko -- pedoman Project Profile PSN');
            $table->date('target_mulai')->nullable()->after('penanggung_jawab_id');
            $table->date('target_selesai')->nullable()->after('target_mulai');
            $table->unsignedBigInteger('ro_id')->nullable()->after('target_selesai')
                ->comment('Clustering risiko ke Proyek/RO -- Critical Path berbasis Risiko');
            $table->boolean('is_titik_kritis')->default(false)->after('ro_id')
                ->comment('Penanda Critical Path pada Profil Risiko');
            $table->smallInteger('tahun_pelaksanaan_perlakuan')->nullable()->after('is_titik_kritis');

            $table->foreign('penanggung_jawab_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('ro_id')->references('id')->on('ro_proyek')->onDelete('set null')->onUpdate('cascade');
        });

        Schema::table('trisula_kontribusi_psn', function (Blueprint $table) {
            $table->string('sub_kategori_sdm', 20)->nullable()->after('kategori_trisula')
                ->comment('Pendidikan/Kesehatan -- hanya berlaku saat kategori_trisula=Sumber Daya Manusia (Indeks Modal Manusia)');
        });

        Schema::table('psn', function (Blueprint $table) {
            $table->string('diagram_kelembagaan_path', 500)->nullable()->after('asta_cita')
                ->comment('Visualisasi Kerangka Kelembagaan (diagram skematik) -- pedoman Project Profile PSN');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `trisula_kontribusi_psn` ADD CONSTRAINT `chk_tkp_sub_sdm` CHECK (`sub_kategori_sdm` IS NULL OR `sub_kategori_sdm` IN (\'Pendidikan\',\'Kesehatan\'))');
        }
    }

    public function down(): void
    {
        Schema::table('psn', function (Blueprint $table) {
            $table->dropColumn('diagram_kelembagaan_path');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `trisula_kontribusi_psn` DROP CONSTRAINT `chk_tkp_sub_sdm`');
        }
        Schema::table('trisula_kontribusi_psn', function (Blueprint $table) {
            $table->dropColumn('sub_kategori_sdm');
        });

        Schema::table('risiko_psn', function (Blueprint $table) {
            $table->dropForeign(['penanggung_jawab_id']);
            $table->dropForeign(['ro_id']);
            $table->dropColumn(['penanggung_jawab_id', 'target_mulai', 'target_selesai', 'ro_id', 'is_titik_kritis', 'tahun_pelaksanaan_perlakuan']);
        });
    }
};
