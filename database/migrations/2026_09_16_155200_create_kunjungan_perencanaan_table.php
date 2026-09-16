<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_perencanaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_usulan_psn', 500);
            $table->string('nomor_kode_usulan', 50)->nullable();
            $table->unsignedBigInteger('klaster_id')->nullable();
            $table->unsignedBigInteger('pengusul_instansi_id')->nullable();
            $table->string('jenis_pengusul', 20)->nullable()->comment('KL / Pemda / BUMN & Swasta');
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->string('lokasi_detail', 255)->nullable();
            $table->string('indikasi_pendanaan', 100)->nullable();
            $table->decimal('nilai_proyek_rp', 20, 2)->nullable();
            $table->string('kpu_terkait', 255)->nullable();
            $table->string('hasil_pmo_rpjmn_program_prioritas', 10)->nullable();
            $table->string('hasil_pmo_rpjmn_phtc', 10)->nullable();
            $table->string('hasil_pmo_selesai_2029', 10)->nullable();
            $table->string('status_keputusan_saat_ini', 100)->nullable();
            $table->text('catatan_pembahasan_pmo')->nullable();
            $table->text('fokus_verifikasi_lapangan')->nullable();
            $table->unsignedBigInteger('verifikator_id')->nullable();
            $table->string('pihak_pengusul_ditemui', 255)->nullable();
            $table->string('narasumber_teknis_lain', 255)->nullable();
            $table->text('dasar_justifikasi')->nullable();
            $table->text('dokumen_masih_diperlukan')->nullable();
            $table->date('batas_waktu_pemenuhan')->nullable();
            $table->unsignedBigInteger('diverifikasi_oleh_id')->nullable();
            $table->unsignedBigInteger('mengetahui_id')->nullable();
            $table->unsignedBigInteger('psn_id')->nullable()->comment('Usulan baru boleh belum tercatat di tabel psn');
            $table->string('skor_pmo_sementara', 20)->nullable();
            $table->date('tanggal_kunjungan');
            $table->string('rekomendasi_keseluruhan', 40)->nullable()->comment('Layak Dilanjutkan / Layak dengan Catatan / Perlu Perbaikan Dokumen / Belum Layak / Ditolak');
            $table->index('psn_id', 'idx_kperc_psn');
            $table->index('klaster_id', 'idx_kperc_klaster');
            $table->index('pengusul_instansi_id', 'idx_kperc_pengusul');
            $table->index('provinsi_id', 'idx_kperc_provinsi');
            $table->index('verifikator_id', 'idx_kperc_verifikator');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('klaster_id')->references('id')->on('ref_klaster')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('pengusul_instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('provinsi_id')->references('id')->on('ref_provinsi')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('verifikator_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('diverifikasi_oleh_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('mengetahui_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Header kunjungan lapangan bidang Perencanaan -- verifikasi usulan PSN thd 14 kriteria');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_perencanaan` ADD CONSTRAINT `chk_kperc_jenis` CHECK (`jenis_pengusul` IS NULL OR `jenis_pengusul` IN (\'KL\',\'Pemda\',\'BUMN & Swasta\'))');
            DB::statement('ALTER TABLE `kunjungan_perencanaan` ADD CONSTRAINT `chk_kperc_rekom` CHECK (`rekomendasi_keseluruhan` IS NULL OR `rekomendasi_keseluruhan` IN (\'Layak Dilanjutkan\',\'Layak dengan Catatan\',\'Perlu Perbaikan Dokumen\',\'Belum Layak\',\'Ditolak\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_perencanaan');
    }
};
