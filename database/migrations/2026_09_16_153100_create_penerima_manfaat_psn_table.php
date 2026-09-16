<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerima_manfaat_psn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psn_id');
            $table->string('kategori_penerima', 255)->comment('mis. Pembudidaya udang, Industri pengolah udang');
            $table->string('satuan', 50)->nullable();
            $table->string('baseline', 100)->nullable();
            $table->index('psn_id', 'idx_pmp_psn');
            $table->foreign('psn_id')->references('id')->on('psn')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Penerima Manfaat (master, kumulatif) -- Perencanaan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('penerima_manfaat_psn');
    }
};
