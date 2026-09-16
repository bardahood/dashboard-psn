<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_log_psi', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal_sync');
            $table->integer('jumlah_psn_diterima')->nullable()->comment('mis. 298 sesuai pemutakhiran PSI');
            $table->string('status', 20)->comment('Sukses / Gagal / Sebagian');
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->comment('Riwayat konsumsi API Dit. PSI -- API dijanjikan tersedia 15 September 2026');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `sync_log_psi` ADD CONSTRAINT `chk_sync_status` CHECK (`status` IN (\'Sukses\',\'Gagal\',\'Sebagian\'))');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('sync_log_psi');
    }
};
