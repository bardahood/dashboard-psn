<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_hierarki', 10)->nullable();
            $table->string('status_psn_tercatat', 50)->nullable();
            $table->string('lokasi_kunjungan', 255)->nullable();
            $table->date('tanggal_kunjungan');
            $table->unsignedBigInteger('verifikator_id')->nullable();
            $table->text('tim_verifikator_tambahan')->nullable();
            $table->string('kepatuhan_frekuensi_pelaporan', 20)->nullable()->comment('Sesuai / Tidak Sesuai');
            $table->text('isu_tantangan')->nullable();
            $table->text('kebutuhan_tindak_lanjut')->nullable();
            $table->string('status_pengendalian', 50)->nullable();
            $table->text('rekomendasi_kelanjutan_status')->nullable();
            $table->unsignedBigInteger('mengetahui_id')->nullable();
            $table->date('tanggal_pengesahan')->nullable();
            $table->unsignedBigInteger('psn_id')->comment('Bagian A: Identitas & Klasifikasi Hierarki');
            $table->text('hasil_evaluasi_proyek')->nullable()->comment('Bagian G: Hasil Evaluasi Tim Pengendalian');
            $table->text('kesimpulan_umum')->nullable()->comment('Bagian I: Kesimpulan & Pengesahan');
            $table->index('psn_id', 'idx_kp_psn');
            $table->index('tanggal_kunjungan', 'idx_kp_tanggal');
            $table->index('verifikator_id', 'idx_kp_verifikator');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('verifikator_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('mengetahui_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Header kunjungan lapangan bidang Pengendalian -- Bagian A, G, I formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian` ADD CONSTRAINT `chk_kp_hierarki` CHECK (`tipe_hierarki` IS NULL OR `tipe_hierarki` IN (\'PKPN\',\'PSN\'))');
            DB::statement('ALTER TABLE `kunjungan_pengendalian` ADD CONSTRAINT `chk_kp_kepatuhan` CHECK (`kepatuhan_frekuensi_pelaporan` IS NULL OR `kepatuhan_frekuensi_pelaporan` IN (\'Sesuai\',\'Tidak Sesuai\'))');
            DB::statement('ALTER TABLE `kunjungan_pengendalian` ADD CONSTRAINT `chk_kp_status_pengendalian` CHECK (`status_pengendalian` IS NULL OR `status_pengendalian` IN (\'Aktif Dikendalikan Sesuai Rencana\',\'Perlu Perhatian\',\'Kritis/Perlu Eskalasi\',\'Selesai\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian');
    }
};
