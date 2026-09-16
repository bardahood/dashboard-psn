<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psn', function (Blueprint $table) {
            $table->id();
            $table->text('nama_psn')->comment('Uraian PSN, dapat berupa narasi panjang (kolom PSN pada Matrik)');
            $table->text('urgensi')->nullable()->comment('Gambaran Umum: Urgensi & Dasar Hukum (narasi urgensi)');
            $table->text('tujuan_utama')->nullable();
            $table->smallInteger('tahun_penyelesaian')->nullable()->comment('mis. 2029');
            $table->text('output_akhir')->nullable()->comment('mis. "4 Tambak Udang"');
            $table->decimal('nilai_investasi_apbn_rp', 20, 2)->nullable()->comment('Nilai Investasi/Anggaran Total s.d. akhir proyek -- APBN');
            $table->decimal('nilai_investasi_non_apbn_rp', 20, 2)->nullable();
            $table->text('asta_cita')->nullable()->comment('Keterkaitan dengan Asta Cita');
            $table->unsignedBigInteger('pengusul_instansi_id')->nullable()->comment('Peran tunggal: Pengusul (Stakeholder Mapping)');
            $table->unsignedBigInteger('pengelola_instansi_id')->nullable()->comment('Peran tunggal: Pengelola');
            $table->unsignedBigInteger('kontraktor_instansi_id')->nullable()->comment('Peran tunggal: Kontraktor');
            $table->unsignedBigInteger('supervisi_instansi_id')->nullable()->comment('Peran tunggal: Supervisi');
            $table->unsignedBigInteger('klaster_id')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('status_psn_id')->nullable()->comment('Gambaran Umum: Status PSN (lifecycle)');
            $table->string('tipe_hierarki', 10)->nullable()->comment('PKPN atau PSN -- menentukan wajib bulanan (PKPN) vs boleh triwulanan (PSN)');
            $table->string('kabupaten_kota', 150)->nullable()->comment('Kolom Lokasi_Kab, umumnya sejalan provinsi_id');
            $table->string('kode_rkp', 30)->nullable()->comment('Kode acuan RKP Pemutakhiran 2026 bila tersedia');
            $table->string('sumber_input', 30)->default('Manual')->comment('"API PSI" atau "Manual" -- melacak asal pemutakhiran');
            $table->date('periode_update')->nullable()->comment('Tanggal data ini terakhir dimutakhirkan');
            $table->index('klaster_id', 'idx_psn_klaster');
            $table->index('provinsi_id', 'idx_psn_provinsi');
            $table->index('kode_rkp', 'idx_psn_kode_rkp');
            $table->fullText('nama_psn', 'ftx_psn_nama');
            $table->foreign('klaster_id')->references('id')->on('ref_klaster')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('provinsi_id')->references('id')->on('ref_provinsi')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('status_psn_id')->references('id')->on('ref_status_psn')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('pengusul_instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('pengelola_instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('kontraktor_instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('supervisi_instansi_id')->references('id')->on('ref_instansi')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('TABEL INTI -- setara Tabel PSN milik Database Perencanaan Dit. PSI');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `psn` ADD CONSTRAINT `chk_psn_sumber_input` CHECK (`sumber_input` IN (\'API PSI\',\'Manual\'))');
            DB::statement('ALTER TABLE `psn` ADD CONSTRAINT `chk_psn_hierarki` CHECK (`tipe_hierarki` IS NULL OR `tipe_hierarki` IN (\'PKPN\',\'PSN\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('psn');
    }
};
