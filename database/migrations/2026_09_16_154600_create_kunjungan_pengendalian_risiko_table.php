<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_pengendalian_risiko', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->unsignedBigInteger('risiko_id');
            $table->decimal('progres_pelaksanaan_persen', 5, 2)->nullable();
            $table->string('risiko_residual_aktual', 20)->nullable();
            $table->string('status_perlakuan', 30)->nullable();
            $table->string('evaluasi_risiko', 30)->nullable()->comment('Sesuai/Lebih Baik atau Memburuk');
            $table->text('catatan')->nullable();
            $table->index('kunjungan_id', 'idx_kpr_kunjungan');
            $table->index('risiko_id', 'idx_kpr_risiko');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungan_pengendalian')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('risiko_id')->references('id')->on('risiko_psn')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Verifikasi peristiwa risiko & risiko residual -- Bagian E formulir instrumen');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kunjungan_pengendalian_risiko` ADD CONSTRAINT `chk_kpr_residual` CHECK (`risiko_residual_aktual` IS NULL OR `risiko_residual_aktual` IN (\'Rendah\',\'Sedang\',\'Tinggi\',\'Sangat Tinggi\'))');
            DB::statement('ALTER TABLE `kunjungan_pengendalian_risiko` ADD CONSTRAINT `chk_kpr_status` CHECK (`status_perlakuan` IS NULL OR `status_perlakuan` IN (\'Selesai\',\'On Progress\',\'Belum ada Tindak Lanjut\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pengendalian_risiko');
    }
};
